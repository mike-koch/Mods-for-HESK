<?php

namespace v330\CalendarImprovements;


class AddShowStartTimeSetting extends \AbstractUpdatableMigration {

    function innerUp($hesk_settings) {
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
            VALUES ('calendar_show_start_time', 'true')");
    }

    function innerDown($hesk_settings) {
        $this->executeQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` 
            WHERE `Key` = 'calendar_show_start_time'");
    }
}