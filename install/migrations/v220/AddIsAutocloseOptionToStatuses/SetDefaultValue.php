<?php

namespace v220\AddIsAutocloseOptionToStatuses;

class SetDefaultValue extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` SET `IsAutocloseOption` = 1 WHERE `IsStaffClosedOption` = 1");
    }

    function down($hesk_settings) {
        // no-op
    }
}