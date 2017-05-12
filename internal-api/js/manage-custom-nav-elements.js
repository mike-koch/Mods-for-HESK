$(document).ready(function() {
    var heskUrl = $('#heskUrl').text();

    var places = [];
    places[1] = 'Homepage - Block';
    places[2] = 'Customer Navigation';
    places[3] = 'Staff Navigation';

    $.ajax({
        method: 'GET',
        url: heskUrl + '/api/v1-internal/custom-navigation/all',
        headers: { 'X-Internal-Call': true },
        success: function(data) {
            $.each(data, function() {
                var $template = $($('#nav-element-template').html());
                //var $template = $(template);
                console.log($template);
                $template.find('span[data-property="id"]').text(this.id);
                if (this.imageUrl === null) {
                    $template.find('span[data-property="image-or-font"]').html('<i class="' + escape(this.fontIcon) + '"></i>');
                } else {
                    $template.find('span[data-property="image-or-font"]').text(this.imageUrl);
                }

                $template.find('span[data-property="place"]').text(places[this.place]);

                var text = '';
                $.each(this.text, function(key, value) {
                    text += '<li><b>' + escape(key) + ':</b> ' + escape(value) + '</li>';
                });
                $template.find('ul[data-property="text"]').html(text);

                $.each(this.subtext, function() {
                    console.log(this);
                });

                $('#table-body').append($template);
            });
        },
        error: function(data) {
            console.error(data);
        }
    });
});

function escape(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}