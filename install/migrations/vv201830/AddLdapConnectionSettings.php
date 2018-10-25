<?php

namespace vv201830;


class AddLdapConnectionSettings extends \AbstractUpdatableMigration {

    function innerUp($hesk_settings) {
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
            VALUES ('ldap_search_user', '')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
            VALUES ('ldap_password', '')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
            VALUES ('ldap_search_base', '')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
            VALUES ('ldap_schema', '')");
    }

    function innerDown($hesk_settings) {
        $this->executeQuery("DELETE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` 
            WHERE `Key` IN ('ldap_search_user', 'ldap_password', 'ldap_search_base', 'ldap_schema')");
    }
}