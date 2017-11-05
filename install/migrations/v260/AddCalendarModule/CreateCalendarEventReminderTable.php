<?php

namespace v260\AddCalendarModule;


class CreateCalendarEventReminderTable extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event_reminder` (
          `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `user_id` INT NOT NULL,
          `event_id` INT NOT NULL,
          `amount` INT NOT NULL,
          `unit` INT NOT NULL,
          `email_sent` ENUM('0', '1') NOT NULL DEFAULT '0') ENGINE = MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    }

    function down($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event_reminder`");
    }
}