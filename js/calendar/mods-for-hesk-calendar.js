$(document).ready(function() {
    var heskPath = $('p#hesk-path').text();

    $('#calendar').fullCalendar({
        header: {
            left: 'prevYear,prev,next,nextYear today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay,listWeek'
        },
        locale: mfhLang.text('CALENDAR_LANGUAGE'),
        editable: true,
        eventLimit: true,
        timeFormat: 'H:mm',
        axisFormat: 'H:mm',
        firstDay: $('#setting_first_day_of_week').text(),
        defaultView: $('#setting_default_view').text().trim(),
        events: function(start, end, timezone, callback) {
            $.ajax({
                url: heskPath + 'api/v1/calendar/events/staff?start=' + start + '&end=' + end,
                method: 'GET',
                dataType: 'json',
                headers: { 'X-Internal-Call': true },
                success: function(data) {
                    var events = [];
                    $(data).each(function() {
                        events.push(buildEvent(this.id, this));
                    });
                    callback(events);
                    updateCategoryVisibility();
                },
                error: function(data) {
                    mfhAlert.error(mfhLang.text('error_loading_events'));
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
            var endDate = event.end === null ? event.start : event.end;

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
                if (event.allDay) {
                    endDate = event.end.clone();
                    endDate.add(-1, 'days');
                } else {
                    format += ', HH:mm';
                }

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
            if (event.fontIconMarkup !== undefined) {
                eventTitle = event.fontIconMarkup + '&nbsp;' + eventTitle;
            }

            $eventMarkup.popover({
                title: eventTitle,
                html: true,
                content: $contents,
                animation: true,
                container: 'body',
                placement: 'auto'
            }).data('bs.popover')
                .tip()
                .css('padding', '0')
                .find('.popover-title')
                .css('background-color', event.backgroundColor);

            if (event.textColor === 'AUTO') {
                $eventMarkup.addClass('background-volatile');
            } else {
                $eventMarkup.data('bs.popover').tip().find('.popover-title')
                        .css('color', event.textColor)
                        .css('border', 'solid 1px ' + event.borderColor);
            }


            $eventMarkup.popover('show');
            refreshBackgroundVolatileItems();
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
            url: heskPath + 'internal-api/admin/calendar/',
            data: data,
            success: function() {
                removeFromCalendar(data.id);
                mfhAlert.success(mfhLang.text('event_deleted'));
                $('#edit-event-modal').modal('hide');
            },
            error: function() {
                mfhAlert.error(mfhLang.text('error_deleting_event'));
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

        var reminderValue = $createForm.find('input[name="reminder-value"]').val();
        var reminderUnits = $createForm.find('select[name="reminder-unit"]').val();

        var data = {
            title: $createForm.find('input[name="name"]').val(),
            location: $createForm.find('input[name="location"]').val(),
            startTime: moment(start).format(dateFormat),
            endTime: moment(end).format(dateFormat),
            allDay: allDay,
            comments: $createForm.find('textarea[name="comments"]').val(),
            categoryId: $createForm.find('select[name="category"]').val(),
            type: 'CALENDAR',
            backgroundColor: $createForm.find('select[name="category"] :selected').attr('data-background-color'),
            foregroundColor: $createForm.find('select[name="category"] :selected').attr('data-foreground-color'),
            displayBorder: $createForm.find('select[name="category"] :selected').attr('data-display-border'),
            categoryName: $createForm.find('select[name="category"] :selected').text().trim(),
            reminderValue: reminderValue === "" ? null : reminderValue,
            reminderUnits: reminderValue === "" ? null : reminderUnits
        };

        $.ajax({
            method: 'POST',
            url: heskPath + 'api/v1/calendar/events/staff',
            data: JSON.stringify(data),
            contentType: 'json',
            headers: { 'X-Internal-Call': true },
            success: function(id) {
                addToCalendar(id, data, $('#lang_event_created').text());
                $('#create-event-modal').modal('hide');
                updateCategoryVisibility();
            },
            error: function() {
                mfhAlert.error(mfhLang.text('error_creating_event'));
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

        var reminderValue = $createForm.find('input[name="reminder-value"]').val();
        var reminderUnits = $createForm.find('select[name="reminder-unit"]').val();

        var data = {
            id: $form.find('input[name="id"]').val(),
            title: $form.find('input[name="name"]').val(),
            location: $form.find('input[name="location"]').val(),
            startTime: moment(start).format(dateFormat),
            endTime: moment(end).format(dateFormat),
            allDay: allDay,
            comments: $form.find('textarea[name="comments"]').val(),
            categoryId: parseInt($form.find('select[name="category"]').val()),
            backgroundColor: $form.find('select[name="category"] :selected').attr('data-background-color'),
            foregroundColor: $form.find('select[name="category"] :selected').attr('data-foreground-color'),
            displayBorder: $form.find('select[name="category"] :selected').attr('data-display-border'),
            categoryName: $form.find('select[name="category"] :selected').text().trim(),
            reminderValue: reminderValue === "" ? null : reminderValue,
            reminderUnits: reminderValue === "" ? null : reminderUnits
        };

        $.ajax({
            method: 'POST',
            url: heskPath + 'api/v1/calendar/events/staff/' + data.id,
            data: JSON.stringify(data),
            contentType: 'json',
            headers: {
                'X-Internal-Call': true,
                'X-HTTP-Method-Override': 'PUT'
            },
            success: function() {
                removeFromCalendar(data.id);
                addToCalendar(data.id, data, $('#lang_event_updated').text());
                $('#edit-event-modal').modal('hide');
            },
            error: function() {
                mfhAlert.error(mfhLang.text('error_updating_event'));
            }
        });
    });

    $('div[data-name="category-toggle"]').click(updateCategoryVisibility);
});

function addToCalendar(id, event, successMessage) {
    var eventObject = buildEvent(id, event);
    $('#calendar').fullCalendar('renderEvent', eventObject);
    mfhAlert.success(successMessage);
}

function removeFromCalendar(id) {
    $('#calendar').fullCalendar('removeEvents', id);
}

function buildEvent(id, dbObject) {
    if (dbObject.type === 'TICKET') {
        return {
            id: id,
            title: dbObject.title,
            subject: dbObject.subject,
            trackingId: dbObject.trackingId,
            start: moment(dbObject.startTime),
            url: dbObject.url,
            backgroundColor: dbObject.backgroundColor,
            textColor: dbObject.foregroundColor === 'AUTO' ? calculateTextColor(dbObject.backgroundColor) : dbObject.foregroundColor,
            borderColor: parseInt(dbObject.displayBorder) === 1 ? dbObject.foregroundColor : dbObject.backgroundColor,
            allDay: true,
            type: dbObject.type,
            categoryId: dbObject.categoryId,
            categoryName: dbObject.categoryName,
            className: 'category-' + dbObject.categoryId,
            owner: dbObject.owner,
            priority: dbObject.priority,
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
        backgroundColor: dbObject.backgroundColor,
        textColor: dbObject.foregroundColor === 'AUTO' ? calculateTextColor(dbObject.backgroundColor) : dbObject.foregroundColor,
        borderColor: parseInt(dbObject.displayBorder) === 1 ? dbObject.foregroundColor : dbObject.backgroundColor,
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
    if (color === null || color === '' || color === undefined) {
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
        .find('select[name="reminder-unit"]').val("MINUTE").end()
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


    var heskPath = $('p#hesk-path').text();
    var adminDir = $('p#admin-dir').text();
    var createTicketLink = heskPath + adminDir + '/new_ticket.php?subject=';
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
    var heskPath = $('p#hesk-path').text();

    if (event.type === 'TICKET') {
        var uri = 'api/v1/staff/tickets/' + event.id + '/due-date';
        $.ajax({
            method: 'POST',
            url: heskPath + uri,
            headers: {
                'X-Internal-Call': true,
                'X-HTTP-Method-Override': 'PATCH'
            },
            data: JSON.stringify({
                dueDate: event.start.format('YYYY-MM-DD')
            }),
            success: function() {
                event.fontIconMarkup = getIcon({
                    startTime: event.start
                });
                $('#calendar').fullCalendar('updateEvent', event);
                mfhAlert.success(mfhLang.text('ticket_due_date_updated'));
            },
            error: function() {
                mfhAlert.error(mfhLang.text('error_updating_ticket_due_date'));
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

        var url = heskPath + 'api/v1/calendar/events/staff/' + event.id;
        $.ajax({
            method: 'POST',
            url: url,
            data: JSON.stringify(data),
            headers: {
                'X-Internal-Call': true,
                'X-HTTP-Method-Override': 'PUT'
            },
            success: function() {
                mfhAlert.success(mfhLang.text('event_updated'));
            },
            error: function() {
                mfhAlert.error(mfhLang.text('error_updating_event'));
                revertFunc();
            }
        });
    }
}