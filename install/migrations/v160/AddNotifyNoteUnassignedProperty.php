<?php

namespace v160;


class AddNotifyNoteUnassignedProperty extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `notify_note_unassigned` ENUM('0', '1') NOT NULL DEFAULT '0'");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `notify_note_unassigned`");
    }
}