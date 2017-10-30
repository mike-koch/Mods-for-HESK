<?php

namespace v220\AddClosableColumnToStatuses;


class SetDefaultValue extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` SET `Closable` = 'yes'");
    }

    function down($hesk_settings) {
        // no-op
    }
}