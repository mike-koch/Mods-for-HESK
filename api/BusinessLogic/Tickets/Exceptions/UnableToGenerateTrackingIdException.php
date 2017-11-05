<?php

namespace BusinessLogic\Tickets\Exceptions;


class UnableToGenerateTrackingIdException extends \BaseException {
    public function __construct() {
        parent::__construct("Error generating a unique ticket ID.");
    }
}