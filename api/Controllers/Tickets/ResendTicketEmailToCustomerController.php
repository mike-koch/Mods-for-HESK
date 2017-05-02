<?php

namespace Controllers\Tickets;


use BusinessLogic\Tickets\TicketRetriever;
use Controllers\InternalApiController;

class ResendTicketEmailToCustomerController extends InternalApiController {
    function get($ticketId) {
        global $applicationContext, $userContext, $hesk_settings;

        $this->checkForInternalUseOnly();

        /* @var $ticketRetriever TicketRetriever */
        $ticketRetriever = $applicationContext->get[TicketRetriever::class];
        $ticket = $ticketRetriever->getTicketById($ticketId, $hesk_settings, $userContext);

        $reply = -1;
        if (isset($_GET['replyId'])) {
            $reply = $_GET['replyId'];
        }

        //-- TODO Get reply if necessary including all attachments :O
    }
}