$(document).ready(function() {
    var heskPath = $('p#hesk-path').text();

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
        var ticketId = $('input[type="hidden"][name="orig_id"]').val();
        $.ajax({
            method: 'POST',
            url: heskPath + 'api/v1/staff/tickets/' + ticketId + '/due-date',
            headers: {
                'X-Internal-Call': true,
                'X-HTTP-Method-Override': 'PATCH'
            },
            data: JSON.stringify({
                dueDate: newDueDate === '' ? null : newDueDate
            }),
            success: function() {
                mfhAlert.success(mfhLang.text('ticket_due_date_updated'));
                $readonlyDueDateContainer.find('span#due-date').text(newDueDate == '' ? $('#lang_none').text() : newDueDate);
                $readonlyDueDateContainer.show();
                $editableDueDateContainer.hide();
            },
            error: function() {
                mfhAlert.error(mfhLang.text('error_updating_ticket_due_date'));
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
        var apiUrl = heskPath + 'api/index.php/v1-internal/staff/tickets/' + ticketId + '/resend-email';

        if (replyId !== undefined) {
            apiUrl += '?replyId=' + replyId;
        }

        $this.attr('disabled', true);
        $.ajax({
            method: 'GET',
            url: apiUrl,
            headers: { 'X-Internal-Call': true },
            success: function() {
                mfhAlert.success(mfhLang.text('email_notification_sent'));
            },
            error: function() {
                mfhAlert.error(mfhLang.text('email_notification_resend_failed'));
            },
            complete: function() {
                $this.attr('disabled', false);
            }
        });
    });
});