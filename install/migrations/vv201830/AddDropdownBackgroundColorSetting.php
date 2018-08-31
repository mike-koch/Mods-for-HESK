<?php

namespace vv201830;


class AddDropdownBackgroundColorSetting extends \AbstractUpdatableMigration {

    function innerUp($hesk_settings) {
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
            VALUES ('dropdownBackgroundColor', '#ffffff')");
    }

    function innerDown($hesk_settings) {
        $this->executeQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` 
            WHERE `Key` = 'dropdownBackgroundColor'");
    }
}