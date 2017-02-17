<?php

namespace BusinessLogic\Emails;


use BusinessLogic\Tickets\Attachment;
use BusinessLogic\Tickets\Ticket;

interface EmailSender {
    /**
     * @param $emailBuilder EmailBuilder
     * @param $heskSettings array
     * @param $modsForHeskSettings array
     */
    function sendEmail($emailBuilder, $heskSettings, $modsForHeskSettings);

    /**
     * @param $emailBuilder EmailBuilder
     * @param $ticket Ticket
     * @param $attachments Attachment[]
     * @param $heskSettings array
     * @param $modsForHeskSettings array
     */
    function sendEmailWithTicket($emailBuilder, $ticket, $attachments, $heskSettings, $modsForHeskSettings);
}