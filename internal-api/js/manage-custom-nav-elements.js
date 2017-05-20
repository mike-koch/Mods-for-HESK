var elements = [];

$(document).ready(function() {
    loadTable();
    bindEditModal();
    bindCreateModal();
    bindDeleteButton();

    $('[data-toggle="nav-iconpicker"]').iconpicker({
        iconset: ['fontawesome', 'octicon'],
        selectedClass: "btn-warning",
        labelNoIcon: $('#no-icon').text(),
        searchText: $('#search-icon').text(),
        labelFooter: $('#footer-icon').text(),
        resetButton: false,
        icon: 'fa fa-adn'
    });

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
    });

    $('form#manage-nav-element').submit(function(e) {
        e.preventDefault();
        var heskUrl = $('#heskUrl').text();

        var $modal = $('#nav-element-modal');

        var place = parseInt($modal.find('select[name="place"]').val());

        var $textLanguages = $modal.find('[data-text-language]');
        var text = {};
        $.each($textLanguages, function() {
            text[$(this).data('text-language')] = $(this).val();
        });

        var subtext = {};
        if (place === 1) {
            var $subtextLanguages = $modal.find('[data-subtext-language]');
            $.each($subtextLanguages, function() {
                subtext[$(this).data('subtext-language')] = $(this).val();
            });
        }

        var imageUrl = null;
        var fontIcon = null;
        if ($modal.find('select[name="image-type"]').val() === 'image-url') {
            imageUrl = $modal.find('input[name="image-url"]').val();
        } else {
            fontIcon = $modal.find('.iconpicker').find('input[type="hidden"]').val();
        }

        var id = parseInt($modal.find('input[name="id"]').val());

        var data = {
            place: place,
            text: text,
            subtext: subtext,
            imageUrl: imageUrl,
            fontIcon: fontIcon
        };

        var url = heskUrl + '/api/v1-internal/custom-navigation/';
        var method = 'POST';

        if (id !== -1) {
            url += id;
            method = 'PUT';
        }

        $.ajax({
            method: method,
            url: url,
            headers: { 'X-Internal-Call': true },
            data: JSON.stringify(data),
            success: function(data) {
                loadTable($modal);
            },
            error: function(data) {
                console.error(data);
            }
        });
    });
});

function loadTable(modalToClose) {
    var heskUrl = $('#heskUrl').text();
    var notFoundText = $('#lang_no_custom_nav_elements_found').text();
    var places = [];
    places[1] = 'Homepage - Block';
    places[2] = 'Customer Navbar';
    places[3] = 'Staff Navbar';

    $.ajax({
        method: 'GET',
        url: heskUrl + '/api/v1-internal/custom-navigation/all',
        headers: { 'X-Internal-Call': true },
        success: function(data) {
            $('#table-body').html('');
            elements = [];

            if (data.length === 0) {
                $('#table-body').append('<tr><td colspan="6">' + notFoundText + '</td></tr>');
                return;
            }

            $('#table-body').append('<tr><td colspan="6" class="bg-gray"><i><b>' + places[1] + '</b></i></td></tr>');
            var currentPlace = 1;
            var addedElementToPlace = false;
            var first = true;
            var lastElement = null;
            $.each(data, function() {
                if (this.place !== currentPlace) {
                    if (!addedElementToPlace) {
                        $('#table-body').append('<tr><td colspan="6">' + notFoundText + '</td></tr>');
                    }

                    if (lastElement !== null) {
                        //-- Hide the down arrow on the last element
                        $('[data-value="' + lastElement.id + '"]').parent().parent()
                            .find('[data-direction="down"]').find('i').removeClass('fa-arrow-down');
                        lastElement = null;
                    }

                    $('#table-body').append('<tr><td colspan="6" class="bg-gray"><i><b>' + places[this.place] + '</b></i></td></tr>');
                    currentPlace = this.place;
                    console.log(this);
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

                $template.find('span[data-property="url"]').text(places[this.url]);

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
                    $template.find('[data-direction="up"]').find('i').removeClass('fa-arrow-up');
                    first = false;
                }

                $('#table-body').append($template);

                elements[this.id] = this;

                addedElementToPlace = true;
                lastElement = this;
            });

            //-- Add missing headers if no elements are in them
            if (currentPlace === 1) {
                $('#table-body').append('<tr><td colspan="6" class="bg-gray"><i><b>' + places[2] + '</b></i></td></tr>');
                $('#table-body').append('<tr><td colspan="6">' + notFoundText + '</td></tr>');
            }
            if (currentPlace === 2) {
                $('#table-body').append('<tr><td colspan="6" class="bg-gray"><i><b>' + places[3] + '</b></i></td></tr>');
                $('#table-body').append('<tr><td colspan="6">' + notFoundText + '</td></tr>');
            }

            if (lastElement) {
                //-- Hide the down arrow on the last element
                $('[data-value="' + lastElement.id + '"]').parent().parent()
                    .find('[data-direction="down"]').find('i').removeClass('fa-arrow-down');
            }

            if (modalToClose !== undefined) {
                modalToClose.modal('hide');
            }
        },
        error: function(data) {
            console.error(data);
        },
        complete: function() {
            $('#overlay').hide();
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

        $modal.find('select[name="place"]').val(element.place);
        $modal.find('input[name="id"]').val(element.id);
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

        if (element.place === 1) {
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

function bindCreateModal() {
    $('#create-button').click(function() {
        var $modal = $('#nav-element-modal');
        $modal.find('select[name="place"]').val(1);
        $modal.find('input[name="id"]').val(-1);
        var $textLanguages = $modal.find('[data-text-language]');
        $.each($textLanguages, function() {
            var language = $(this).data('text-language');

            $(this).val('');
        });

        var $subtextLanguages = $modal.find('[data-subtext-language]');
        $.each($subtextLanguages, function() {
            var language = $(this).data('subtext-language');

            $(this).val('');
        });

        $('#subtext').show();

        $modal.find('select[name="image-type"]').val('image-url');
        $modal.find('input[name="image-url"]').val('');
        $modal.find('#font-icon-group').hide();
        $modal.find('#image-url-group').show();

        $modal.modal('show');
    });
}

function bindDeleteButton() {
    $(document).on('click', '[data-action="delete"]', function() {
        $('#overlay').show();

        var heskUrl = $('#heskUrl').text();
        var element = elements[$(this).parent().parent().find('[data-property="id"]').text()];

        $.ajax({
            method: 'DELETE',
            url: heskUrl + '/api/v1-internal/custom-navigation/' + element.id,
            headers: { 'X-Internal-Call': true },
            success: function() {
                console.log('DELETED!');
                loadTable();
            },
            error: function(data) {
                console.error(data);
            }
        });
    });
}