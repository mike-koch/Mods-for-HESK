<?php

namespace v240;


class CreateNewStatusNameTable extends \AbstractMigration {

    function up($hesk_settings) {
        global $hesklang;

        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "text_to_status_xref` (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `language` VARCHAR(200) NOT NULL,
            `text` VARCHAR(200) NOT NULL,
            `status_id` INT NOT NULL) ENGINE = MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ADD COLUMN `sort` INT");
        $statusesRs = $this->executeQuery("SELECT `ID` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ORDER BY `ID` ASC");
        $i = 10;
        while ($myStatus = hesk_dbFetchAssoc($statusesRs)) {
            $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` SET `sort`=" . intval($i) . "
            WHERE `id`='" . intval($myStatus['ID']) . "' LIMIT 1");
            $i += 10;
        }

        $languages = array();
        foreach ($hesk_settings['languages'] as $key => $value) {
            $languages[$key] = $hesk_settings['languages'][$key]['folder'];
        }

        $statusesRs = $this->executeQuery("SELECT `ID`, `Key` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses`");
        $oldSetting = $hesk_settings['can_sel_lang'];
        $hesk_settings['can_sel_lang'] = 1;
        while ($row = hesk_dbFetchAssoc($statusesRs)) {
            foreach ($languages as $language => $languageCode) {
                hesk_setLanguage($language);
                $sql = "INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "text_to_status_xref` (`language`, `text`, `status_id`)
                VALUES ('" . hesk_dbEscape($language) . "', '" . hesk_dbEscape($hesklang[$row['Key']]) . "', " . intval($row['ID']) . ")";
                $this->executeQuery($sql);
            }
        }
        $hesk_settings['can_sel_lang'] = $oldSetting;
        hesk_resetLanguage();
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` DROP COLUMN `sort`");
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "text_to_status_xref`");
    }
}