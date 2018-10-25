<?php

namespace vv201830;


class AddActiveDirectorySettings extends \AbstractUpdatableMigration {

    function innerUp($hesk_settings) {
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
            VALUES ('msad_default_domain', '')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
            VALUES ('msad_dns_servers', '')");
    }

    function innerDown($hesk_settings) {
        $this->executeQuery("DELETE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` 
            WHERE `Key` IN ('msad_default_domain', 'msad_dns_servers')");
    }
}