<?php

namespace v240\CreateNewStatusNameTable;


class InsertTextToStatusXrefValues extends \AbstractMigration {

    function up($hesk_settings) {
        global $hesklang;

        $languages = array();
        foreach ($hesk_settings['languages'] as $key => $value) {
            $languages[$key] = $hesk_settings['languages'][$key]['folder'];
        }

        $statusesRs = $this->executeQuery("SELECT `ID`, `Key` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses`");
        $oldSetting = $hesk_settings['can_sel_lang'];
        $hesk_settings['can_sel_lang'] = 1;
        while ($row = hesk_dbFetchAssoc($statusesRs)) {
            $englishText = '';
            foreach ($languages as $language => $languageCode) {
                hesk_setLanguage($language);

                if ($language === 'English') {
                    if (key_exists($row['Key'], $hesklang)) {
                        $englishText = $hesklang[$row['Key']];
                    } else {
                        $englishText = $row['Key'];
                    }
                }

                if (key_exists($row['Key'], $hesklang)) {
                    $textToInsert = $hesklang[$row['Key']];
                } else {
                    $textToInsert = $englishText;
                }

                $sql = "INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "text_to_status_xref` (`language`, `text`, `status_id`)
                VALUES ('" . hesk_dbEscape($language) . "', '" . hesk_dbEscape($textToInsert) . "', " . intval($row['ID']) . ")";
                $this->executeQuery($sql);
            }
        }
        $hesk_settings['can_sel_lang'] = $oldSetting;
        hesk_resetLanguage();
    }

    function down($hesk_settings) {
        $this->executeQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "text_to_status_xref`");
    }
}