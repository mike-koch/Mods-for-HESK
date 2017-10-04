<?php

namespace v320;


class AddDescriptionToCategoriesAndCustomFields extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories`
            ADD COLUMN `mfh_description` TEXT");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_fields`
            ADD COLUMN `mfh_description` TEXT");

        // Purge the custom field caches as we're adding a new field
        foreach ($hesk_settings['languages'] as $key => $value) {
            $language_hash = sha1($key);
            hesk_unlink(HESK_PATH . "cache/cf_{$language_hash}.cache.php");
        }
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories`
            DROP COLUMN `mfh_description`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_fields`
            DROP COLUMN `mfh_description`");

        // Purge the custom field caches as we're adding a new field
        foreach ($hesk_settings['languages'] as $key => $value) {
            $language_hash = sha1($key);
            hesk_unlink(HESK_PATH . "cache/cf_{$language_hash}.cache.php");
        }
    }
}