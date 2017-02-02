<?php

namespace Controllers\Tickets;

use BusinessLogic\Tickets\TicketRetriever;


class TicketController {
    function get($id) {
        global $applicationContext, $hesk_settings, $userContext;

        /* @var $ticketRetriever TicketRetriever */
        $ticketRetriever = $applicationContext->get['TicketRetriever'];

        output($ticketRetriever->getTicketById($id, $hesk_settings, $userContext));
    }
}