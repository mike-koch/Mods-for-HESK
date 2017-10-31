<?php

namespace v260\AddCalendarModule;


class AddNotifyOverdueUnassignedColumnToUsers extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `notify_overdue_unassigned` ENUM('0', '1') NOT NULL DEFAULT '0' AFTER `notify_note_unassigned`");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `notify_overdue_unassigned`");
    }
}