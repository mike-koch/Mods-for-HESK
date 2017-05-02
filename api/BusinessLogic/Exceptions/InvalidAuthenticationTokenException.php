<?php

namespace BusinessLogic\Exceptions;


class InvalidAuthenticationTokenException extends ApiFriendlyException {
    public function __construct() {
        parent::__construct('The X-Auth-Token is invalid. The token must be for an active helpdesk user.',
            'Security Exception',
            401);
    }
}