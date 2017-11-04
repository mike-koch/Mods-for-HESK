<?php

namespace v310;


class AddStackTraceToLogs extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "logging` ADD COLUMN `stack_trace` TEXT");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "logging` DROP COLUMN `stack_trace`");
    }
}