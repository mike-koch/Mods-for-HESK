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
                    //callback w/events here!
                },
                error: function(data) {
                    console.error(data);
                }
            });
        },
        dayClick: function(date, jsEvent, view) {
            displayCreateModal(date);
        }
    });

    $('input[name="all-day"]').change(function() {
        var hideTimeFields = $(this).is(':checked');

        $('.clockpicker').css('display', hideTimeFields ? 'none' : 'block');
    });
});

function displayCreateModal(date) {
    var $modal = $('#create-event-modal');
    var formattedDate = date.format('YYYY-MM-DD');
    $modal.find('input[name="start-date"]').val(formattedDate).end()
        .find('input[name="end-date"]').val(formattedDate);
    $('#create-event-modal').modal('show');
}