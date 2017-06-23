<?php

namespace BusinessLogic\Exceptions;


class InternalUseOnlyException extends ApiFriendlyException {
    function __construct() {
        parent::__construct("This endpoint can only be used internally", "Internal Use Only", 401);
    }
}