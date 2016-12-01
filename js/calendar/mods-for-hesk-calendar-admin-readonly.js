$(document).ready(function() {
    $('#calendar').fullCalendar({
        header: {
            left: 'prevYear,prev,next,nextYear today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay,listWeek'
        },
        editable: false,
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
                    .find('.popover-to span').text(endDate.format(format))
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
        eventRender: function(event, element) {
            if (event.type === 'TICKET' && moment(event.start).endOf("day").isBefore(moment())) {
                $('[data-date="' + event.start.format('YYYY-MM-DD') + '"]').css('background', '#f2dede');
            }

            if (event.fontIconMarkup !== undefined) {
                element.find('span.fc-title').html(event.fontIconMarkup + '&nbsp;' + element.find('span.fc-title').text());
            }
        }
    });

    $('div[data-name="category-toggle"]').click(updateCategoryVisibility);
});

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