<?php

namespace BusinessLogic\Calendar;


class SearchEventsFilter {
    /* @var $startTime int|null */
    public $startTime;

    /* @var $endTime int|null */
    public $endTime;

    /* @var $id int|null */
    public $eventId;

    /* @var $categories int[]|null */
    public $categories;

    /* @var $reminderUserId int|null */
    public $reminderUserId;

    /* @var $includeTickets bool */
    public $includeTickets;

    /* @var $includeUnassignedTickets bool */
    public $includeUnassignedTickets;

    /* @var $includeTicketsAssignedToOthers bool */
    public $includeTicketsAssignedToOthers;

    /* @var $includeTicketsAssignedToMe bool */
    public $includeTicketsAssignedToMe;
}