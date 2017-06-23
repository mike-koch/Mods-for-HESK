<?php

namespace DataAccess\Settings;


use DataAccess\CommonDao;

class ModsForHeskSettingsGateway extends CommonDao {
    function getAllSettings($heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT `Key`, `Value` FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "settings` WHERE `Key` <> 'modsForHeskVersion'");

        $settings = array();
        while ($row = hesk_dbFetchAssoc($rs)) {
            $settings[$row['Key']] = $row['Value'];
        }

        $this->close();

        return $settings;
    }
}