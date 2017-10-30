<?php

namespace v220\AddIsAutocloseOptionToStatuses;

class AddNewColumn extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ADD COLUMN `IsAutocloseOption` INT NOT NULL DEFAULT 0");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` DROP COLUMN `IsAutocloseOption`");
    }
}