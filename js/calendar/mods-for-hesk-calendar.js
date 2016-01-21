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

    $('#create-form input[name="all-day"]').change(function() {
        var hideTimeFields = $(this).is(':checked');

        $('#create-form .clockpicker').css('display', hideTimeFields ? 'none' : 'block');
    });

    $('#create-form').submit(function(e) {
        e.preventDefault();

        var start = $('#create-form input[name="start-date"]').val();
        var end = $('#create-form input[name="end-date"]').val();
        var dateFormat = 'YYYY-MM-DD';
        var allDay = $('#create-form input[name="all-day"]').is(':checked');
        var createTicketDate = null;
        var assignTo = null;
        if ($('#create-form input[name="assign-to"]').length) {
            assignTo = $('#create-form input[name="assign-to"]').val();
        } else if ($('#create-form select[name="assign-to"]').length) {
            assignTo = $('#create-form select[name="assign-to"]').val();
        }

        if ($('#create-form input[name="create-ticket-date"]').val() != '') {
            createTicketDate = moment($('#create-form input[name="create-ticket-date"]').val()).format('YYYY-MM-DD');
        }
        if (!allDay) {
            start += ' ' + $('#create-form input[name="start-time"]').val();
            end += ' ' + $('#create-form input[name="end-time"]').val();
            dateFormat = 'YYYY-MM-DD HH:mm:ss';
        }

        var data = {
            title: $('#create-form input[name="name"]').val(),
            location: $('#create-form input[name="location"]').val(),
            startTime: moment(start).format(dateFormat),
            endTime: moment(end).format(dateFormat),
            allDay: allDay,
            comments: $('#create-form textarea[name="comments"]').val(),
            createTicketDate: createTicketDate,
            assignTo: assignTo,
            action: 'create'
        };

        console.log(data);

        $.ajax({
            method: 'POST',
            url: getHelpdeskUrl() + '/internal-api/admin/calendar',
            data: data,
            success: function(id) {
                addToCalendar(id, data);
                $('#create-event-modal').modal('hide');
            },
            failure: function(data) {
                console.log(data);
            }
        });
    });
});

function addToCalendar(id, event) {
    var eventObject = {
        id: id,
        title: event.title,
        allDay: event.allDay,
        start: event.startTime,
        end: event.endTime,
        comments: event.comments,
        createTicketDate: event.createTicketDate,
        assignTo: event.assignTo,
        location: event.location
    };
    $('#calendar').fullCalendar('renderEvent', eventObject);
}

function displayCreateModal(date, viewName) {
    $('#create-form input[name="name"]').val('');
    $('#create-form input[name="location"]').val('');
    $('#create-form textarea[name="comments"]').val('');
    $('#create-form input[name="create-ticket-date"]').val('');

    var $modal = $('#create-event-modal');
    var formattedDate = date.format('YYYY-MM-DD');
    $modal.find('input[name="start-date"]').val(formattedDate).end()
        .find('input[name="end-date"]').val(formattedDate).end();
    if (viewName === 'month') {
        // Select "All Day"
        $('#create-form input[name="all-day"]').prop('checked', true);
        $('#create-form .clockpicker').hide();
    } else {
        $('#create-form input[name="all-day"]').prop('checked', false);
        $('#create-form .clockpicker').show();
        var formattedTime = date.format('h:mm:ss');
        var selectedHour = date.hour();
        $modal.find('input[name="start-time"]').val(formattedTime).end()
            .find('input[name="end-time"]').val(date.hour(selectedHour + 1).format('h:mm:ss'));
    }

    $('#create-event-modal').modal('show');
}