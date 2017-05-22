var elements = [];

$(document).ready(function() {
    loadTable();
    bindEditModal();
    bindCreateModal();
    bindDeleteButton();
    bindSortButtons();

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
        var navUrl = $modal.find('input[name="url"]').val();

        var data = {
            place: place,
            text: text,
            subtext: subtext,
            imageUrl: imageUrl,
            fontIcon: fontIcon,
            url: navUrl
        };

        var url = heskUrl + '/api/v1-internal/custom-navigation/';
        var method = 'POST';

        if (id !== -1) {
            url += id;
            method = 'PUT';
        }

        $modal.find('#action-buttons').find('.cancel-button').attr('disabled', 'disabled');
        $modal.find('#action-buttons').find('.save-button').attr('disabled', 'disabled');

        $.ajax({
            method: method,
            url: url,
            headers: { 'X-Internal-Call': true },
            data: JSON.stringify(data),
            success: function(data) {
                if (id === -1) {
                    mfhAlert.success(mfhLang.text('custom_nav_element_created'));
                } else {
                    mfhAlert.success(mfhLang.text('custom_nav_element_saved'));
                }
                $modal.modal('hide');
                loadTable();
            },
            error: function(data) {
                mfhAlert.error("[!]Error saving custom nav element (" + data.responseJSON.logId + ")");
                console.error(data);
            },
            complete: function() {
                $modal.find('#action-buttons').find('.cancel-button').removeAttr('disabled');
                $modal.find('#action-buttons').find('.save-button').removeAttr('disabled');
            }
        });
    });
});

function loadTable() {
    $('#overlay').show();
    var heskUrl = $('#heskUrl').text();
    var notFoundText = mfhLang.text('no_custom_nav_elements_found');
    var places = [];
    var $tableBody = $('#table-body');
    places[1] = mfhLang.text('homepage_block');
    places[2] = mfhLang.text('customer_navigation');
    places[3] = mfhLang.text('staff_navigation');

    $.ajax({
        method: 'GET',
        url: heskUrl + '/api/v1-internal/custom-navigation/all',
        headers: { 'X-Internal-Call': true },
        success: function(data) {
            $tableBody.html('');
            elements = [];

            if (data.length === 0) {
                $('#table-body').append('<tr><td colspan="6">' + notFoundText + '</td></tr>');
                return;
            }

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

function escape(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function bindEditModal() {
    $(document).on('click', '[data-action="edit"]', function() {
        var element = elements[$(this).parent().parent().find('[data-property="id"]').text()];
        var $modal = $('#nav-element-modal');

        $modal.find('#edit-label').show();
        $modal.find('#crate-label').hide();
        $modal.find('select[name="place"]').val(element.place);
        $modal.find('input[name="id"]').val(element.id);
        $modal.find('input[name="url"]').val(element.url);
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
        $modal.find('#edit-label').hide();
        $modal.find('#crate-label').show();
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
        $modal.find('input[name="url"]').val('');

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
                mfhAlert.success(mfhLang.text('custom_nav_element_deleted'));
                loadTable();
            },
            error: function(data) {
                $('#overlay').hide();
                mfhAlert.errorWithLog(mfhLang.text('error_deleting_custom_nav_element'), data.responseJSON);
                console.error(data);
            }
        });
    });
}

function bindSortButtons() {
    $(document).on('click', '[data-action="sort"]', function() {
        $('#overlay').show();
        var heskUrl = $('#heskUrl').text();
        var direction = $(this).data('direction');
        var element = elements[$(this).parent().parent().find('[data-property="id"]').text()];

        $.ajax({
            method: 'POST',
            url: heskUrl + '/api/v1-internal/custom-navigation/' + element.id + '/sort/' + direction,
            headers: { 'X-Internal-Call': true },
            success: function() {
                loadTable();
            },
            error: function(data) {
                mfhAlert.errorWithLog(mfhLang.text('error_sorting_custom_nav_elements'), data.responseJSON);
                console.error(data);
                $('#overlay').hide();
            }
        })
    });
}