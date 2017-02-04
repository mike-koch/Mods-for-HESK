<?php
namespace BusinessLogic\Tickets;


use DataAccess\Tickets\TicketGateway;

class TicketValidators {
    /**
     * @var $ticketGateway TicketGateway
     */
    private $ticketGateway;

    function __construct($ticketGateway) {
        $this->ticketGateway = $ticketGateway;
    }


    /**
     * @param $customerEmail string The email address
     * @param $heskSettings array HESK Settings
     * @return bool true if the user is maxed out on open tickets, false otherwise
     */
    function isCustomerAtMaxTickets($customerEmail, $heskSettings) {
        if ($heskSettings['max_open'] === 0) {
            return false;
        }

        return count($this->ticketGateway->getTicketsByEmail($customerEmail, $heskSettings)) >= $heskSettings['max_open'];
    }
}