<?php

namespace BusinessLogic\Statuses;


use DataAccess\Statuses\StatusGateway;

// TODO Test!
class StatusRetriever extends \BaseClass {
    /* @var $statusGateway StatusGateway */
    private $statusGateway;

    function __construct(StatusGateway $statusGateway) {
        $this->statusGateway = $statusGateway;
    }

    function getAllStatuses($heskSettings) {
        return $this->statusGateway->getStatuses($heskSettings);
    }
}