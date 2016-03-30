$(document).ready(function() {
    $('#calendar').fullCalendar({
        header: {
            left: 'prevYear,prev,next,nextYear today',
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
        eventDrop: respondToDragAndDrop,
        eventResize: respondToDragAndDrop,
        eventMouseover: function(event) {
            if (event.type === 'TICKET') {
                // Don't build a popover for tickets
                return;
            }

            var contents = $('.popover-template').html();
            var $contents = $(contents);

            var format = 'dddd, MMMM Do YYYY';
            var endDate = event.end == null ? event.start : event.end;

            if (!event.allDay) {
                format += ', HH:mm';
            }

            if (event.location === '') {
                $contents.find('.popover-location').hide();
            }

            $contents.find('.popover-category span').text(event.categoryName).end()
                .find('.popover-location span').text(event.location).end()
                .find('.popover-from span').text(event.start.format(format)).end()
                .find('.popover-to span').text(endDate.format(format));
            var $eventMarkup = $(this);
            $eventMarkup.popover({
                title: event.title,
                html: true,
                content: $contents,
                animation: true,
                container: 'body',
                placement: 'auto'
            }).popover('show');
        },
        eventMouseout: function(event) {
            if (event.type === 'TICKET') {
                // There's no popover to destroy
                return;
            }

            $(this).popover('destroy');
        },
        dayRender: function(date, cell) {
            var $cell = $(cell);
            $cell.attr('title', 'Click to add event');
        }
    });

    $('#create-event-button').click(function() {
        // Hard-code the view name so the modal treats this as an "all-day" event.
        var viewName = 'month';
        displayCreateModal(moment(), viewName);
    })


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
            url: getHelpdeskUrl() + '/internal-api/admin/calendar/',
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
            categoryId: $('#create-form select[name="category"]').val(),
            action: 'create',
            type: 'CALENDAR',
            categoryColor: $('#create-form select[name="category"] :selected').attr('data-color'),
            categoryName: $('#create-form select[name="category"] :selected').text().trim(),
            reminderValue: $('#create-form input[name="reminder-value"]').val(),
            reminderUnits: $('#create-form select[name="reminder-unit"]').val()
        };

        $.ajax({
            method: 'POST',
            url: getHelpdeskUrl() + '/internal-api/admin/calendar/',
            data: data,
            success: function(id) {
                addToCalendar(id, data, "Event successfully created");
                $('#create-event-modal').modal('hide');
                updateCategoryVisibility();
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
            categoryId: $form.find('select[name="category"]').val(),
            categoryColor: $form.find('select[name="category"] :selected').attr('data-color'),
            categoryName: $form.find('select[name="category"] :selected').text().trim(),
            action: 'update',
            reminderValue: $form.find('input[name="reminder-value"]').val(),
            reminderUnits: $form.find('select[name="reminder-unit"]').val()
        };

        $.ajax({
            method: 'POST',
            url: getHelpdeskUrl() + '/internal-api/admin/calendar/',
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

    $('input[name="category-toggle"]').change(updateCategoryVisibility);
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
        var endOfDay = moment(dbObject.startTime)
            .set('hour', 23)
            .set('minute', 59)
            .set('second', 59)
            .set('millisecond', 999);

        return {
            title: dbObject.title,
            trackingId: dbObject.trackingId,
            start: moment(dbObject.startTime),
            url: dbObject.url,
            color: dbObject.categoryColor === '' || dbObject.categoryColor === null ? '#fff' : dbObject.categoryColor,
            allDay: true,
            type: dbObject.type,
            categoryId: dbObject.categoryId,
            className: 'category-' + dbObject.categoryId,
            textColor: calculateTextColor(dbObject.categoryColor)
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
        type: dbObject.type,
        categoryId: dbObject.categoryId,
        categoryName: dbObject.categoryName,
        className: 'category-' + dbObject.categoryId,
        color: dbObject.categoryColor === '' || dbObject.categoryColor === null ? '#fff' : dbObject.categoryColor,
        textColor: calculateTextColor(dbObject.categoryColor),
        reminderValue: dbObject.reminderValue == null ? '' : dbObject.reminderValue,
        reminderUnits: dbObject.reminderUnits
    };
}

function calculateTextColor(color) {
    if (color === null || color === '') {
        return 'black';
    }

    var red = 0;
    var green = 0;
    var blue = 0;

    // If hex value is 3 characters, take each value and concatenate it to itself
    if (color.length === 3) {
        red = parseInt(color.substring(1, 2), 16);
        green = parseInt(color.substring(2, 3), 16);
        blue = parseInt(color.substring(3, 4), 16);
    } else {
        red = parseInt(color.substring(1, 3), 16);
        green = parseInt(color.substring(3, 5), 16);
        blue = parseInt(color.substring(5, 7), 16);
    }

    var gray = red * 0.299 + green * 0.587 + blue * 0.114;

    return gray > 186 ? 'black' : 'white';
}

function displayCreateModal(date, viewName) {
    var $form = $('#create-form');
    $form.find('input[name="name"]').val('').end()
        .find('input[name="location"]').val('').end()
        .find('textarea[name="comments"]').val('').end()
        .find('select[name="category"]').val('').end()
        .find('select[name="reminder-unit"]').val(0).end()
        .find('input[name="reminder-value"]').val('').end();

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
        .find('input[name="id"]').val(date.id).end()
        .find('input[name="reminder-value"]').val(date.reminderValue).end()
        .find('select[name="reminder-unit"]').val(date.reminderUnits).end();

    var createTicketLink = getHelpdeskUrl() + '/' + getAdminDirectory() + '/new_ticket.php?subject=';
    createTicketLink += encodeURI('[' + date.start.format('YYYY-MM-DD') + '] ' + date.title);
    if (date.location != '') {
        createTicketLink += encodeURI(' @ ' + date.location);
    }
    createTicketLink += encodeURI('&message=' + date.comments);
    createTicketLink += encodeURI('&category=' + date.categoryId);

    $form.find('#create-ticket-button').prop('href', createTicketLink);

    $form.find('select[name="category"] option[value="' + date.categoryId + '"]').prop('selected', true);

    $('#edit-event-modal').modal('show');
}

function updateCategoryVisibility() {
    $('input[name="category-toggle"]').each(function() {
        $this = $(this);

        if ($this.is(':checked')) {
            $('.category-' + $this.val()).show();
        } else {
            $('.category-' + $this.val()).hide();
        }
    });
}

function respondToDragAndDrop(event, delta, revertFunc) {
    if (event.type === 'TICKET') {
        $.ajax({
            method: 'POST',
            url: getHelpdeskUrl() + '/internal-api/admin/calendar/',
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
            categoryId: event.categoryId,
            action: 'update',
            reminderValue: event.reminderValue,
            reminderUnits: event.reminderUnits
        };
        $.ajax({
            method: 'POST',
            url: getHelpdeskUrl() + '/internal-api/admin/calendar/',
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