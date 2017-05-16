var elements = [];

$(document).ready(function() {
    loadTable();
    bindEditModal();

    $('select[name="place"]').change(function() {
        var $subtextField = $('#subtext');
        if (parseInt($(this).val()) === 1) {
            $subtextField.show();
        } else {
            $subtextField.hide();
        }
    });

    $('select[name="image-type"]').change(function() {
        var $imageUrl = $('#image-url-group');
        var $fontIcon = $('#font-icon-group');

        if ($(this).val() === 'image-url') {
            $imageUrl.show();
            $fontIcon.hide();
        } else {
            $imageUrl.hide();
            $fontIcon.show();
        }
    })
});

function loadTable() {
    var heskUrl = $('#heskUrl').text();
    var places = [];
    places[1] = 'Homepage - Block';
    places[2] = 'Customer Navbar';
    places[3] = 'Staff Navbar';

    $.ajax({
        method: 'GET',
        url: heskUrl + '/api/v1-internal/custom-navigation/all',
        headers: { 'X-Internal-Call': true },
        success: function(data) {
            $.each(data, function() {
                var $template = $($('#nav-element-template').html());

                $template.find('span[data-property="id"]').text(this.id);
                if (this.imageUrl === null) {
                    $template.find('span[data-property="image-or-font"]').html('<i class="' + escape(this.fontIcon) + '"></i>');
                } else {
                    $template.find('span[data-property="image-or-font"]').text(this.imageUrl);
                }

                $template.find('span[data-property="place"]').text(places[this.place]);
                $template.find('span[data-property="place-id"]').text(this.place);

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

                $('#table-body').append($template);

                elements[this.id] = this;
            });
        },
        error: function(data) {
            console.error(data);
        },
        complete: function() {
            $('#loader').hide();
        }
    });
}

function escape(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function bindEditModal() {
    $(document).on('click', '[data-action="edit"]', function() {
        var element = elements[$(this).parent().parent().find('[data-property="id"]').text()];
        var $modal = $('#nav-element-modal');

        $modal.find('select[name="place"]').val(element.place).text();
        var $textLanguages = $modal.find('[data-text-language]');
        $.each($textLanguages, function() {
            var language = $(this).data('text-language');

            $(this).val(element.text[language]);
        });

        var $subtextLanguages = $modal.find('[data-subtext-language]');
        $.each($subtextLanguages, function() {
            var language = $(this).data('subtext-language');

            $(this).val(element.subtext[language]);
        });

        if (this.place === 1) {
            $('#subtext').show();
        } else {
            $('#subtext').hide();
        }

        if (element.imageUrl !== null) {
            $modal.find('select[name="image-type"]').val('image-url');
            $modal.find('input[name="image-url"]').val(element.imageUrl);
            $modal.find('#font-icon-group').hide();
            $modal.find('#image-url-group').show();
        } else {
            $modal.find('select[name="image-type"]').val('font-icon');
            $('[data-toggle="iconpicker"]').iconpicker('setIcon', element.fontIcon);
            $modal.find('#font-icon-group').show();
            $modal.find('#image-url-group').hide();
        }


        $modal.modal('show');
    });
}