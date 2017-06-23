<?php

namespace BusinessLogic\Statuses;


use DataAccess\Statuses\StatusGateway;

// TODO Test!
class StatusRetriever {
    /* @var $statusGateway StatusGateway */
    private $statusGateway;

    function __construct($statusGateway) {
        $this->statusGateway = $statusGateway;
    }

    function getAllStatuses($heskSettings) {
        return $this->statusGateway->getStatuses($heskSettings);
    }
}