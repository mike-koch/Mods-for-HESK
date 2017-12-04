<?php

namespace BusinessLogic\Exceptions;

class MissingAuthenticationTokenException extends ApiFriendlyException {
    function __construct() {
        parent::__construct("An 'X-Auth-Token' is required for this request",
            'Security Exception',
            401);
    }
}