<?php

namespace BusinessLogic;

class ValidationModel {
    /**
     * @var array
     */
    public $errorKeys;

    function __construct() {
        $this->errorKeys = [];
    }
}