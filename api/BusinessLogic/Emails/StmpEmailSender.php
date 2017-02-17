<?php

namespace BusinessLogic\Emails;


use BusinessLogic\Tickets\Attachment;
use BusinessLogic\Tickets\Ticket;

class StmpEmailSender implements EmailSender {
    /**
     * @param $emailBuilder EmailBuilder
     * @param $heskSettings array
     * @param $modsForHeskSettings array
     */
    function sendEmail($emailBuilder, $heskSettings, $modsForHeskSettings) {
        // TODO: Implement sendEmail() method.
    }

    /**
     * @param $emailBuilder EmailBuilder
     * @param $ticket Ticket
     * @param $attachments Attachment[]
     * @param $heskSettings array
     * @param $modsForHeskSettings array
     */
    function sendEmailWithTicket($emailBuilder, $ticket, $attachments, $heskSettings, $modsForHeskSettings) {
        // TODO: Implement sendEmailWithTicket() method.
    }
}