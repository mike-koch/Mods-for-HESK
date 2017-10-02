<?php

namespace v160;


class AddEditInfoToNotes extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` ADD COLUMN `edit_date` DATETIME NULL");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` ADD COLUMN `number_of_edits` INT NOT NULL DEFAULT 0");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` DROP COLUMN `edit_date`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` DROP COLUMN `number_of_edits`");
    }
}