$(document).ready(function() {
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        editable: true,
        eventLimit: true,
        events: function(start, end, timezone, callback) {
            $.ajax({
                url: getHelpdeskUrl() + '/internal-api/admin/calendar/?start=' + start + '&end=' + end,
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    //callback w/events here!
                },
                error: function(data) {
                    console.error(data);
                }
            });
        },
        dayClick: function(date, jsEvent, view) {
            displayCreateModal(date, view.name);
        }
    });

    $('input[name="all-day"]').change(function() {
        var hideTimeFields = $(this).is(':checked');

        $('.clockpicker').css('display', hideTimeFields ? 'none' : 'block');
    });
});

function displayCreateModal(date, viewName) {
    $('input[name="name"]').val('');
    $('input[name="location"]').val('');
    $('textarea[name="comments"]').val('');
    $('input[name="create-ticket-date"]').val('');

    var $modal = $('#create-event-modal');
    var formattedDate = date.format('YYYY-MM-DD');
    $modal.find('input[name="start-date"]').val(formattedDate).end()
        .find('input[name="end-date"]').val(formattedDate).end();
    if (viewName === 'month') {
        // Select "All Day"
        $('input[name="all-day"]').prop('checked', true);
        $('.clockpicker').hide();
    } else {
        $('input[name="all-day"]').prop('checked', false);
        $('.clockpicker').show();
        var formattedTime = date.format('h:mm:ss');
        var selectedHour = date.hour();
        $modal.find('input[name="start-time"]').val(formattedTime).end()
            .find('input[name="end-time"]').val(date.hour(selectedHour + 1).format('h:mm:ss'));
    }

    $('#create-event-modal').modal('show');
}