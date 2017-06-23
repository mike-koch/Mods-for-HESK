<?php

namespace Controllers\Settings;


use BusinessLogic\Settings\SettingsRetriever;

class SettingsController {
    function get() {
        global $applicationContext, $hesk_settings, $modsForHesk_settings;

        /* @var $settingsRetriever SettingsRetriever */
        $settingsRetriever = $applicationContext->get[SettingsRetriever::class];

        output($settingsRetriever->getAllSettings($hesk_settings, $modsForHesk_settings));
    }
}