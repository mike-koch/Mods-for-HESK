<?php

namespace BusinessLogic\Exceptions;

use Exception;

class InvalidAuthenticationTokenException extends Exception {
    public function __construct() {
        parent::__construct('The X-Auth-Token is invalid. The token must be for an active helpdesk user.');
    }
}