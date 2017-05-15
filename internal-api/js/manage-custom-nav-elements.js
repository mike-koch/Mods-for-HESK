$(document).ready(function() {
    loadTable();

    $(document).on('click', '[data-action="edit"]', function() {
        var $tableRow = $(this).parent().parent();
        var $modal = $('#nav-element-modal');

        $modal.find('select[name="place"]').val($tableRow.find('[data-property="place-id"]').text());

        $modal.modal('show');
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
                if (this.place == 1) {
                    subtext = '';
                    $.each(this.subtext, function(key, value) {
                        subtext += '<li><b>' + escape(key) + ':</b> ' + escape(value) + '</li>';
                    });
                }
                $template.find('ul[data-property="subtext"]').html(text);

                $('#table-body').append($template);
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

function displayModal(element) {
    var creatingElement = element === undefined;

    var $form = $('#nav-element-modal').find('form');
}