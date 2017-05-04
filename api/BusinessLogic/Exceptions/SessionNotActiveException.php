<?php
/**
 * Created by PhpStorm.
 * User: cokoch
 * Date: 5/2/2017
 * Time: 12:28 PM
 */

namespace BusinessLogic\Exceptions;


class SessionNotActiveException extends ApiFriendlyException {
    function __construct() {
        parent::__construct("You must be logged in to call internal API methods", "Authentication Required", 401);
    }
}