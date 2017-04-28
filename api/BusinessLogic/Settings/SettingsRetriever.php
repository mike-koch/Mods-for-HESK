<?php

namespace BusinessLogic\Settings;

// TODO Test!
class SettingsRetriever {

    private static $settingsToNotReturn = array(
        'webmaster_email',
        'noreply_email',
        'noreply_name',
        'db_.*',
    );

    /**
     * @param $heskSettings array
     * @param $modsForHeskSettings array
     */
    function getAllSettings($heskSettings, $modsForHeskSettings) {

    }
}