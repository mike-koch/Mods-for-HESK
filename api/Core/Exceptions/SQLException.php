<?php

namespace Core\Exceptions;

use Exception;

class SQLException extends Exception {
    /**
     * @var $failingQuery string
     */
    public $failingQuery;

    function __construct($failingQuery) {
        $this->failingQuery = $failingQuery;

        parent::__construct('A SQL Exceptions occurred. Check the logs for more information.');
    }
}