<?php

namespace Controllers\Calendar;


use BusinessLogic\Calendar\CalendarEvent;
use BusinessLogic\Calendar\CalendarHandler;
use BusinessLogic\Calendar\RecurringRule;
use BusinessLogic\Calendar\ReminderUnit;
use BusinessLogic\Calendar\SearchEventsFilter;
use BusinessLogic\Categories\CategoryHandler;
use BusinessLogic\Exceptions\ValidationException;
use BusinessLogic\Helpers;
use BusinessLogic\Security\UserContext;
use BusinessLogic\Security\UserPrivilege;
use BusinessLogic\ValidationModel;
use Controllers\JsonRetriever;
use DataAccess\Settings\ModsForHeskSettingsGateway;
use RRule\RRule;
use RRule\RSet;

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
        $searchEventsFilter->startTime = $startTime;
        $searchEventsFilter->endTime = $endTime;
        $searchEventsFilter->reminderUserId = $userContext->id;

        if ($userContext->isAnonymousUser()) {
            $searchEventsFilter->includeTicketsAssignedToOthers = false;
            $searchEventsFilter->includeUnassignedTickets = false;
            $searchEventsFilter->includeTickets = false;
            $searchEventsFilter->includeTicketsAssignedToMe = false;

            /* @var $categoryHandler CategoryHandler */
            $categoryHandler = $applicationContext->get(CategoryHandler::clazz());

            $publicCategories = $categoryHandler->getPublicCategories($hesk_settings);
            $ids = array();
            foreach ($publicCategories as $category) {
                $ids[] = $category->id;
            }
            $searchEventsFilter->categories = $ids;
        } else {
            $searchEventsFilter->includeTicketsAssignedToOthers = $userContext->admin ? true : in_array(UserPrivilege::CAN_VIEW_ASSIGNED_TO_OTHER, $userContext->permissions);
            $searchEventsFilter->includeUnassignedTickets = $userContext->admin ? true : in_array(UserPrivilege::CAN_VIEW_UNASSIGNED, $userContext->permissions);
            $searchEventsFilter->includeTickets = true;
            $searchEventsFilter->includeTicketsAssignedToMe = $userContext->admin ? true : in_array(UserPrivilege::CAN_VIEW_ASSIGNED_BY_ME, $userContext->permissions);
            $searchEventsFilter->categories = $userContext->admin ? null : $userContext->categories;
        }

        $events = $calendarHandler->getEventsForStaff($searchEventsFilter, $hesk_settings);

        return output($events);
    }

    function post() {
        /* @var $userContext UserContext */
        /* @var $hesk_settings array */
        global $applicationContext, $hesk_settings, $userContext;

        $json = JsonRetriever::getJsonData();

        $event = $this->transformJson($json);

        /* @var $calendarHandler CalendarHandler */
        $calendarHandler = $applicationContext->get(CalendarHandler::clazz());

        return output($calendarHandler->createEvent($event, $userContext, $hesk_settings), 201);
    }

    function put($id) {
        /* @var $userContext UserContext */
        global $applicationContext, $hesk_settings, $userContext;

        $json = JsonRetriever::getJsonData();

        $event = $this->transformJson($json, $id);

        /* @var $calendarHandler CalendarHandler */
        $calendarHandler = $applicationContext->get(CalendarHandler::clazz());

        return output($calendarHandler->updateEvent($event, $userContext, $hesk_settings));
    }

    function delete($id) {
        /* @var $userContext UserContext */
        global $applicationContext, $hesk_settings, $userContext;

        /* @var $calendarHandler CalendarHandler */
        $calendarHandler = $applicationContext->get(CalendarHandler::clazz());

        $calendarHandler->deleteEvent($id, $userContext, $hesk_settings);

        return http_response_code(204);
    }

    private function transformJson($json, $id = null) {
        $event = new CalendarEvent();

        $event->id = $id;
        $event->startTime = date('Y-m-d H:i:s', strtotime(Helpers::safeArrayGet($json, 'startTime')));
        $event->endTime = date('Y-m-d H:i:s', strtotime(Helpers::safeArrayGet($json, 'endTime')));
        $event->allDay = Helpers::safeArrayGet($json, 'allDay');
        $event->title = Helpers::safeArrayGet($json, 'title');
        $event->location = Helpers::safeArrayGet($json, 'location');
        $event->comments = Helpers::safeArrayGet($json, 'comments');
        $event->categoryId = Helpers::safeArrayGet($json, 'categoryId');
        $event->reminderValue = Helpers::safeArrayGet($json, 'reminderValue');
        $event->reminderUnits = ReminderUnit::getByName(Helpers::safeArrayGet($json, 'reminderUnits'));

        return $event;
    }

    static function getBusinessHours() {
        global $applicationContext, $hesk_settings;

        $calendarHandler = $applicationContext->get(CalendarHandler::clazz());

        return output($calendarHandler->getBusinessHours($hesk_settings));
    }
}