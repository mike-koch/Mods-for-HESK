<?php

namespace v240\CreateNewStatusNameTable;


class UpdateSortValues extends \AbstractMigration {

    function up($hesk_settings) {
        $statusesRs = $this->executeQuery("SELECT `ID` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ORDER BY `ID` ASC");
        $i = 10;
        while ($myStatus = hesk_dbFetchAssoc($statusesRs)) {
            $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` SET `sort`=" . intval($i) . "
            WHERE `id`='" . intval($myStatus['ID']) . "' LIMIT 1");
            $i += 10;
        }
    }

    function down($hesk_settings) {
    }
}