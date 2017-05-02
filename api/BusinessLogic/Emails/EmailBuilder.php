<?php

namespace BusinessLogic\Emails;


use BusinessLogic\Tickets\Attachment;

class EmailBuilder {
    /**
     * @var $to string[]
     */
    public $to;

    /**
     * @var $cc string[]
     */
    public $cc;

    /**
     * @var $bcc string[]
     */
    public $bcc;

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

    /**
     * @var $attachments Attachment[]
     */
    public $attachments;
}