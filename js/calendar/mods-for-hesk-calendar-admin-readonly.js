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
                url: heskPath + 'api/index.php/v1/calendar/events/staff?start=' + start + '&end=' + end,
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
                    console.error(data);
                    mfhAlert.error(mfhLang.text('error_loading_events'));
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
                    .find('.popover-priority span').text(event.priority)
                    .find('.popover-status span').text(event.status).end();
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
    var priorities = [];
    priorities['CRITICAL'] = mfhLang.text('critical');
    priorities['HIGH'] = mfhLang.text('high');
    priorities['MEDIUM'] = mfhLang.text('medium');
    priorities['LOW'] = mfhLang.text('low');

    if (dbObject.type === 'TICKET') {
        return {
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
            priority: priorities[dbObject.priority],
            fontIconMarkup: getIcon(dbObject),
            status: dbObject.status
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