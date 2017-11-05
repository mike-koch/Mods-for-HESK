<?php

namespace BusinessLogic\Emails;


class ParsedEmailProperties extends \BaseClass {
    function __construct($subject, $message, $htmlMessage) {
        $this->subject = $subject;
        $this->message = $message;
        $this->htmlMessage = $htmlMessage;
    }

    /**
     * @var $subject string
     */
    public $subject;

    /**
     * @var $message string
     */
    public $message;

    /**
     * @var $htmlMessage string
     */
    public $htmlMessage;
}