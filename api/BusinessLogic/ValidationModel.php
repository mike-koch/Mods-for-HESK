<?php

namespace BusinessLogic;

class ValidationModel extends \BaseClass {
    /**
     * @var array
     */
    public $errorKeys;

    function __construct() {
        $this->errorKeys = [];
    }
}