<?php

namespace vv201830;


class AddGenericLdapSettings extends \AbstractUpdatableMigration {

    function innerUp($hesk_settings) {
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
            VALUES ('ldap_servers', '')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
            VALUES ('ldap_use_tls', '')");
    }

    function innerDown($hesk_settings) {
        $this->executeQuery("DELETE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` 
            WHERE `Key` IN ('ldap_servers', 'ldap_use_tls')");
    }
}