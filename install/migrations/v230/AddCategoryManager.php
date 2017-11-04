<?php

namespace v230;


class AddCategoryManager extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ADD COLUMN `manager` INT NOT NULL DEFAULT 0");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` DROP COLUMN `manager`");
    }
}