<?php

namespace v220;

class AddIsAutocloseOptionToStatuses extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ADD COLUMN `IsAutocloseOption` INT NOT NULL DEFAULT 0");
        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` SET `IsAutocloseOption` = 1 WHERE `IsStaffClosedOption` = 1");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` DROP COLUMN `IsAutocloseOption`");
    }
}