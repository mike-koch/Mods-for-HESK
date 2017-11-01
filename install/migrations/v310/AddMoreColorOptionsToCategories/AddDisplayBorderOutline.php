<?php

namespace v310\AddMoreColorOptionsToCategories;


class AddDisplayBorderOutline extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ADD COLUMN `display_border_outline` ENUM('0','1') NOT NULL DEFAULT '0'");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` DROP COLUMN `display_border_outline`");
    }
}