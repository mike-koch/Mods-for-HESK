<?php

namespace v260\AddCalendarModule;


class AddOverdueEmailSentColumnToTickets extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `overdue_email_sent` ENUM('0','1')");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `overdue_email_sent`");
    }
}