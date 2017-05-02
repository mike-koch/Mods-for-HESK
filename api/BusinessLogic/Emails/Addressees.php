<?php

namespace BusinessLogic\Emails;


class Addressees {
    /**
     * @var $to string[]
     */
    public $to;

    /**
     * @var $cc string[]|null
     */
    public $cc;

    /**
     * @var $bcc string[]|null
     */
    public $bcc;
}