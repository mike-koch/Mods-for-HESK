<?php

namespace v310\AddMoreColorOptionsToCategories;


class AddBackgroundColor extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` SET `color` = '#FFFFFF' WHERE `color` IS NULL");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` CHANGE `color` `background_color` VARCHAR(7) NOT NULL DEFAULT '#FFFFFF'");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` CHANGE `background_color` `color` VARCHAR(7) NOT NULL DEFAULT '#FFFFFF'");
    }
}