<?php

namespace BusinessLogic\Calendar;


class SearchEventsFilter {
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
}