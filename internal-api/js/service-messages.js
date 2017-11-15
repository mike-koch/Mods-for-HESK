var serviceMessages = [];

var g_styles = [];
g_styles["ERROR"] = 4;
g_styles["NOTICE"] = 3;
g_styles["INFO"] = 2;
g_styles["SUCCESS"] = 1;
g_styles["NONE"] = 0;

$(document).ready(function() {
    loadTable();
    bindEditModal();
    bindFormSubmit();
    bindDeleteButton();
    bindCreateModal();
    bindSortButtons();
    bindPreview();
});


function loadTable() {
    $('#overlay').show();
    var heskUrl = $('p#hesk-path').text();
    var $tableBody = $('#table-body');

    $.ajax({
        method: 'GET',
        url: heskUrl + 'api/index.php/v1/service-messages',
        headers: { 'X-Internal-Call': true },
        success: function(data) {
            $tableBody.html('');

            if (data.length === 0) {
                $tableBody.append('<tr><td colspan="4">' + mfhLang.text('no_sm') + '</td></tr>');
                $('#overlay').hide();
                return;
            }

            var first = true;
            var lastElement = null;
            $.each(data, function() {
                var $template = $($('#service-message-template').html());

                $template.find('[data-property="id"]').attr('data-value', this.id);
                $template.find('span[data-property="title"]').html(
                    getFormattedTitle(this.icon, this.title, this.style));
                $template.find('span[data-property="author"]').text(users[this.createdBy].name);
                if (this.published) {
                    $template.find('span[data-property="type"]').text(mfhLang.text('sm_published'));
                } else {
                    $template.find('span[data-property="type"]').text(mfhLang.text('sm_draft'));
                }

                $tableBody.append($template);

                serviceMessages[this.id] = this;

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
            mfhAlert.errorWithLog(mfhLang.text('error_retrieving_sm'), data.responseJSON);
            console.error(data);
        },
        complete: function() {
            $('#overlay').hide();
        }
    });
}

function getFormattedTitle(icon, title, style) {
    var $template = $($('#service-message-title-template').html());

    var alertClass = 'none';
    switch (style) {
        case 'ERROR':
            alertClass = 'alert alert-danger';
            break;
        case 'NOTICE':
            alertClass = 'alert alert-warning';
            break;
        case 'INFO':
            alertClass = 'alert alert-info';
            break;
        case 'SUCCESS':
            alertClass = 'alert alert-success';
            break;
    }
    $template.addClass(alertClass)
        .find('[data-property="icon"]').addClass(icon).end()
        .find('[data-property="title"]').text(title);

    return $template;
}

function getServiceMessagePreview(icon, title, message, style) {
    var $template = $('#service-message-preview-template').html();

    var alertClass = 'none';
    switch (style) {
        case 'ERROR':
            alertClass = 'alert alert-danger';
            break;
        case 'NOTICE':
            alertClass = 'alert alert-warning';
            break;
        case 'INFO':
            alertClass = 'alert alert-info';
            break;
        case 'SUCCESS':
            alertClass = 'alert alert-success';
            break;
    }
    $template = $template.replace('none', alertClass)
        .replace('{{TITLE}}', title)
        .replace('{{MESSAGE}}', message);
    $template = $($template);
    if (icon !== '') {
        $template.find('i.fa').removeClass('fa').addClass(icon);
    }

    return $template;
}

function bindEditModal() {
    $(document).on('click', '[data-action="edit"]', function() {
        var element = serviceMessages[$(this).parent().parent().find('[data-property="id"]').data('value')];
        var $modal = $('#service-message-modal');
        $modal.find('#preview-pane').html('');

        $modal.find('#edit-label').show();
        $modal.find('#create-label').hide();

        $modal.find('input[name="style"][value="' + (g_styles[element.style]) + '"]').prop('checked', 'checked').end()
            .find('input[name="type"][value="' + (element.published ? 0 : 1) + '"]')
                .prop('checked', 'checked').end()
            .find('input[name="title"]').val(element.title).end()
            .find('input[name="id"]').val(element.id).end()
            .find('input[name="order"]').val(element.order).end();
        setIcon(element.icon);
        tinyMCE.get('content').setContent(element.message);

        $modal.modal('show');
    });
}

function bindCreateModal() {
    $('#create-button').click(function() {
        var $modal = $('#service-message-modal');
        $modal.find('#edit-label').hide().end()
            .find('#create-label').show().end()
            .find('input[name="style"][value="0"]').prop('checked', 'checked').end() // "None" style
            .find('input[name="type"][value="0"]').prop('checked', 'checked').end() // Published
            .find('input[name="title"]').val('').end()
            .find('input[name="id"]').val(-1).end()
            .find('input[name="order"]').val('').end()
            .find('#preview-pane').html('').end();
        setIcon('');
        tinyMCE.get('content').setContent('');

        $modal.modal('show');
    });
}

function bindFormSubmit() {
    $('form#service-message').submit(function(e) {
        e.preventDefault();
        var heskUrl = $('p#hesk-path').text();

        var $modal = $('#service-message-modal');

        var styles = [];
        styles[0] = "NONE";
        styles[1] = "SUCCESS";
        styles[2] = "INFO";
        styles[3] = "NOTICE";
        styles[4] = "ERROR";

        var data = {
            icon: $modal.find('input[name="icon"]').val(),
            title: $modal.find('input[name="title"]').val(),
            message: tinyMCE.get('content').getContent(),
            published: $modal.find('input[name="type"]:checked').val() === "0",
            style: styles[$modal.find('input[name="style"]:checked').val()],
            order: $modal.find('input[name="order"]').val()
        };

        var url = heskUrl + 'api/index.php/v1/service-messages/';
        var method = 'POST';

        var serviceMessageId = parseInt($modal.find('input[name="id"]').val());
        if (serviceMessageId !== -1) {
            url += serviceMessageId;
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
                if (serviceMessageId === -1) {
                    mfhAlert.success(mfhLang.text('sm_added'));
                } else {
                    mfhAlert.success(mfhLang.text('sm_mdf'));
                }
                $modal.modal('hide');
                loadTable();
            },
            error: function(data) {
                mfhAlert.errorWithLog(mfhLang.text('error_saving_updating_sm'), data.responseJSON);
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
        var element = serviceMessages[$(this).parent().parent().find('[data-property="id"]').data('value')];

        $.ajax({
            method: 'POST',
            url: heskUrl + 'api/index.php/v1/service-messages/' + element.id,
            headers: {
                'X-Internal-Call': true,
                'X-HTTP-Method-Override': 'DELETE'
            },
            success: function() {
                mfhAlert.success(mfhLang.text('sm_deleted'));
                loadTable();
            },
            error: function(data) {
                $('#overlay').hide();
                mfhAlert.errorWithLog(mfhLang.text('error_deleting_sm'), data.responseJSON);
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
        var element = serviceMessages[$(this).parent().parent().parent().find('[data-property="id"]').data('value')];

        $.ajax({
            method: 'POST',
            url: heskUrl + 'api/index.php/v1-internal/service-messages/' + element.id + '/sort/' + direction,
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

function bindPreview() {
    $('.preview-button').click(function() {
        var styles = [];
        styles[0] = "NONE";
        styles[1] = "SUCCESS";
        styles[2] = "INFO";
        styles[3] = "NOTICE";
        styles[4] = "ERROR";

        var $modal = $('#service-message-modal');
        var data = {
            icon: $modal.find('input[name="icon"]').val(),
            title: $modal.find('input[name="title"]').val(),
            message: tinyMCE.get('content').getContent(),
            published: $modal.find('input[name="type"]:checked').val() === "0",
            style: styles[$modal.find('input[name="style"]:checked').val()],
            order: $modal.find('input[name="order"]').val()
        };

        var preview = getServiceMessagePreview(data.icon, data.title, data.message, data.style);
        $('#preview-pane').html(preview);
    });
}