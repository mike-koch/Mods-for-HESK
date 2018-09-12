<?php

namespace vv201830;


class AddUseLdapSetting extends \AbstractUpdatableMigration {
    function innerUp($hesk_settings) {
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
            VALUES ('use_ldap', '0')");
    }

    function innerDown($hesk_settings) {
        $this->executeQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` 
            WHERE `Key` = 'use_ldap'");
    }
}