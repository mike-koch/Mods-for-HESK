<?php

namespace BusinessLogic\Calendar;


use DataAccess\Calendar\CalendarGateway;

class CalendarHandler extends \BaseClass {
    private $calendarGateway;

    public function __construct(CalendarGateway $calendarGateway) {
        $this->calendarGateway = $calendarGateway;
    }

    public function getEventsForStaff($startTime, $endTime, $searchEventsFilter, $heskSettings) {
        return $this->calendarGateway->getEventsForStaff($startTime, $endTime, $searchEventsFilter, $heskSettings);
    }

    public function updateEvent($calendarEvent, $userContext, $heskSettings) {
        $this->calendarGateway->updateEvent($calendarEvent, $userContext, $heskSettings);
    }
}