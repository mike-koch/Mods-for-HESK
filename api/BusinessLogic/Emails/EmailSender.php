<?php

namespace BusinessLogic\Emails;


use BusinessLogic\Tickets\Attachment;
use BusinessLogic\Tickets\Ticket;
use PHPMailer;

interface EmailSender {
    /**
     * Use to send emails that do NOT include ticket information
     *
     * @param $emailBuilder EmailBuilder
     * @param $heskSettings array
     * @param $modsForHeskSettings array
     * @param $sendAsHtml bool
     * @return bool|string true if message sent successfully, error string otherwise
     */
    function sendEmail($emailBuilder, $heskSettings, $modsForHeskSettings, $sendAsHtml);
}