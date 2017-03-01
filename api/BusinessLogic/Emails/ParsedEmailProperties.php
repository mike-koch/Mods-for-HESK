<?php
/**
 * Created by PhpStorm.
 * User: mkoch
 * Date: 2/28/2017
 * Time: 9:36 PM
 */

namespace BusinessLogic\Emails;


class ParsedEmailProperties {
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