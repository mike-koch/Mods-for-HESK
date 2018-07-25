var categoryGroups = [];

$(document).ready(function() {
    loadTable();
    /*bindEditModal();
    bindModalCancelCallback();
    bindFormSubmit();
    bindDeleteButton();*/
    bindCreateModal();
    //bindSortButtons();*/
});


function loadTable() {
    $('#overlay').show();
    var heskUrl = $('p#hesk-path').text();
    var $tableBody = $('#table-body');

    $.ajax({
        method: 'GET',
        url: heskUrl + 'api/index.php/v1/category-groups',
        headers: { 'X-Internal-Call': true },
        success: function(data) {
            $tableBody.html('');

            if (data.length === 0) {
                $tableBody.append('<td colspan="4">' + mfhLang.text('no_category_groups_found') + '</td>');
                // TODO No category groups have been created
            }

            var lastElement = undefined;
            $.each(data, function() {
                var $template = $($('#category-group-row-template').html());

                $template.find('span[data-property="id"]').text(this.id).attr('data-value', this.id);
                var $nameField = $template.find('td[data-property="name"]');
                $nameField.text(this.name);

                $tableBody.append($template);

                categoryGroups[this.id] = this;

                lastElement = this;

                if (first) {
                    $template.find('[data-direction="up"]').css('visibility', 'hidden');
                    first = false;
                }
            });

            if (lastElement) {
                //-- Hide the down arrow on the last element
                $('[data-value="' + lastElement.id + '"]').parent().parent()
                    .find('[data-direction="down"]').css('visibility', 'hidden');
            }
        },
        error: function(data) {
            mfhAlert.errorWithLog(mfhLang.text('error_retrieving_category_groups'), data.responseJSON);
            console.error(data);
        },
        complete: function() {
            $('#overlay').hide();
        }
    });
}

function bindEditModal() {
    $(document).on('click', '[data-action="edit"]', function() {
        var element = categories[$(this).parent().parent().find('[data-property="id"]').text()];
        var $modal = $('#category-modal');

        $modal.find('#edit-label').show();
        $modal.find('#create-label').hide();

        $modal.find('input[name="name"]').val(element.name).end()
            .find('select[name="priority"]').val(element.priority).end()
            .find('select[name="manager"]').val(element.manager === null ? 0 : element.manager).end()
            .find('input[name="id"]').val(element.id).end()
            .find('select[name="usage"]').val(element.usage).end()
            .find('input[name="display-border"][value="' + (element.displayBorder ? 1 : 0) + '"]')
                .prop('checked', 'checked').end();

        var backgroundColor = element.backgroundColor;
        var foregroundColor = element.foregroundColor;
        var colorpickerOptions = {
            format: 'hex',
            color: backgroundColor
        };
        $modal.find('input[name="background-color"]')
            .colorpicker(colorpickerOptions).end().modal('show');

        colorpickerOptions = {
            format: 'hex'
        };
        if (foregroundColor !== '' && foregroundColor !== 'AUTO') {
            colorpickerOptions.color = foregroundColor;
        }

        $modal.find('input[name="foreground-color"]')
            .colorpicker(colorpickerOptions).end().modal('show');

        if (foregroundColor === '' || foregroundColor === 'AUTO') {
            $modal.find('input[name="foreground-color"]').colorpicker('setValue', '#fff');
            $modal.find('input[name="foreground-color"]').val('');
        }

        $modal.find('input[name="cat-order"]').val(element.catOrder);
        $modal.find('input[name="autoassign"][value="' + (element.autoAssign ? 1 : 0) + '"]')
            .prop('checked', 'checked');
        $modal.find('input[name="type"][value="' + (element.type ? 1 : 0) + '"]')
            .prop('checked', 'checked');
        $modal.find('textarea[name="description"]').val(element.description === null ? '' : element.description);

        $modal.modal('show');
    });
}

function bindCreateModal() {
    $('#create-button').click(function() {
        var $modal = $('#category-modal');
        $modal.find('#edit-label').hide();
        $modal.find('#create-label').show();

        $modal.find('input[name="name"]').val('');
        $modal.find('select[name="priority"]').val(3); // Low priority
        $modal.find('select[name="usage"]').val(0); // Tickets and events
        $modal.find('input[name="id"]').val(-1);
        $modal.find('textarea[name="description"]').val('');
        $modal.find('input[name="cat-order"]').val('');
        $modal.find('input[name="type"][value="0"]').prop('checked', 'checked');
        $modal.find('input[name="autoassign"][value="0"]').prop('checked', 'checked');
        $modal.find('input[name="display-border"][value="0"]')
            .prop('checked', 'checked');

        var colorpickerOptions = {
            format: 'hex',
            color: '#fff'
        };
        $modal.find('input[name="background-color"]')
            .colorpicker(colorpickerOptions).end().modal('show');
        $modal.find('input[name="background-color"]').val('');
        $modal.find('input[name="foreground-color"]')
            .colorpicker(colorpickerOptions).end().modal('show');
        $modal.find('input[name="foreground-color"]').val('');

        $modal.modal('show');
    });
}

function bindModalCancelCallback() {
    $('.cancel-callback').click(resetModal);
}

function resetModal() {
    var $editCategoryModal = $('#category-modal');

    $editCategoryModal.find('input[name="background-color"]').val('').colorpicker('destroy').end();
    $editCategoryModal.find('input[name="foreground-color"]').val('').colorpicker('destroy').end();
    $editCategoryModal.find('input[name="display-border"][value="1"]').prop('checked');
    $editCategoryModal.find('input[name="display-border"][value="0"]').prop('checked');
    $editCategoryModal.find('input[name="autoassign"][value="1"]').prop('checked');
    $editCategoryModal.find('input[name="autoassign"][value="0"]').prop('checked');
}

function bindFormSubmit() {
    $('form#manage-category').submit(function(e) {
        e.preventDefault();
        var heskUrl = $('p#hesk-path').text();

        var $modal = $('#category-modal');

        var foregroundColor = $modal.find('input[name="foreground-color"]').val();
        var manager = parseInt($modal.find('select[name="manager"]').val());
        var data = {
            autoassign: $modal.find('input[name="autoassign"]:checked').val() === '1',
            backgroundColor: $modal.find('input[name="background-color"]').val(),
            description: $modal.find('textarea[name="description"]').val(),
            displayBorder: $modal.find('input[name="display-border"]:checked').val() === '1',
            foregroundColor: foregroundColor === '' ? 'AUTO' : foregroundColor,
            name: $modal.find('input[name="name"]').val(),
            priority: parseInt($modal.find('select[name="priority"]').val()),
            manager: manager === 0 ? null : manager,
            type: parseInt($modal.find('input[name="type"]:checked').val()),
            usage: parseInt($modal.find('select[name="usage"]').val()),
            catOrder: parseInt($modal.find('input[name="cat-order"]').val())
        };

        var url = heskUrl + 'api/index.php/v1/categories/';
        var method = 'POST';

        var categoryId = parseInt($modal.find('input[name="id"]').val());
        if (categoryId !== -1) {
            url += categoryId;
            method = 'PUT';
        }

        $modal.find('#action-buttons').find('.cancel-button').attr('disabled', 'disabled');
        $modal.find('#action-buttons').find('.save-button').attr('disabled', 'disabled');

        $.ajax({
            method: 'POST',
            url: url,
            headers: {
                'X-Internal-Call': true,
                'X-HTTP-Method-Override': method
            },
            data: JSON.stringify(data),
            success: function(data) {
                var format = undefined;
                if (categoryId === -1) {
                    format = mfhLang.html('cat_name_added');
                    mfhAlert.success(format.replace('%s', data.name));
                } else {
                    format = mfhLang.html('category_updated');
                    mfhAlert.success(format.replace('%s', data.name));
                }
                resetModal();
                $modal.modal('hide');
                loadTable();
            },
            error: function(data) {
                mfhAlert.errorWithLog(mfhLang.text('error_saving_updating_category'), data.responseJSON);
                console.error(data);
            },
            complete: function(data) {
                $modal.find('#action-buttons').find('.cancel-button').removeAttr('disabled');
                $modal.find('#action-buttons').find('.save-button').removeAttr('disabled');
            }
        });
    });
}

function bindDeleteButton() {
    $(document).on('click', '[data-action="delete"]', function() {
        $('#overlay').show();

        var heskUrl = $('p#hesk-path').text();
        var element = categories[$(this).parent().parent().find('[data-property="id"]').text()];

        $.ajax({
            method: 'POST',
            url: heskUrl + 'api/index.php/v1/categories/' + element.id,
            headers: {
                'X-Internal-Call': true,
                'X-HTTP-Method-Override': 'DELETE'
            },
            success: function() {
                mfhAlert.success(mfhLang.text('cat_removed'));
                loadTable();
            },
            error: function(data) {
                $('#overlay').hide();
                mfhAlert.errorWithLog(mfhLang.text('error_deleting_category'), data.responseJSON);
                console.error(data);
            }
        });
    });
}

function bindSortButtons() {
    $(document).on('click', '[data-action="sort"]', function() {
        $('#overlay').show();
        var heskUrl = $('p#hesk-path').text();
        var direction = $(this).data('direction');
        var element = categories[$(this).parent().parent().parent().find('[data-property="id"]').text()];

        $.ajax({
            method: 'POST',
            url: heskUrl + 'api/index.php/v1-internal/categories/' + element.id + '/sort/' + direction,
            headers: { 'X-Internal-Call': true },
            success: function() {
                loadTable();
            },
            error: function(data) {
                mfhAlert.errorWithLog(mfhLang.text('error_sorting_categories'), data.responseJSON);
                console.error(data);
                $('#overlay').hide();
            }
        })
    });
}