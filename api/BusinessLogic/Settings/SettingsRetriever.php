<?php

namespace BusinessLogic\Settings;

// TODO Test!
use DataAccess\Settings\ModsForHeskSettingsGateway;

class SettingsRetriever extends \BaseClass {
    /* @var $modsForHeskSettingsGateway ModsForHeskSettingsGateway */
    private $modsForHeskSettingsGateway;

    function __construct(ModsForHeskSettingsGateway $modsForHeskSettingsGateway) {
        $this->modsForHeskSettingsGateway = $modsForHeskSettingsGateway;
    }

    private static $settingsToNotReturn = array(
        'webmaster_email',
        'noreply_email',
        'noreply_name',
        'db_.*',
        'admin_dir',
        'attach_dir',
        'cache_dir',
        'autoclose',
        'autologin',
        'autoassign',
        'secimg_.*',
        'recaptcha_.*',
        'question_.*',
        'attempt_.*',
        'reset_pass',
        'x_frame_opt',
        'force_ssl',
        'imap.*',
        'smtp.*',
        'email_piping',
        'pop3.*',
        'loop.*',
        'email_providers',
        'notify_.*',
        'alink',
        'submit_notice',
        'online',
        'online_min',
        'modsForHeskVersion',
        'use_mailgun',
        'mailgun.*',
        'kb_attach_dir',
        'public_api',
        'custom_fields',
        'hesk_version',
        'hesk_license',
    );

    /**
     * @param $heskSettings array
     * @return array
     */
    function getAllSettings($heskSettings) {
        $modsForHeskSettings = $this->modsForHeskSettingsGateway->getAllSettings($heskSettings);
        $settingsToReturn = array();

        foreach ($heskSettings as $key => $value) {
            if ($this->isPermittedKey($key)) {
                $settingsToReturn[$key] = $value;
            }
        }
        foreach ($modsForHeskSettings as $key => $value) {
            if ($this->isPermittedKey($key)) {
                $settingsToReturn[$key] = $value;
            }
        }

        return $settingsToReturn;
    }

    private function isPermittedKey($key) {
        foreach (self::$settingsToNotReturn as $setting) {
            if (preg_match("/{$setting}/", $key)) {
                return false;
            }
        }

        return true;
    }
}