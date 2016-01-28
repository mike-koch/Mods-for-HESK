$(document).ready(function() {
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        editable: true,
        eventLimit: true,
        timeFormat: 'H:mm',
        axisFormat: 'H:mm',
        events: function(start, end, timezone, callback) {
            $.ajax({
                url: getHelpdeskUrl() + '/internal-api/admin/calendar/?start=' + start + '&end=' + end,
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    var events = [];
                    $(data).each(function() {
                        events.push(buildEvent(this.id, this));
                    });
                    callback(events);
                },
                error: function(data) {
                    console.error(data);
                    $.jGrowl('An error occurred when trying to load events', { theme: 'alert-danger', closeTemplate: '' });
                }
            });
        },
        dayClick: function(date, jsEvent, view) {
            displayCreateModal(date, view.name);
        },
        eventClick: function(event) {
            if (event.url) {
                window.open(event.url, "_blank");
                return false;
            }
            if (event.type !== 'TICKET') {
                displayEditModal(event);
            }
        },
        eventDrop: function(event, delta, revertFunc) {
            if (event.type === 'TICKET') {
                $.ajax({
                    method: 'POST',
                    url: getHelpdeskUrl() + '/internal-api/admin/calendar',
                    data: {
                        trackingId: event.trackingId,
                        action: 'update-ticket',
                        dueDate: event.start.format('YYYY-MM-DD')
                    },
                    success: function() {
                        $.jGrowl('Ticket due date successfully updated', { theme: 'alert-success', closeTemplate: '' });
                    },
                    error: function() {
                        $.jGrowl('An error occurred when trying to update the ticket due date', { theme: 'alert-danger', closeTemplate: '' });
                        revertFunc();
                    }
                });
            } else {
                var start = event.start.format('YYYY-MM-DD');
                if (event.end === null) {
                    event.end = event.start.clone();
                }
                var end = event.end.format('YYYY-MM-DD');
                if (!event.allDay) {
                    start += ' ' + event.start.format('HH:mm:ss');
                    end += ' ' + event.end.format('HH:mm:ss');
                }
                var data = {
                    id: event.id,
                    title: event.title,
                    location: event.location,
                    startTime: start,
                    endTime: end,
                    allDay: event.allDay,
                    comments: event.comments,
                    action: 'update'
                };
                $.ajax({
                    method: 'POST',
                    url: getHelpdeskUrl() + '/internal-api/admin/calendar',
                    data: data,
                    success: function() {
                        $.jGrowl('Event successfully updated', { theme: 'alert-success', closeTemplate: '' });
                    },
                    error: function() {
                        $.jGrowl('An error occurred when trying to update the event', { theme: 'alert-danger', closeTemplate: '' });
                        revertFunc();
                    }
                });
            }
        }
    });

    $('#create-form input[name="all-day"]').change(function() {
        var hideTimeFields = $(this).is(':checked');

        $('#create-form .clockpicker').css('display', hideTimeFields ? 'none' : 'block');
    });

    $('#edit-form input[name="all-day"]').change(function() {
        var hideTimeFields = $(this).is(':checked');

        $('#edit-form .clockpicker').css('display', hideTimeFields ? 'none' : 'block');
    });

    $('#edit-form #delete-button').click(function() {
        var id = $('#edit-form').find('input[name="id"]').val();

        var data = {
            id: id,
            action: 'delete'
        };

        $.ajax({
            method: 'POST',
            url: getHelpdeskUrl() + '/internal-api/admin/calendar',
            data: data,
            success: function() {
                removeFromCalendar(data.id);
                $.jGrowl('Event successfully deleted', { theme: 'alert-success', closeTemplate: '' });
                $('#edit-event-modal').modal('hide');
            },
            error: function(data) {
                $.jGrowl('An error occurred when trying to delete the event', { theme: 'alert-danger', closeTemplate: '' });
            }
        });
    });

    $('#create-form').submit(function(e) {
        e.preventDefault();

        var start = $('#create-form input[name="start-date"]').val();
        var end = $('#create-form input[name="end-date"]').val();
        var dateFormat = 'YYYY-MM-DD';
        var allDay = $('#create-form input[name="all-day"]').is(':checked');

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
            action: 'create',
            type: 'CALENDAR'
        };

        $.ajax({
            method: 'POST',
            url: getHelpdeskUrl() + '/internal-api/admin/calendar',
            data: data,
            success: function(id) {
                addToCalendar(id, data, "Event successfully created");
                $('#create-event-modal').modal('hide');
            },
            error: function(data) {
                $.jGrowl('An error occurred when trying to create the event', { theme: 'alert-danger', closeTemplate: '' });
            }
        });
    });

    $('#edit-form').submit(function(e) {
        e.preventDefault();

        var $form = $('#edit-form');
        var start = $form.find('input[name="start-date"]').val();
        var end = $form.find('input[name="end-date"]').val();
        var dateFormat = 'YYYY-MM-DD';
        var allDay = $form.find('input[name="all-day"]').is(':checked');

        if (!allDay) {
            start += ' ' + $form.find('input[name="start-time"]').val();
            end += ' ' + $form.find('input[name="end-time"]').val();
            dateFormat = 'YYYY-MM-DD HH:mm:ss';
        }

        var data = {
            id: $form.find('input[name="id"]').val(),
            title: $form.find('input[name="name"]').val(),
            location: $form.find('input[name="location"]').val(),
            startTime: moment(start).format(dateFormat),
            endTime: moment(end).format(dateFormat),
            allDay: allDay,
            comments: $form.find('textarea[name="comments"]').val(),
            action: 'update'
        };

        $.ajax({
            method: 'POST',
            url: getHelpdeskUrl() + '/internal-api/admin/calendar',
            data: data,
            success: function() {
                removeFromCalendar(data.id);
                addToCalendar(data.id, data, "Event successfully updated");
                $('#edit-event-modal').modal('hide');
            },
            error: function(data) {
                $.jGrowl('An error occurred when trying to update the event', { theme: 'alert-danger', closeTemplate: '' });
            }
        });
    });
});

function addToCalendar(id, event, successMessage) {
    var eventObject = buildEvent(id, event);
    $('#calendar').fullCalendar('renderEvent', eventObject);
    $.jGrowl(successMessage, { theme: 'alert-success', closeTemplate: '' });
}

function removeFromCalendar(id) {
    $('#calendar').fullCalendar('removeEvents', id);
}

function buildEvent(id, dbObject) {
    if (dbObject.type == 'TICKET') {
        return {
            title: dbObject.title,
            trackingId: dbObject.trackingId,
            start: moment(dbObject.startTime),
            url: dbObject.url,
            color: 'green',
            allDay: true,
            type: dbObject.type
        };
    }

    return {
        id: id,
        title: dbObject.title,
        allDay: dbObject.allDay,
        start: moment(dbObject.startTime),
        end: moment(dbObject.endTime),
        comments: dbObject.comments,
        location: dbObject.location,
        type: dbObject.type
    };
}

function displayCreateModal(date, viewName) {
    var $form = $('#create-form');
    $form.find('input[name="name"]').val('').end()
        .find('input[name="location"]').val('').end()
        .find('textarea[name="comments"]').val('').end();

    var $modal = $('#create-event-modal');
    var formattedDate = date.format('YYYY-MM-DD');
    $modal.find('input[name="start-date"]').val(formattedDate).end()
        .find('input[name="end-date"]').val(formattedDate).end();
    if (viewName === 'month') {
        // Select "All Day"
        $form.find('input[name="all-day"]').prop('checked', true).end()
            .find('.clockpicker').hide();
    } else {
        $form.find('input[name="all-day"]').prop('checked', false).end()
            .find('.clockpicker').show();
        var formattedTime = date.format('HH:mm:ss');
        var selectedHour = date.hour();
        $modal.find('input[name="start-time"]').val(formattedTime).end()
            .find('input[name="end-time"]').val(date.hour(selectedHour + 1).format('HH:mm:ss'));
    }

    $modal.modal('show');
}

function displayEditModal(date) {
    var $form = $('#edit-form');

    if (date.end === null) {
        // FullCalendar will set the end date to null if it is the same as the start date.
        date.end = date.start.clone();
    }

    if (date.allDay) {
        $form.find('input[name="all-day"]').prop('checked', true).end()
            .find('input[name="start-time"]').hide().end()
            .find('input[name="end-time"]').hide().end();
    } else {
        $form.find('input[name="all-day"]').prop('checked', false).end()
            .find('.clockpicker').show().end()
            .find('input[name="start-time"]').val(date.start.format('HH:mm:ss')).end()
            .find('input[name="end-time"]').val(date.end.format('HH:mm:ss')).end();
    }

    $form.find('input[name="name"]').val(date.title).end()
        .find('input[name="location"]').val(date.location).end()
        .find('textarea[name="comments"]').val(date.comments).end()
        .find('input[name="start-date"]').val(date.start.format('YYYY-MM-DD')).end()
        .find('input[name="end-date"]').val(date.end.format('YYYY-MM-DD')).end()
        .find('input[name="id"]').val(date.id).end();

    $('#edit-event-modal').modal('show');
}