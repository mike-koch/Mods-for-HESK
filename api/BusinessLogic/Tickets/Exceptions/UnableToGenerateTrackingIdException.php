<?php

namespace BusinessLogic\Tickets\Exceptions;


use Exception;

class UnableToGenerateTrackingIdException extends Exception {
    public function __construct() {
        parent::__construct("Error generating a unique ticket ID.");
    }
}