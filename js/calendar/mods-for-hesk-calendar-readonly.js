$(document).ready(function() {
    $('#calendar').fullCalendar({
        header: {
            left: 'prevYear,prev,next,nextYear today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        editable: false,
        eventLimit: true,
        timeFormat: 'H:mm',
        axisFormat: 'H:mm',
        firstDay: $('#setting_first_day_of_week').text(),
        defaultView: $('#setting_default_view').text().trim(),
        events: function(start, end, timezone, callback) {
            $.ajax({
                url: getHelpdeskUrl() + '/internal-api/calendar/?start=' + start + '&end=' + end,
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
                .find('.popover-to span').text(endDate.format(format)).end()
                .find('.popover-comments span').text(event.comments);
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
        }
    });

    $('input[name="category-toggle"]').change(updateCategoryVisibility);
});

function buildEvent(id, dbObject) {
    if (dbObject.type == 'TICKET') {
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
            textColor: calculateTextColor(dbObject.categoryColor),
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