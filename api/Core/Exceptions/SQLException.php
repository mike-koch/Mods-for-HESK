<?php

namespace Core\Exceptions;

class SQLException extends \BaseException {
    /**
     * @var $failingQuery string
     */
    public $failingQuery;

    function __construct($failingQuery) {
        $this->failingQuery = $failingQuery;

        parent::__construct('A SQL exception occurred. Check the logs for more information.');
    }
}