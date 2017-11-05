<?php

namespace v310\AddMoreColorOptionsToCategories;


class AddForegroundColor extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ADD COLUMN `foreground_color` VARCHAR(7) NOT NULL DEFAULT 'AUTO'");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` DROP COLUMN `foreground_color`");
    }
}