<?php
/**
 * Created by PhpStorm.
 * User: mkoch
 * Date: 1/28/2017
 * Time: 9:55 PM
 */

namespace BusinessLogic\Exceptions;

use Exception;

class MissingAuthenticationTokenException extends Exception {
    function __construct() {
        parent::__construct("An 'X-Auth-Token' is required for all requests");
    }
}