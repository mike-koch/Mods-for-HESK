<?php

namespace v260\AddCalendarModule;


class CreateCalendarEventTable extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event` (
          `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `start` DATETIME,
          `end` DATETIME,
          `all_day` ENUM('0','1') NOT NULL,
          `name` VARCHAR(255) NOT NULL,
          `location` VARCHAR(255),
          `comments` MEDIUMTEXT,
          `category` INT NOT NULL) ENGINE = MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    }

    function down($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event`");
    }
}