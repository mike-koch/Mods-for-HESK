<?php

namespace BusinessLogic\Emails;


use BusinessLogic\Tickets\Attachment;
use BusinessLogic\Tickets\Ticket;
use PHPMailer;

interface EmailSender {
    /**
     * Use to send emails
     *
     * @param $emailBuilder EmailBuilder
     * @param $heskSettings array
     * @param $modsForHeskSettings array
     * @param $sendAsHtml bool
     * @return bool|string|\stdClass true if message sent successfully, string for PHPMail/Smtp error, stdClass for Mailgun error
     */
    function sendEmail($emailBuilder, $heskSettings, $modsForHeskSettings, $sendAsHtml);
}