<?php

namespace v200\MigrateIpAndEmailBans;

class InsertEmailBans extends \AbstractMigration {

    function up($hesk_settings) {
        $emailBanRS = $this->executeQuery("SELECT `Email` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_emails`");
        while ($row = hesk_dbFetchAssoc($emailBanRS)) {
            $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "banned_emails` (`email`, `banned_by`, `dt`)
                VALUES ('" . hesk_dbEscape($row['Email']) . "', 1, NOW())");
        }
    }

    function down($hesk_settings) {
        $emails = $this->executeQuery("SELECT `email` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "banned_emails`");
        while ($row = hesk_dbFetchAssoc($emails)) {
            $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_emails` (Email) VALUES ('" . hesk_dbEscape($row['email']) . "')");
        }
    }
}