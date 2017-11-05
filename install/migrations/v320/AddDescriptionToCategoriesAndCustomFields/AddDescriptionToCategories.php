<?php

namespace v320\AddDescriptionToCategoriesAndCustomFields;


class AddDescriptionToCategories extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories`
            ADD COLUMN `mfh_description` TEXT");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories`
            DROP COLUMN `mfh_description`");
    }
}