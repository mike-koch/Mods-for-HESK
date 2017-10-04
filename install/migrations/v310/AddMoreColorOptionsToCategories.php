<?php

namespace v310;


class AddMoreColorOptionsToCategories extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ADD COLUMN `foreground_color` VARCHAR(7) NOT NULL DEFAULT 'AUTO'");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ADD COLUMN `display_border_outline` ENUM('0','1') NOT NULL DEFAULT '0'");
        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` SET `color` = '#FFFFFF' WHERE `color` IS NULL");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` CHANGE `color` `background_color` VARCHAR(7) NOT NULL DEFAULT '#FFFFFF'");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` DROP COLUMN `foreground_color`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` DROP COLUMN `display_border_outline`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` CHANGE `background_color` `color` VARCHAR(7) NOT NULL DEFAULT '#FFFFFF'");
    }
}