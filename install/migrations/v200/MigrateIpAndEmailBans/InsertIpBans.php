<?php

namespace v200\MigrateIpAndEmailBans;


class InsertIpBans extends \AbstractMigration {

    function up($hesk_settings) {
        $ipBanRS = $this->executeQuery("SELECT `RangeStart`, `RangeEnd` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_ips`");
        while ($row = hesk_dbFetchAssoc($ipBanRS)) {
            $ipFrom = long2ip($row['RangeStart']);
            $ipTo = long2ip($row['RangeEnd']);
            $ipDisplay = $ipFrom == $ipTo ? $ipFrom : $ipFrom . ' - ' . $ipTo;
            $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "banned_ips` (`ip_from`, `ip_to`, `ip_display`, `banned_by`, `dt`)
                VALUES (" . $row['RangeStart'] . ", " . $row['RangeEnd'] . ", '" . $ipDisplay . "', 1, NOW())");
        }
    }

    function down($hesk_settings) {
        $ips = $this->executeQuery("SELECT `ip_from`, `ip_to` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "banned_ips`");
        while ($row = hesk_dbFetchAssoc($ips)) {
            $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_ips` (`RangeStart`, `RangeEnd`) VALUES (" . $row['ip_from'] . ", " . $row['ip_to'] . ")");
        }
    }
}