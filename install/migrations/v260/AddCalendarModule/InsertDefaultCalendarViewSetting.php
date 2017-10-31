<?php

namespace v260\AddCalendarModule;


class InsertDefaultCalendarViewSetting extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('default_calendar_view', 'month')");
    }

    function down($hesk_settings) {
        $this->executeQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` WHERE `Key` = 'default_calendar_view'");
    }
}