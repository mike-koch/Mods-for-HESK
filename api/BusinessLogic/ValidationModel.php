<?php

namespace BusinessLogic;

class ValidationModel {
    /**
     * @var array
     */
    public $errorKeys;

    /**
     * @var bool
     */
    public $valid;

    function __construct() {
        $this->errorKeys = [];
        $this->valid = true;
    }
}