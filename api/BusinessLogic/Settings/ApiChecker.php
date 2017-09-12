<?php

namespace BusinessLogic\Settings;


use DataAccess\Settings\ModsForHeskSettingsGateway;

class ApiChecker extends \BaseClass {
    /* @var $modsForHeskSettingsGateway ModsForHeskSettingsGateway */
    private $modsForHeskSettingsGateway;

    function __construct(ModsForHeskSettingsGateway $modsForHeskSettingsGateway) {
        $this->modsForHeskSettingsGateway = $modsForHeskSettingsGateway;
    }

    function isApiEnabled($heskSettings) {
        $modsForHeskSettings = $this->modsForHeskSettingsGateway->getAllSettings($heskSettings);

        return intval($modsForHeskSettings['public_api']) === 1;
    }
}