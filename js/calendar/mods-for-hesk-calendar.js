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
                }
            });
        },
        dayClick: function(date, jsEvent, view) {
            displayCreateModal(date, view.name);
        },
        eventClick: function(event) {
            displayEditModal(event);
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
                console.error(data);
            }
        });
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

        $.ajax({
            method: 'POST',
            url: getHelpdeskUrl() + '/internal-api/admin/calendar',
            data: data,
            success: function(id) {
                addToCalendar(id, data, "Event successfully created");
                $('#create-event-modal').modal('hide');
            },
            error: function(data) {
                console.error(data);
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
        var createTicketDate = null;
        var assignTo = null;
        if ($form.find('input[name="assign-to"]').length) {
            assignTo = $form.find('input[name="assign-to"]').val();
        } else if ($form.find('select[name="assign-to"]').length) {
            assignTo = $form.find('select[name="assign-to"]').val();
        }

        if ($form.find('input[name="create-ticket-date"]').val() != '') {
            createTicketDate = moment($form.find('input[name="create-ticket-date"]').val()).format('YYYY-MM-DD');
        }
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
            createTicketDate: createTicketDate,
            assignTo: assignTo,
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
                console.error(data);
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
    var createTicketDate = null;
    if (dbObject.createTicketDate != null) {
        createTicketDate = moment(dbObject.createTicketDate);
    }
    return {
        id: id,
        title: dbObject.title,
        allDay: dbObject.allDay,
        start: moment(dbObject.startTime),
        end: moment(dbObject.endTime),
        comments: dbObject.comments,
        createTicketDate: createTicketDate,
        assignTo: dbObject.assignTo,
        location: dbObject.location
    };
}

function displayCreateModal(date, viewName) {
    var $form = $('#create-form');
    $form.find('input[name="name"]').val('').end()
        .find('input[name="location"]').val('').end()
        .find('textarea[name="comments"]').val('').end()
        .find('input[name="create-ticket-date"]').val('').end();

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
        var formattedTime = date.format('H:mm:ss');
        var selectedHour = date.hour();
        $modal.find('input[name="start-time"]').val(formattedTime).end()
            .find('input[name="end-time"]').val(date.hour(selectedHour + 1).format('H:mm:ss'));
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
            .find('input[name="start-time"]').val(date.start.format('H:mm:ss')).end()
            .find('input[name="end-time"]').val(date.end.format('H:mm:ss')).end();
    }

    if (date.createTicketDate != null) {
        $form.find('input[name="create-ticket-date"]').val(date.createTicketDate.format('YYYY-MM-DD')).end();
    }

    $form.find('input[name="name"]').val(date.title).end()
        .find('input[name="location"]').val(date.location).end()
        .find('textarea[name="comments"]').val(date.comments).end()
        .find('input[name="start-date"]').val(date.start.format('YYYY-MM-DD')).end()
        .find('input[name="end-date"]').val(date.end.format('YYYY-MM-DD')).end()
        .find('input[name="id"]').val(date.id).end();

    $('#edit-event-modal').modal('show');
}