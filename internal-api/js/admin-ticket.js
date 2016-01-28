$(document).ready(function() {
    var $readonlyDueDateContainer = $('#readonly-due-date');
    var $editableDueDateContainer = $('#editable-due-date');
    var $dueDateButton = $('#due-date-button');
    $dueDateButton.click(function() {
        $readonlyDueDateContainer.hide();
        $editableDueDateContainer.show();
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
            url: getHelpdeskUrl() + '/internal-api/admin/calendar',
            data: {
                trackingId: $('input[type="hidden"][name="track"]').val(),
                action: 'update-ticket',
                dueDate: newDueDate
            },
            success: function() {
                $.jGrowl('Ticket due date successfully updated', { theme: 'alert-success', closeTemplate: '' });
                $readonlyDueDateContainer.find('span#due-date').text(newDueDate);
                $readonlyDueDateContainer.show();
                $editableDueDateContainer.hide();
            },
            error: function() {
                $.jGrowl('An error occurred when trying to update the ticket due date', { theme: 'alert-danger', closeTemplate: '' });
            }
        });
    });
});