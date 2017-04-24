<?php

namespace Controllers\Tickets;


use BusinessLogic\Tickets\TicketDeleter;

class StaffTicketController {
    function delete($id) {
        global $applicationContext, $userContext, $hesk_settings;

        /* @var $ticketDeleter TicketDeleter */
        $ticketDeleter = $applicationContext->get[TicketDeleter::class];

        $ticketDeleter->deleteTicket($id, $userContext, $hesk_settings);
    }
}