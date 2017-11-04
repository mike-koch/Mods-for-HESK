<?php

namespace v260\AddCalendarModule;


class AddDueDateColumnToTickets extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `due_date` DATETIME");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `due_date`");
    }
}