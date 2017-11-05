<?php

namespace v200\RemoveEditInfoFromNotes;


class DropNumberOfEditsColumn extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` DROP COLUMN `number_of_edits`");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` ADD COLUMN `number_of_edits` INT NOT NULL DEFAULT 0");
    }
}