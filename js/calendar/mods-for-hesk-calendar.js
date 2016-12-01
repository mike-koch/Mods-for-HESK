$(document).ready(function() {
    $('#calendar').fullCalendar({
        header: {
            left: 'prevYear,prev,next,nextYear today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay,listWeek'
        },
        editable: true,
        eventLimit: true,
        timeFormat: 'H:mm',
        axisFormat: 'H:mm',
        firstDay: $('#setting_first_day_of_week').text(),
        defaultView: $('#setting_default_view').text().trim(),
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
                    $.jGrowl($('#lang_error_loading_events').text(), { theme: 'alert-danger', closeTemplate: '' });
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
            var contents = $('.popover-template').html();
            var $contents = $(contents);

            var format = 'dddd, MMMM Do YYYY';
            var endDate = event.end == null ? event.start : event.end;

            if (event.allDay) {
                endDate = event.end.clone();
                endDate.add(-1, 'days');
            }

            if (!event.allDay && event.type !== 'TICKET') {
                format += ', HH:mm';
            }

            if (event.type === 'TICKET') {
                contents = $('.ticket-popover-template').html();
                $contents = $(contents);

                if (event.owner === null) {
                    $contents.find('.popover-owner').hide();
                }

                $contents.find('.popover-tracking-id span').text(event.trackingId).end()
                    .find('.popover-owner span').text(event.owner).end()
                    .find('.popover-subject span').text(event.subject).end()
                    .find('.popover-category span').text(event.categoryName).end()
                    .find('.popover-priority span').text(event.priority);
            } else {
                if (event.location === '') {
                    $contents.find('.popover-location').hide();
                }

                $contents.find('.popover-category span').text(event.categoryName).end()
                    .find('.popover-location span').text(event.location).end()
                    .find('.popover-from span').text(event.start.format(format)).end()
                    .find('.popover-to span').text(endDate.format(format)).end()
                    .find('.popover-comments span').text(event.comments);
            }

            var $eventMarkup = $(this);

            var eventTitle = event.title;
            if (event.fontIconMarkup != undefined) {
                eventTitle = event.fontIconMarkup + '&nbsp;' + eventTitle;
            }

            $eventMarkup.popover({
                title: eventTitle,
                html: true,
                content: $contents,
                animation: true,
                container: 'body',
                placement: 'auto'
            }).popover('show');
        },
        eventMouseout: function() {
            $(this).popover('destroy');
        },
        dayRender: function(date, cell) {
            var $cell = $(cell);
            $cell.attr('title', 'Click to add event');
        },
        eventRender: function(event, element) {
            if (event.type === 'TICKET' && moment(event.start).endOf("day").isBefore(moment())) {
                $('[data-date="' + event.start.format('YYYY-MM-DD') + '"]').css('background', '#f2dede');
            }

            if (event.fontIconMarkup !== undefined) {
                element.find('span.fc-title').html(event.fontIconMarkup + '&nbsp;' + element.find('span.fc-title').text());
            }
        }
    });

    $('#create-event-button').click(function() {
        // Hard-code the view name so the modal treats this as an "all-day" event.
        var viewName = 'month';
        displayCreateModal(moment(), viewName);
    });


    var $createForm = $('#create-form');
    $createForm.find('input[name="all-day"]').change(function() {
        var hideTimeFields = $(this).is(':checked');

        $createForm.find('.clockpicker').css('display', hideTimeFields ? 'none' : 'block');
    });

    var $editForm = $('#edit-form');
    $editForm.find('input[name="all-day"]').change(function() {
        var hideTimeFields = $(this).is(':checked');

        $editForm.find('.clockpicker').css('display', hideTimeFields ? 'none' : 'block');
    });

    $editForm.find('#delete-button').click(function() {
        var id = $editForm.find('input[name="id"]').val();

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
                $.jGrowl($('#lang_event_deleted').text(), { theme: 'alert-success', closeTemplate: '' });
                $('#edit-event-modal').modal('hide');
            },
            error: function() {
                $.jGrowl($('#lang_error_deleting_event').text(), { theme: 'alert-danger', closeTemplate: '' });
            }
        });
    });

    $createForm.submit(function(e) {
        e.preventDefault();

        var start = $createForm.find('input[name="start-date"]').val();
        var end = $createForm.find('input[name="end-date"]').val();
        var dateFormat = 'YYYY-MM-DD';
        var allDay = $createForm.find('input[name="all-day"]').is(':checked');

        if (!allDay) {
            start += ' ' + $createForm.find('input[name="start-time"]').val();
            end += ' ' + $createForm.find('input[name="end-time"]').val();
            dateFormat = 'YYYY-MM-DD HH:mm:ss';
        }

        var data = {
            title: $createForm.find('input[name="name"]').val(),
            location: $createForm.find('input[name="location"]').val(),
            startTime: moment(start).format(dateFormat),
            endTime: moment(end).format(dateFormat),
            allDay: allDay,
            comments: $createForm.find('textarea[name="comments"]').val(),
            categoryId: $createForm.find('select[name="category"]').val(),
            action: 'create',
            type: 'CALENDAR',
            categoryColor: $createForm.find('select[name="category"] :selected').attr('data-color'),
            categoryName: $createForm.find('select[name="category"] :selected').text().trim(),
            reminderValue: $createForm.find('input[name="reminder-value"]').val(),
            reminderUnits: $createForm.find('select[name="reminder-unit"]').val()
        };

        $.ajax({
            method: 'POST',
            url: getHelpdeskUrl() + '/internal-api/admin/calendar/',
            data: data,
            success: function(id) {
                addToCalendar(id, data, $('#lang_event_created').text());
                $('#create-event-modal').modal('hide');
                updateCategoryVisibility();
            },
            error: function() {
                $.jGrowl($('#lang_error_creating_event').text(), { theme: 'alert-danger', closeTemplate: '' });
            }
        });
    });

    $editForm.submit(function(e) {
        e.preventDefault();

        var $form = $editForm;
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
                addToCalendar(data.id, data, $('#lang_event_updated').text());
                $('#edit-event-modal').modal('hide');
            },
            error: function() {
                $.jGrowl($('#lang_error_updating_event').text(), { theme: 'alert-danger', closeTemplate: '' });
            }
        });
    });

    $('div[data-name="category-toggle"]').click(updateCategoryVisibility);
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
            subject: dbObject.subject,
            trackingId: dbObject.trackingId,
            start: moment(dbObject.startTime),
            url: dbObject.url,
            color: dbObject.categoryColor === '' || dbObject.categoryColor === null ? '#fff' : dbObject.categoryColor,
            allDay: true,
            type: dbObject.type,
            categoryId: dbObject.categoryId,
            categoryName: dbObject.categoryName,
            className: 'category-' + dbObject.categoryId,
            owner: dbObject.owner,
            priority: dbObject.priority,
            textColor: calculateTextColor(dbObject.categoryColor),
            fontIconMarkup: getIcon(dbObject)
        };
    }

    var endTime = moment(dbObject.endTime);
    if (dbObject.allDay) {
        endTime.add(1, 'days');
    }

    return {
        id: id,
        title: dbObject.title,
        allDay: dbObject.allDay,
        start: moment(dbObject.startTime),
        end: endTime,
        realEnd: moment(dbObject.endTime),
        comments: dbObject.comments,
        location: dbObject.location,
        type: dbObject.type,
        categoryId: dbObject.categoryId,
        categoryName: dbObject.categoryName,
        className: 'category-' + dbObject.categoryId,
        color: dbObject.categoryColor === '' || dbObject.categoryColor === null ? '#fff' : dbObject.categoryColor,
        textColor: calculateTextColor(dbObject.categoryColor),
        reminderValue: dbObject.reminderValue == null ? '' : dbObject.reminderValue,
        reminderUnits: dbObject.reminderUnits,
        fontIconMarkup: '<i class="fa fa-calendar"></i>'
    };
}

function getIcon(dbObject) {
    var endOfDay = moment(dbObject.startTime).endOf("day");

    if (moment(endOfDay).isBefore(moment())) {
        return '<i class="fa fa-exclamation-triangle"></i>';
    }

    return '<i class="fa fa-ticket"></i>';
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
        .find('select[name="category"]').val($form.find('select[name="category"] option:first-child').val()).end()
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

    var endDate = date.end.clone();
    if (date.allDay) {
        endDate.add(-1, 'days');
    }

    $form.find('input[name="name"]').val(date.title).end()
        .find('input[name="location"]').val(date.location).end()
        .find('textarea[name="comments"]').val(date.comments).end()
        .find('input[name="start-date"]').val(date.start.format('YYYY-MM-DD')).end()
        .find('input[name="end-date"]').val(endDate.format('YYYY-MM-DD')).end()
        .find('input[name="id"]').val(date.id).end()
        .find('input[name="reminder-value"]').val(date.reminderValue).end();

    if (date.reminderUnits != null) {
        $form.find('select[name="reminder-unit"]').val(date.reminderUnits).end();
    }


    var createTicketLink = getHelpdeskUrl() + '/' + getAdminDirectory() + '/new_ticket.php?subject=';
    createTicketLink += encodeURI('[' + date.start.format('YYYY-MM-DD') + '] ' + date.title);
    if (date.location != '') {
        createTicketLink += encodeURI(' @ ' + date.location);
    }
    createTicketLink += encodeURI('&message=' + date.comments);
    createTicketLink += encodeURI('&category=' + date.categoryId);
    createTicketLink += encodeURI('&due_date=' + endDate.format('YYYY-MM-DD'));

    $form.find('#create-ticket-button').prop('href', createTicketLink);

    $form.find('select[name="category"] option[value="' + date.categoryId + '"]').prop('selected', true);

    $('#edit-event-modal').modal('show');
}

function updateCategoryVisibility() {
    if ($(this).attr('data-checked') == '1') {
        $(this).attr('data-checked', 0);
    } else {
        $(this).attr('data-checked', 1);
    }

    $('div[data-name="category-toggle"]').each(function() {
        var $this = $(this);

        if ($this.attr('data-checked') == '1') {
            $('.category-' + $this.attr('data-category-value')).show();
        } else {
            $('.category-' + $this.attr('data-category-value')).hide();
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
                event.fontIconMarkup = getIcon({
                    startTime: event.start
                });
                $('#calendar').fullCalendar('updateEvent', event);
                $.jGrowl($('#lang_ticket_due_date_updated').text(), { theme: 'alert-success', closeTemplate: '' });
            },
            error: function() {
                $.jGrowl($('#lang_error_updating_ticket_due_date').text(), { theme: 'alert-danger', closeTemplate: '' });
                revertFunc();
            }
        });
    } else {
        var start = event.start.format('YYYY-MM-DD');
        if (event.end === null) {
            event.end = event.start.clone();
        }
        var end = event.end.clone();
        if (event.allDay) {
            end.add(-1, 'days');
        }

        end = end.format('YYYY-MM-DD');
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
                $.jGrowl($('#lang_event_updated').text(), { theme: 'alert-success', closeTemplate: '' });
            },
            error: function() {
                $.jGrowl($('#lang_error_updating_event').text(), { theme: 'alert-danger', closeTemplate: '' });
                revertFunc();
            }
        });
    }
}