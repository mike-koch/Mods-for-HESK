<?php

namespace vv201820;


class MigrateServiceMessageLanguages extends \AbstractUpdatableMigration {
    function innerUp($hesk_settings) {
        // Get all service messages with non-null language (only HESK will populate this; MFH won't)
        $rs = hesk_dbQuery("SELECT `id`, `language` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages`
            WHERE `language` IS NOT NULL");

        $languageMap = array();
        while ($row = hesk_dbFetchAssoc($rs)) {
            // Get the MFH language
            if (count($languageMap) === 0) {
                // Initialize the map for the first run
                foreach($hesk_settings['languages'] as $name => $info) {
                    $languageMap[$name] = $info['folder'];
                }
            }

            $mfh_language = $languageMap[$row['language']];

            hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages`
                SET `mfh_language` = '" . hesk_dbEscape($mfh_language) . "' WHERE `id` = " . intval($row['id']));
        }
    }

    function innerDown($hesk_settings) {
        // Get all service messages with non-null language (only HESK will populate this; MFH won't)
        $rs = hesk_dbQuery("SELECT `id`, `mfh_language` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages`");

        $language_map = array();
        while ($row = hesk_dbFetchAssoc($rs)) {
            // Get the language
            if (count($language_map) === 0) {
                // Initialize the map for the first run
                foreach($hesk_settings['languages'] as $name => $info) {
                    $language_map[$info['folder']] = $name;
                }
            }

            $language = $row['mfh_language'] === 'ALL' ? 'NULL' : "'" . hesk_dbEscape($language_map[$row['mfh_language']]) . "'";

            hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages`
                SET `language` = {$language} WHERE `id` = " . intval($row['id']));
        }
    }
}