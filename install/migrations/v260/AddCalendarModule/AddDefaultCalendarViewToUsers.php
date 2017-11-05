<?php

namespace v260\AddCalendarModule;


class AddDefaultCalendarViewToUsers extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `default_calendar_view` INT NOT NULL DEFAULT '0' AFTER `notify_note_unassigned`");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `default_calendar_view`");
    }
}