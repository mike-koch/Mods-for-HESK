var categories = [];

$(document).ready(function() {
    loadTable();
    bindEditModal();
    bindModalCancelCallback();
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
                $template.find('a[data-property="number-of-tickets"]')
                    .text(this.numberOfTickets)
                    .attr('href', '#' + this.numberOfTickets);
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
                    $template.find('[data-property="autoassign-link"]').attr('href', '#on')
                        .find('i').attr('title', mfhLang.text('aaon')).addClass('orange');
                } else {
                    $template.find('[data-property="autoassign-link"]').attr('href', '#off')
                        .find('i').attr('title', mfhLang.text('aaoff')).addClass('gray');
                }

                if (this.type === 1) {
                    // Private
                    $template.find('[data-property="type-link"]').attr('href', '#private')
                        .find('i').addClass('fa-lock').attr('title', mfhLang.text('cat_private')).addClass('gray');
                    $template.find('[data-property="generate-link"]')
                        .find('i').removeClass('fa-code').addClass('fa-ban').addClass('red')
                            .attr('title', mfhLang.text('cpric'));
                } else {
                    // Public
                    $template.find('[data-property="type-link"]').attr('href', '#public')
                        .find('i').addClass('fa-unlock-alt').attr('title', mfhLang.text('cat_public')).addClass('blue');
                    $template.find('[data-property="generate-link"]')
                        .find('i').addClass('green').attr('title', mfhLang.text('geco'));
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
            .find('select[name="usage"]').val(element.usage).end();

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

        /*$modal.find('select[name="place"]').val(element.place);
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
            $('[data-toggle="nav-iconpicker"]').iconpicker('setIcon', element.fontIcon);
            $modal.find('#font-icon-group').show();
            $modal.find('#image-url-group').hide();
        }*/

        $modal.modal('show');
    });
}

function bindModalCancelCallback() {
    $('.cancel-callback').click(function() {
        var $editCategoryModal = $('#category-modal');

        $editCategoryModal.find('input[name="background-color"]').val('').colorpicker('destroy').end();
        $editCategoryModal.find('input[name="foreground-color"]').val('').colorpicker('destroy').end();
    });
}