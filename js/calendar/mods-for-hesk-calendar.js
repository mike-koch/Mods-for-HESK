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
            console.log('in events');
            $.ajax({
                url: getHelpdeskUrl() + '/internal-api/admin/calendar/?start=' + start + '&end=' + end,
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.info(data);
                    //callback w/events here!
                },
                error: function(data) {
                    console.error(data);
                }
            });
        }
    });
});