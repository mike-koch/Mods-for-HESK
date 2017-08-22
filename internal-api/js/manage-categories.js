var categories = [];

$(document).ready(function() {
    loadTable();
});


function loadTable() {
    $('#overlay').show();
    var heskUrl = $('p#hesk-path').text();
    var $tableBody = $('#table-body');

    $.ajax({
        method: 'GET',
        url: heskUrl + 'api/index.php/v1/categories/all',
        headers: { 'X-Internal-Call': true },
        success: function(data) {
            $tableBody.html('');
            elements = [];

            if (data.length === 0) {
                mfhAlert.error("I couldn't find any categories :(", "No categories found");
                $('#overlay').hide();
                return;
            }

            var sortedElements = [];

            $.each(data, function() {
                
            });

            var currentPlace = 0;
            var addedElementToPlace = false;
            var first = true;
            var lastElement = null;
            $.each(data, function() {
                if (this.place !== currentPlace) {
                    if (lastElement !== null) {
                        //-- Hide the down arrow on the last element
                        $('[data-value="' + lastElement.id + '"]').parent().parent()
                            .find('[data-direction="down"]').css('visibility', 'hidden');
                        lastElement = null;
                    }

                    $('#table-body').append('<tr><td colspan="6" class="bg-gray"><i><b>' + places[this.place] + '</b></i></td></tr>');
                    currentPlace = this.place;
                    addedElementToPlace = false;
                    first = true;
                }

                var $template = $($('#nav-element-template').html());

                $template.find('span[data-property="id"]').text(this.id).attr('data-value', this.id);
                if (this.imageUrl === null) {
                    $template.find('span[data-property="image-or-font"]').html('<i class="' + escape(this.fontIcon) + '"></i>');
                } else {
                    $template.find('span[data-property="image-or-font"]').text(this.imageUrl);
                }

                $template.find('span[data-property="url"]').text(this.url);

                var text = '';
                $.each(this.text, function(key, value) {
                    text += '<li><b>' + escape(key) + ':</b> ' + escape(value) + '</li>';
                });
                $template.find('ul[data-property="text"]').html(text);

                var subtext = '-';
                if (this.place === 1) {
                    subtext = '';
                    $.each(this.subtext, function(key, value) {
                        subtext += '<li><b>' + escape(key) + ':</b> ' + escape(value) + '</li>';
                    });
                }
                $template.find('ul[data-property="subtext"]').html(subtext);

                if (first) {
                    $template.find('[data-direction="up"]').css('visibility', 'hidden');
                    first = false;
                }

                $tableBody.append($template);

                elements[this.id] = this;

                addedElementToPlace = true;
                lastElement = this;
            });

            if (lastElement) {
                //-- Hide the down arrow on the last element
                $('[data-value="' + lastElement.id + '"]').parent().parent()
                    .find('[data-direction="down"]').css('visibility', 'hidden');
            }
        },
        error: function(data) {
            mfhAlert.errorWithLog(mfhLang.text('failed_to_load_custom_nav_elements'), data.responseJSON);
            console.error(data);
        },
        complete: function() {
            $('#overlay').hide();
        }
    });
}