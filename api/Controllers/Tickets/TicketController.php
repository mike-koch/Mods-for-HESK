<?php

namespace Controllers\Tickets;

use BusinessLogic\Tickets\TicketCreator;
use BusinessLogic\Tickets\TicketRetriever;


class TicketController {
    function get($id) {
        global $applicationContext, $hesk_settings, $userContext;

        /* @var $ticketRetriever TicketRetriever */
        $ticketRetriever = $applicationContext->get['TicketRetriever'];

        output($ticketRetriever->getTicketById($id, $hesk_settings, $userContext));
    }

    function post() {
        global $applicationContext, $hesk_settings, $modsForHeskSettings, $userContext;

        /* @var $ticketCreator TicketCreator */
        $ticketCreator = $applicationContext->get[TicketCreator::class];

        //-- TODO Parse POST data

        $ticketCreator->createTicketByCustomer(null, $hesk_settings, $modsForHeskSettings, $userContext);
    }
}