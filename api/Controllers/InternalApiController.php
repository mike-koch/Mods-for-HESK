<?php

namespace Controllers;


use BusinessLogic\Exceptions\InternalUseOnlyException;
use BusinessLogic\Helpers;

abstract class InternalApiController {
    static function staticCheckForInternalUseOnly() {
        $tokenHeader = Helpers::getHeader('X-AUTH-TOKEN');
        if ($tokenHeader !== null && trim($tokenHeader) !== '') {
            throw new InternalUseOnlyException();
        }
    }

    function checkForInternalUseOnly() {
        $tokenHeader = Helpers::getHeader('X-AUTH-TOKEN');
        if ($tokenHeader !== null && trim($tokenHeader) !== '') {
            throw new InternalUseOnlyException();
        }
    }
}