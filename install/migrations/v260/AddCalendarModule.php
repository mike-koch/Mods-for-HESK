<?php

namespace v260;


class AddCalendarModule extends \AbstractMigration {

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
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event_reminder` (
          `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `user_id` INT NOT NULL,
          `event_id` INT NOT NULL,
          `amount` INT NOT NULL,
          `unit` INT NOT NULL,
          `email_sent` ENUM('0', '1') NOT NULL DEFAULT '0') ENGINE = MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `due_date` DATETIME");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `overdue_email_sent` ENUM('0','1')");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ADD COLUMN `color` VARCHAR(7)");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ADD COLUMN `usage` INT NOT NULL DEFAULT 0");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `notify_overdue_unassigned` ENUM('0', '1') NOT NULL DEFAULT '0' AFTER `notify_note_unassigned`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `default_calendar_view` INT NOT NULL DEFAULT '0' AFTER `notify_note_unassigned`");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('enable_calendar', '1')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('first_day_of_week', '0')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('default_calendar_view', 'month')");
    }

    function down($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event`");
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event_reminder`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `due_date`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `overdue_email_sent`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` DROP COLUMN `color`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` DROP COLUMN `usage`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `notify_overdue_unassigned`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `default_calendar_view`");
        $this->executeQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` WHERE `Key` = 'enable_calendar'");
        $this->executeQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` WHERE `Key` = 'first_day_of_week'");
        $this->executeQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` WHERE `Key` = 'default_calendar_view'");
    }
}