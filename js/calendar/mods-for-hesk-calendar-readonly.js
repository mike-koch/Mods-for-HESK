$(document).ready(function() {
    var heskPath = $('p#hesk-path').text();

    $('#calendar').fullCalendar({
        header: {
            left: 'prevYear,prev,next,nextYear today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay,listWeek'
        },
        locale: mfhLang.text('CALENDAR_LANGUAGE'),
        editable: false,
        eventLimit: true,
        timeFormat: 'H:mm',
        axisFormat: 'H:mm',
        displayEventTime: $('#setting_show_start_time').text(),
        businessHours: [
            {
                dow: [0],
                start: $('#business_hours_0_start').text(),
                end: $('#business_hours_0_end').text()
            },
            {
                dow: [1],
                start: $('#business_hours_1_start').text(),
                end: $('#business_hours_1_end').text()
            },
            {
                dow: [2],
                start: $('#business_hours_2_start').text(),
                end: $('#business_hours_2_end').text()
            },
            {
                dow: [3],
                start: $('#business_hours_3_start').text(),
                end: $('#business_hours_3_end').text()
            },
            {
                dow: [4],
                start: $('#business_hours_4_start').text(),
                end: $('#business_hours_4_end').text()
            },
            {
                dow: [5],
                start: $('#business_hours_5_start').text(),
                end: $('#business_hours_5_end').text()
            },
            {
                dow: [6],
                start: $('#business_hours_6_start').text(),
                end: $('#business_hours_6_end').text()
            }
        ],
        firstDay: $('#setting_first_day_of_week').text(),
        defaultView: $('#setting_default_view').text().trim(),
        events: function(start, end, timezone, callback) {
            $.ajax({
                url: heskPath + 'api/index.php/v1/calendar/events/?start=' + start + '&end=' + end,
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    var events = [];
                    $(data).each(function() {
                        events.push(buildEvent(this.id, this));
                    });
                    callback(events);
                    updateCategoryVisibility();
                },
                error: function(data) {
                    console.error(data);
                    mfhAlert.error(mfhLang.text('error_loading_events'));
                }
            });
        },
        eventMouseover: function(event) {
            if (event.type === 'TICKET') {
                return;
            }

            var contents = $('.popover-template').html();
            var $contents = $(contents);

            var format = 'dddd, MMMM Do YYYY';
            var endDate = event.end == null ? event.start : event.end;

            if (event.allDay) {
                endDate = event.end.clone();
                endDate.add(-1, 'days');
            }

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
            }).data('bs.popover')
                .tip()
                .css('padding', '0')
                .find('.popover-title')
                .css('background-color', event.backgroundColor)
                .addClass('background-volatile');

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
        reminderUnits: dbObject.reminderUnits
    };
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