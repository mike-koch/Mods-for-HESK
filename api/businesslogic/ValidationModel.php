<?php

namespace BusinessLogic;

class ValidationModel {
    /**
     * @var array
     */
    public $errorKeys;

    public $valid;

    function __construct() {
        $errorKeys = [];
        $valid = true;
    }
}