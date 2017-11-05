<?php

namespace v200;


class RemoveNoteIdFromAttachments extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` DROP COLUMN `note_id`");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` ADD COLUMN `note_id` INT NULL AFTER `ticket_id`");
    }
}