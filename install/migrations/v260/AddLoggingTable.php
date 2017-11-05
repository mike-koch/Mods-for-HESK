<?php

namespace v260;


class AddLoggingTable extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "logging` (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(200),
            `message` MEDIUMTEXT NOT NULL,
            `severity` INT NOT NULL,
            `location` MEDIUMTEXT,
            `timestamp` TIMESTAMP NOT NULL) ENGINE = MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    }

    function down($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "logging`");
    }
}