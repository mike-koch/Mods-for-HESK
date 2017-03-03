<?php

namespace BusinessLogic\Emails;


class EmailSenderHelper {
    /**
     * @var $emailTemplateParser EmailTemplateParser
     */
    private $emailTemplateParser;

    /**
     * @var $basicEmailSender BasicEmailSender
     */
    private $basicEmailSender;

    /**
     * @var $mailgunEmailSender MailgunEmailSender
     */
    private $mailgunEmailSender;

    function __construct($emailTemplateParser, $basicEmailSender, $mailgunEmailSender) {
        $this->emailTemplateParser = $emailTemplateParser;
        $this->basicEmailSender = $basicEmailSender;
        $this->mailgunEmailSender = $mailgunEmailSender;
    }

    function sendEmailForTicket($emailTemplateName, $ticket, $heskSettings, $modsForHeskSettings) {
        //-- parse template

        //-- if no mailgun, use basic sender

        //-- otherwise use mailgun sender
    }
}