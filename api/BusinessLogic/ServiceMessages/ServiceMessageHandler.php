<?php

namespace BusinessLogic\ServiceMessages;


// TODO Test
use DataAccess\ServiceMessages\ServiceMessagesGateway;

class ServiceMessageHandler extends \BaseClass {
    /* @var $serviceMessageGateway ServiceMessagesGateway */
    private $serviceMessageGateway;

    function __construct(ServiceMessagesGateway $serviceMessagesGateway) {
        $this->serviceMessageGateway = $serviceMessagesGateway;
    }

    function createServiceMessage($serviceMessage, $heskSettings) {
        // TODO Validate

        return $this->serviceMessageGateway->createServiceMessage($serviceMessage, $heskSettings);
    }
}