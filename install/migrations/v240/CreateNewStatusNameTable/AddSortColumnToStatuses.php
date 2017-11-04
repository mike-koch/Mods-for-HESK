<?php

namespace v240\CreateNewStatusNameTable;


class AddSortColumnToStatuses extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ADD COLUMN `sort` INT");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` DROP COLUMN `sort`");
    }
}