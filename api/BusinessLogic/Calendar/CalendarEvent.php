<?php

namespace BusinessLogic\Calendar;


use BusinessLogic\Tickets\AuditTrail;

class CalendarEvent extends AbstractEvent {
    public $type = 'CALENDAR';

    public $endTime;

    /* @var $allDay bool */
    public $allDay;

    public $location;

    public $comments;

    public $reminderValue;

    public $reminderUnits;

    /* @var $auditTrail AuditTrail[] */
    public $auditTrail = array();
}