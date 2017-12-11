<?php

namespace Controllers\Calendar;


use BusinessLogic\Calendar\CalendarHandler;
use BusinessLogic\Calendar\SearchEventsFilter;

class CalendarController extends \BaseClass {
    function get() {
        global $applicationContext, $hesk_settings;

        $startTime = isset($_GET['start']) ? $_GET['start'] : round(microtime(true) * 1000);
        $endTime = isset($_GET['end']) ? $_GET['end'] : round(microtime(true) * 1000);

        /* @var $calendarHandler CalendarHandler */
        $calendarHandler = $applicationContext->get(CalendarHandler::clazz());

        $events = $calendarHandler->getEventsForStaff($startTime, $endTime, new SearchEventsFilter(), $hesk_settings);

        return output($events);
    }
}