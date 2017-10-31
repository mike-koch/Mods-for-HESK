<?php

namespace v240\AddHtmlColumnToTickets;


class UpdateRepliesTable extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` ADD COLUMN `html` ENUM('0','1') NOT NULL DEFAULT '0'");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` DROP COLUMN `html`");
    }
}