<?php

namespace BusinessLogic\Exceptions;


class AccessViolationException extends ApiFriendlyException {
    function __construct($message) {
        parent::__construct($message, 'Access Exception', 403);
    }
}