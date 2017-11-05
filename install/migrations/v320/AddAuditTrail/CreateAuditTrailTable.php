<?php

namespace v320\AddAuditTrail;


class CreateAuditTrailTable extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "audit_trail` (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
            `entity_id` INT NOT NULL,
            `entity_type` VARCHAR(50) NOT NULL,
            `language_key` VARCHAR(100) NOT NULL, 
            `date` TIMESTAMP NOT NULL)");
    }

    function down($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "audit_trail`");
    }
}