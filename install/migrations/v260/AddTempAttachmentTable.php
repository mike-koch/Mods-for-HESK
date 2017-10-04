<?php

namespace v260;


class AddTempAttachmentTable extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "temp_attachment` (
          `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `file_name` VARCHAR(255) NOT NULL,
          `saved_name` VARCHAR(255) NOT NULL,
          `size` INT(10) UNSIGNED NOT NULL,
          `type` ENUM('0','1') NOT NULL,
          `date_uploaded` TIMESTAMP NOT NULL) ENGINE = MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    }

    function down($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "temp_attachment`");
    }
}