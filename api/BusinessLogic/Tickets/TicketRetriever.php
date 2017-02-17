<?php

namespace BusinessLogic\Tickets;


use DataAccess\Tickets\TicketGateway;

class TicketRetriever {
    /**
     * @var $ticketGateway TicketGateway
     */
    private $ticketGateway;

    function __construct($ticketGateway) {
        $this->ticketGateway = $ticketGateway;
    }

    function getTicketById($id, $heskSettings, $userContext) {
        return $this->ticketGateway->getTicketById($id, $heskSettings);
    }
}