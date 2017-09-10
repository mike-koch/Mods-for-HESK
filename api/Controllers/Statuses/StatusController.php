<?php

namespace Controllers\Statuses;


use BusinessLogic\Statuses\StatusRetriever;

class StatusController {
    function get() {
        global $applicationContext, $hesk_settings;

        /* @var $statusRetriever StatusRetriever */
        $statusRetriever = $applicationContext->get(StatusRetriever::class);

        output($statusRetriever->getAllStatuses($hesk_settings));
    }
}