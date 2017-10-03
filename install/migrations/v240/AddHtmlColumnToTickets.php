<?php

namespace v240;


class AddHtmlColumnToTickets extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `html` ENUM('0','1') NOT NULL DEFAULT '0'");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` ADD COLUMN `html` ENUM('0','1') NOT NULL DEFAULT '0'");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` ADD COLUMN `html` ENUM('0','1') NOT NULL DEFAULT '0'");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `html`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` DROP COLUMN `html`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` DROP COLUMN `html`");
    }
}