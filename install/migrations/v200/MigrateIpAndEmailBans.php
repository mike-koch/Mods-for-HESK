<?php

namespace v200;


class MigrateIpAndEmailBans extends \AbstractMigration {

    function up($hesk_settings) {
        // Insert the email bans
        $emailBanRS = executeQuery("SELECT `Email` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_emails`");
        while ($row = hesk_dbFetchAssoc($emailBanRS)) {
            $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "banned_emails` (`email`, `banned_by`, `dt`)
                VALUES ('" . hesk_dbEscape($row['Email']) . "', 1, NOW())");
        }

        // Insert the IP bans
        $ipBanRS = executeQuery("SELECT `RangeStart`, `RangeEnd` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_ips`");
        while ($row = hesk_dbFetchAssoc($ipBanRS)) {
            $ipFrom = long2ip($row['RangeStart']);
            $ipTo = long2ip($row['RangeEnd']);
            $ipDisplay = $ipFrom == $ipTo ? $ipFrom : $ipFrom . ' - ' . $ipTo;
            $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "banned_ips` (`ip_from`, `ip_to`, `ip_display`, `banned_by`, `dt`)
                VALUES (" . $row['RangeStart'] . ", " . $row['RangeEnd'] . ", '" . $ipDisplay . "', 1, NOW())");
        }
        // Migration Complete. Drop Tables.
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_ips`");
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_emails`");
    }

    function down($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_ips` (
          `ID` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
          `RangeStart` VARCHAR(100) NOT NULL,
          `RangeEnd` VARCHAR(100) NOT NULL)");

        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_emails` (
            ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
            Email VARCHAR(100) NOT NULL);");

        $emails = $this->executeQuery("SELECT `email` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "banned_emails`");
        while ($row = hesk_dbFetchAssoc($emails)) {
            $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_emails` (Email) VALUES ('" . hesk_dbEscape($row['email']) . "')");
        }

        $ips = $this->executeQuery("SELECT `ip_from`, `ip_to` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "banned_ips`");
        while ($row = hesk_dbFetchAssoc($ips)) {
            $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_ips` (`RangeStart`, `RangeEnd`) VALUES (" . $row['ip_from'] . ", " . $row['ip_to'] . ")");
        }

        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "banned_ips`");
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "banned_emails`");
    }
}