<?php

abstract class AbstractUpdatableMigration extends AbstractMigration {
    private $migrationNumber;

    function __construct($migrationNumber) {
        $this->migrationNumber = $migrationNumber;
    }

    function up($hesk_settings) {
        $this->innerUp($hesk_settings);

        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = " . intval($this->migrationNumber) . " 
            WHERE `Key` = 'migrationNumber'");
    }

    abstract function innerUp($hesk_settings);

    function down($hesk_settings) {
        $this->innerDown($hesk_settings);

        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = " . (intval($this->migrationNumber) - 1) . " 
            WHERE `Key` = 'migrationNumber'");
    }

    abstract function innerDown($hesk_settings);
}