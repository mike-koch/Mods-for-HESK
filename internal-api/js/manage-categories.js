var categories = [];

$(document).ready(function() {
    loadTable();
    bindEditModal();
    bindModalCancelCallback();
    bindFormSubmit();
    bindDeleteButton();
    bindCreateModal();
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

            if (data.length === 0) {
                mfhAlert.error("I couldn't find any categories :(", "No categories found");
                $('#overlay').hide();
                return;
            }

            var totalNumberOfTickets = 0;
            $.each(data, function() {
                totalNumberOfTickets += this.numberOfTickets;
            });

            var first = true;
            var lastElement = null;
            $.each(data, function() {
                var $template = $($('#category-row-template').html());

                $template.find('span[data-property="id"]').text(this.id).attr('data-value', this.id);
                var $nameField = $template.find('span[data-property="category-name"]');
                if (this.foregroundColor === 'AUTO') {
                    $nameField.addClass('background-volatile');
                } else {
                    $nameField.css('color', this.foregroundColor);
                }
                $nameField.css('background', this.backgroundColor);
                if (this.displayBorder && this.foregroundColor !== 'AUTO') {
                    $nameField.css('border', 'solid 1px ' + this.foregroundColor);
                }
                $nameField.html(this.name);
                var $priority = $template.find('span[data-property="priority"]');
                if (this.priority === 0) {
                    // Critical
                    $priority.text(mfhLang.text('critical')).addClass('critical');
                } else if (this.priority === 1) {
                    // High
                    $priority.text(mfhLang.text('high')).addClass('important');
                } else if (this.priority === 2) {
                    // Medium
                    $priority.text(mfhLang.text('medium')).addClass('medium');
                } else {
                    // Low
                    $priority.text(mfhLang.text('low')).addClass('normal');
                }
                var linkPattern = $('input[name="show-tickets-path"]').val();
                $template.find('a[data-property="number-of-tickets"]')
                    .text(this.numberOfTickets)
                    .attr('href', linkPattern.replace('{0}', this.id));
                var percentText = mfhLang.text('perat');
                var percentage = Math.round(this.numberOfTickets / totalNumberOfTickets * 100);
                $template.find('div.progress').attr('title', percentText.replace('%s', percentage + '%'));
                $template.find('div.progress-bar').attr('aria-value-now', percentage).css('width', percentage + '%');

                $template.find('[data-property="generate-link"]').find('i').attr('title', mfhLang.text('geco'));

                if (this.usage === 1) {
                    // Tickets only
                    $template.find('.fa-calendar').removeClass('fa-calendar');
                } else if (this.usage === 2) {
                    // Events only
                    $template.find('.fa-ticket').removeClass('fa-ticket');
                }

                if (this.autoAssign) {
                    $template.find('.fa-bolt').addClass('orange');
                    $template.find('[data-property="autoassign"]').text(mfhLang.text('enabled_title_case'));
                } else {
                    $template.find('.fa-bolt').addClass('gray');
                    $template.find('[data-property="autoassign"]').text(mfhLang.text('disabled_title_case'));
                }

                if (this.type === 1) {
                    // Private
                    $template.find('[data-property="type"]').text(mfhLang.text('cat_private'));
                    $template.find('.fa-lock').show();
                    $template.find('[data-property="generate-link"]').find('i')
                        .addClass('fa-ban')
                        .addClass('red')
                        .attr('title', mfhLang.text('cpric'));
                } else {
                    // Public
                    $template.find('[data-property="type"]').text(mfhLang.text('cat_public'));
                    $template.find('.fa-unlock-alt').show();
                    $template.find('[data-property="generate-link"]').find('i')
                        .addClass('fa-code')
                        .addClass('green')
                        .attr('title', mfhLang.text('geco'));
                }

                $tableBody.append($template);

                categories[this.id] = this;

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
            mfhAlert.errorWithLog(mfhLang.text('Something bad happened...'), data.responseJSON);
            console.error(data);
        },
        complete: function() {
            refreshBackgroundVolatileItems();
            $('#overlay').hide();
        }
    });
}

function bindEditModal() {
    $(document).on('click', '[data-action="edit"]', function() {
        var element = categories[$(this).parent().parent().find('[data-property="id"]').text()];
        var $modal = $('#category-modal');

        $modal.find('#title-edit-category').show();
        $modal.find('#title-add-category').hide();

        $modal.find('input[name="name"]').val(element.name).end()
            .find('select[name="priority"]').val(element.priority).end()
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
        if (foregroundColor != '' && foregroundColor !== 'AUTO') {
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
        $modal.find('input[name="type"][value="' + (element.type ? 1 : 0) + '"]');
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
    $('.cancel-callback').click(function() {
        var $editCategoryModal = $('#category-modal');

        $editCategoryModal.find('input[name="background-color"]').val('').colorpicker('destroy').end();
        $editCategoryModal.find('input[name="foreground-color"]').val('').colorpicker('destroy').end();
        $editCategoryModal.find('input[name="display-border"][value="1"]').prop('checked');
        $editCategoryModal.find('input[name="display-border"][value="0"]').prop('checked');
        $editCategoryModal.find('input[name="autoassign"][value="1"]').prop('checked');
        $editCategoryModal.find('input[name="autoassign"][value="0"]').prop('checked');
    });
}

function bindFormSubmit() {
    $('form#manage-category').submit(function(e) {
        e.preventDefault();
        var heskUrl = $('p#hesk-path').text();

        var $modal = $('#category-modal');

        var data = {
            autoassign: $modal.find('input[name="autoassign"]').val() === 'true',
            backgroundColor: $modal.find('input[name="background-color"]').val(),
            description: $modal.find('textarea[name="description"]').val(),
            displayBorder: $modal.find('input[name="display-border"]:checked').val() === '1',
            foregroundColor: $modal.find('input[name="foreground-color"]').val() === '' ? 'AUTO' : $modal.find('input[name="foreground-color"]').val(),
            name: $modal.find('input[name="name"]').val(),
            priority: parseInt($modal.find('select[name="priority"]').val()),
            type: parseInt($modal.find('input[name="type"]').val()),
            usage: parseInt($modal.find('select[name="usage"]').val()),
            catOrder: parseInt($modal.find('input[name="cat-order"]').val())
        };

        var url = heskUrl + 'api/index.php/v1/categories/';
        var method = 'POST';

        var categoryId = $modal.find('input[name="id"]').val();
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
                if (categoryId === -1) {
                    mfhAlert.success('CREATED');
                } else {
                    mfhAlert.success('SAVED');
                }
                $modal.modal('hide');
                loadTable();
            },
            error: function(data) {
                mfhAlert.errorWithLog('ERROR SAVING/CREATING', data.responseJSON);
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