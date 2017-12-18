<?php

namespace Controllers\Calendar;


use BusinessLogic\Calendar\CalendarHandler;
use BusinessLogic\Calendar\SearchEventsFilter;
use BusinessLogic\Exceptions\ValidationException;
use BusinessLogic\Security\UserContext;
use BusinessLogic\Security\UserPrivilege;
use BusinessLogic\ValidationModel;

class CalendarController extends \BaseClass {
    function get() {
        /* @var $userContext UserContext */
        global $applicationContext, $hesk_settings, $userContext;

        if (!isset($_GET['start']) || !isset($_GET['end'])) {
            $validationModel = new ValidationModel();
            $validationModel->errorKeys = array('START_AND_END_TIMES_REQUIRED');
            throw new ValidationException($validationModel);
        }

        $startTime = $_GET['start'];
        $endTime = $_GET['end'];

        /* @var $calendarHandler CalendarHandler */
        $calendarHandler = $applicationContext->get(CalendarHandler::clazz());

        $searchEventsFilter = new SearchEventsFilter();
        $searchEventsFilter->reminderUserId = $userContext->id;
        $searchEventsFilter->includeTicketsAssignedToOthers = in_array(UserPrivilege::CAN_VIEW_ASSIGNED_TO_OTHER, $userContext->permissions);
        $searchEventsFilter->includeUnassignedTickets = in_array(UserPrivilege::CAN_VIEW_UNASSIGNED, $userContext->permissions);
        $searchEventsFilter->includeTickets = true;
        $searchEventsFilter->categories = $userContext->admin ? null : $userContext->categories;

        $events = $calendarHandler->getEventsForStaff($startTime, $endTime, $searchEventsFilter, $hesk_settings);

        return output($events);
    }
}