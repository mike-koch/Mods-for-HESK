<?php

namespace BusinessLogic\Calendar;


use BusinessLogic\Exceptions\ApiFriendlyException;
use BusinessLogic\Security\UserContext;
use DataAccess\Calendar\CalendarGateway;
use PHPUnit\Runner\Exception;

class CalendarHandler extends \BaseClass {
    private $calendarGateway;

    public function __construct(CalendarGateway $calendarGateway) {
        $this->calendarGateway = $calendarGateway;
    }

    public function getEventsForStaff($searchEventsFilter, $heskSettings) {
        return $this->calendarGateway->getEventsForStaff($searchEventsFilter, $heskSettings);
    }

    /**
     * @param $calendarEvent CalendarEvent
     * @param $userContext UserContext
     * @param $heskSettings array
     * @return CalendarEvent
     * @throws \Exception If more than one event is returned for the given ID
     */
    public function updateEvent($calendarEvent, $userContext, $heskSettings) {
        $this->calendarGateway->updateEvent($calendarEvent, $userContext, $heskSettings);

        $eventFilter = new SearchEventsFilter();
        $eventFilter->eventId = $calendarEvent->id;
        $eventFilter->reminderUserId = $userContext->id;

        $events = $this->calendarGateway->getEventsForStaff($eventFilter, $heskSettings);

        if (count($events) !== 1) {
            throw new \Exception("Expected exactly 1 event, found: " . count($events));
        }

        return $events[0];
    }


    public function createEvent($calendarEvent, $userContext, $heskSettings) {
        $this->calendarGateway->createEvent($calendarEvent, $userContext, $heskSettings);

        $eventFilter = new SearchEventsFilter();
        $eventFilter->eventId = $calendarEvent->id;
        $eventFilter->reminderUserId = $userContext->id;

        $events = $this->calendarGateway->getEventsForStaff($eventFilter, $heskSettings);

        if (count($events) !== 1) {
            throw new \Exception("Expected exactly 1 event, found: " . count($events));
        }

        return $events[0];
    }
}