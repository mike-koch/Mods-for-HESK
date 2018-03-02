<?php

namespace BusinessLogic\Calendar;


use BusinessLogic\DateTimeHelpers;
use BusinessLogic\Security\UserContext;
use BusinessLogic\Tickets\AuditTrailEntityType;
use DataAccess\AuditTrail\AuditTrailGateway;
use DataAccess\Calendar\CalendarGateway;

class CalendarHandler extends \BaseClass {
    private $calendarGateway;
    private $auditTrailGateway;

    public function __construct(CalendarGateway $calendarGateway,
                                AuditTrailGateway $auditTrailGateway) {
        $this->calendarGateway = $calendarGateway;
        $this->auditTrailGateway = $auditTrailGateway;
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

        $this->auditTrailGateway->insertAuditTrailRecord($calendarEvent->id,
            AuditTrailEntityType::CALENDAR_EVENT,
            'audit_event_updated',
            DateTimeHelpers::heskDate($heskSettings),
            array(0 => $userContext->name . ' (' . $userContext->username . ')'), $heskSettings);

        $eventFilter = new SearchEventsFilter();
        $eventFilter->eventId = $calendarEvent->id;
        $eventFilter->reminderUserId = $userContext->id;

        $events = $this->calendarGateway->getEventsForStaff($eventFilter, $heskSettings);

        if (count($events) !== 1) {
            throw new \Exception("Expected exactly 1 event, found: " . count($events));
        }

        $event = $events[0];

        return $event;
    }


    /**
     * @param $calendarEvent CalendarEvent
     * @param $userContext UserContext
     * @param $heskSettings array
     * @return AbstractEvent
     * @throws \Exception
     */
    public function createEvent($calendarEvent, $userContext, $heskSettings) {
        $this->calendarGateway->createEvent($calendarEvent, $userContext, $heskSettings);

        $eventFilter = new SearchEventsFilter();
        $eventFilter->eventId = $calendarEvent->id;
        $eventFilter->reminderUserId = $userContext->id;

        $events = $this->calendarGateway->getEventsForStaff($eventFilter, $heskSettings);

        if (count($events) !== 1) {
            throw new \Exception("Expected exactly 1 event, found: " . count($events));
        }

        $event = $events[0];

        $this->auditTrailGateway->insertAuditTrailRecord($event->id,
            AuditTrailEntityType::CALENDAR_EVENT,
            'audit_event_created',
            DateTimeHelpers::heskDate($heskSettings),
            array(0 => $userContext->name . ' (' . $userContext->username . ')'), $heskSettings);

        return $event;
    }

    public function deleteEvent($id, $userContext, $heskSettings) {
        $this->calendarGateway->deleteEvent($id, $userContext, $heskSettings);
    }

    public function getBusinessHours($heskSettings) {
        return $this->calendarGateway->getBusinessHours($heskSettings);
    }
}