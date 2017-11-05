<?php

namespace v160;


class ModifyTicketIdOnAttachments extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` MODIFY COLUMN `ticket_id` VARCHAR(13) NULL");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` MODIFY COLUMN `ticket_id` VARCHAR(13) NOT NULL DEFAULT ''");
    }
}