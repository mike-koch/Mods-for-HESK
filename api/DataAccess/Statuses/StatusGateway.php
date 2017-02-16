<?php

namespace DataAccess\Statuses;


use BusinessLogic\Statuses\DefaultStatusForAction;
use BusinessLogic\Statuses\Status;

class StatusGateway {

    /**
     * @param $defaultAction DefaultStatusForAction
     * @return Status
     */
    function getStatusForDefaultAction($defaultAction) {
        //-- TODO
        return new Status();
    }
}