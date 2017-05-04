$(document).ready(function() {
    var $readonlyDueDateContainer = $('#readonly-due-date');
    var $changeButton = $readonlyDueDateContainer.find('#change-button');
    var $editableDueDateContainer = $('#editable-due-date');
    $changeButton.click(function() {
        $readonlyDueDateContainer.hide();
        $editableDueDateContainer.show();
        if ($readonlyDueDateContainer.find('span#due-date').text().trim() == 'None') {
            $editableDueDateContainer.find('input[type="text"][name="due-date"]').text('').val('');
        }
    });

    $editableDueDateContainer.find('#cancel').click(function() {
        $editableDueDateContainer.hide();
        $editableDueDateContainer.find('input[name="due-date"]').val($readonlyDueDateContainer.find('span#due-date').text().trim());
        $readonlyDueDateContainer.show();
    });

    $editableDueDateContainer.find('#submit').click(function() {
        var newDueDate = $editableDueDateContainer.find('input[type="text"][name="due-date"]').val();
        $.ajax({
            method: 'POST',
            url: getHelpdeskUrl() + '/internal-api/admin/calendar/',
            data: {
                trackingId: $('input[type="hidden"][name="track"]').val(),
                action: 'update-ticket',
                dueDate: newDueDate
            },
            success: function() {
                $.jGrowl($('#lang_ticket_due_date_updated').text(), { theme: 'alert-success', closeTemplate: '' });
                $readonlyDueDateContainer.find('span#due-date').text(newDueDate == '' ? $('#lang_none').text() : newDueDate);
                $readonlyDueDateContainer.show();
                $editableDueDateContainer.hide();
            },
            error: function() {
                $.jGrowl($('#lang_error_updating_ticket_due_date').text(), { theme: 'alert-danger', closeTemplate: '' });
            }
        });
    });

    $('#related-tickets-link').click(function() {
        $(this).hide();
        $('.related-ticket').show();
    });

    $('button[data-action="resend-email-notification"]').click(function() {
        var $this = $(this);

        var ticketId = $this.data('ticket-id');
        var replyId = $this.data('reply-id');
        var heskUrl = $('span#heskUrl').text();
        var apiUrl = heskUrl + '/api/v1-internal/staff/tickets/' + ticketId + '/resend-email?replyId=' + replyId;

        $.ajax({
            method: 'GET',
            url: apiUrl,
            headers: { 'X-Internal-Call': true },
            success: function() {
                $.jGrowl("Email notification sent!", { theme: 'alert-success', closeTemplate: '' });
            },
            error: function() {
                $.jGrowl("Error occurred when trying to send notification email", { theme: 'alert-danger', closeTemplate: '' });
            }
        });
    });

    window.onbeforeunload = function (e) {
        e = e || window.event;

        var plaintextEditorHasContents = $('textarea[name="message"]').val() !== '';
        var htmlEditorHasContents = false;

        if (tinymce.get("message") !== undefined) {
            plaintextEditorHasContents = false;
            htmlEditorHasContents = tinymce.get("message").getContent() !== '';
        }

        if (plaintextEditorHasContents || htmlEditorHasContents) {
            var $langText = $('#lang_ticket_message_contents_exist');

            // For IE and Firefox prior to version 4
            if (e) {
                e.returnValue = $langText.text();
            }

            // For Safari
            return $langText.text();
        }
    };
});