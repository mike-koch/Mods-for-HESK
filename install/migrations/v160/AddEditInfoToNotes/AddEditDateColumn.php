<?php

namespace v160\AddEditInfoToNotes;

class AddEditDateColumn extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` ADD COLUMN `edit_date` DATETIME NULL");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` DROP COLUMN `edit_date`");
    }
}