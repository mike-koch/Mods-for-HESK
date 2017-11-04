<?php

namespace v240\CreateNewStatusNameTable;


class CreateTextToStatusXrefTable extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "text_to_status_xref` (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `language` VARCHAR(200) NOT NULL,
            `text` VARCHAR(200) NOT NULL,
            `status_id` INT NOT NULL) ENGINE = MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    }

    function down($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "text_to_status_xref`");
    }
}