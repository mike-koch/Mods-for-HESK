<?php

namespace v260\AddApiTables;


class CreateTable extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "user_api_tokens` (
          `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `user_id` INT NOT NULL,
          `token` VARCHAR(500) NOT NULL) ENGINE = MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    }

    function down($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "user_api_tokens`");
    }
}