<?php

namespace BusinessLogic\Emails;


class EmailBuilder {
    private $to;
    private $cc;
    private $bcc;
    private $subject;
    private $message;
    private $htmlMessage;
    private $attachments;
}