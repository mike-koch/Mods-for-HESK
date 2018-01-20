<?php

namespace BusinessLogic\Calendar;


class CalendarEvent extends AbstractEvent {
    public $type = 'CALENDAR';

    public $endTime;

    /* @var $allDay bool */
    public $allDay;

    public $location;

    public $comments;

    public $reminderValue;

    public $reminderUnits;

    public $recurringRule;
}