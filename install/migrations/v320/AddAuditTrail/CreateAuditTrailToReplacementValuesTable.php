<?php

namespace v320\AddAuditTrail;


class CreateAuditTrailToReplacementValuesTable extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "audit_trail_to_replacement_values` (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
            `audit_trail_id` INT NOT NULL, 
            `replacement_index` INT NOT NULL, 
            `replacement_value` TEXT NOT NULL)");
    }

    function down($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "audit_trail_to_replacement_values`");
    }
}