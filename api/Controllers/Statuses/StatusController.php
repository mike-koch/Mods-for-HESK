<?php

namespace Controllers\Statuses;


use BusinessLogic\Statuses\StatusRetriever;

class StatusController extends \BaseClass {
    function get() {
        global $applicationContext, $hesk_settings;

        /* @var $statusRetriever StatusRetriever */
        $statusRetriever = $applicationContext->get(StatusRetriever::clazz());

        output($statusRetriever->getAllStatuses($hesk_settings));
    }
}