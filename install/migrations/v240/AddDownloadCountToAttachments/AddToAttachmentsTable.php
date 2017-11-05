<?php

namespace v240\AddDownloadCountToAttachments;


class AddToAttachmentsTable extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` ADD COLUMN `download_count` INT NOT NULL DEFAULT 0");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` DROP COLUMN `download_count`");
    }
}