<?php

namespace Controllers\Settings;


use BusinessLogic\Settings\SettingsRetriever;

class SettingsController extends \BaseClass {
    function get() {
        global $applicationContext, $hesk_settings;

        /* @var $settingsRetriever SettingsRetriever */
        $settingsRetriever = $applicationContext->get(SettingsRetriever::clazz());

        output($settingsRetriever->getAllSettings($hesk_settings));
    }
}