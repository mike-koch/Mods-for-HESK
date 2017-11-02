<?php

namespace Pre140\Statuses;


use AbstractMigration;

class DropOldStatusColumn extends AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `status`");
    }

    function down($hesk_settings) {
        $ticketsRS = $this->executeQuery("SELECT `id`, `status_int` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets`;");
        while ($currentResult = hesk_dbFetchAssoc($ticketsRS)) {

            $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `status_int` = '" . intval($currentResult['status']) . "' WHERE `id` = " . $currentResult['id']);
        }
    }
}