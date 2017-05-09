<?php

namespace Controllers\Navigation;


use BusinessLogic\Navigation\CustomNavElementHandler;

class CustomNavElementController {
    static function getAll() {
        global $applicationContext, $hesk_settings;

        /* @var $handler CustomNavElementHandler */
        $handler = $applicationContext->get[CustomNavElementHandler::class];

        output($handler->getAllCustomNavElements($hesk_settings));
    }
}