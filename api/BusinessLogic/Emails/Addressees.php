<?php

namespace BusinessLogic\Emails;


class Addressees extends \BaseClass {
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