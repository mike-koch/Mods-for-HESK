<?php

namespace vv201830;


class ReallyMakeSureCustom21Thru50AreOnStageTickets extends \AbstractUpdatableMigration {

    function innerUp($hesk_settings) {
        for ($i = 21; $i <= 50; $i++) {
            $rs = $this->executeQuery("SELECT 1
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE table_name = '" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets'
                AND table_schema = '" . hesk_dbEscape($hesk_settings['db_name']) . "'
                AND column_name = 'custom{$i}'");

            if (hesk_dbNumRows($rs) === 0) {
                $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` ADD COLUMN `custom{$i}` MEDIUMTEXT");
            }
        }
    }

    function innerDown($hesk_settings) {
        // This is a safety migration; there is no down as v302 should handle it
    }
}