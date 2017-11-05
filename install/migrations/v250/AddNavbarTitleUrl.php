<?php

namespace v250;


class AddNavbarTitleUrl extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('navbar_title_url', '" . hesk_dbEscape($hesk_settings['hesk_url']) . "')");
    }

    function down($hesk_settings) {
        $this->executeQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` WHERE `Key` = 'navbar_title_url'");
    }
}