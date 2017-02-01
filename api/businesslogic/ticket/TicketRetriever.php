<?php
/**
 * Created by PhpStorm.
 * User: mkoch
 * Date: 1/31/2017
 * Time: 10:13 PM
 */

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