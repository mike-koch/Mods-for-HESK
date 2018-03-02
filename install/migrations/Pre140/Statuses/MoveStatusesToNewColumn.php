<?php

namespace Pre140\Statuses;

use AbstractMigration;

class MoveStatusesToNewColumn extends AbstractMigration {

    function up($hesk_settings) {
        // We no longer need to do this thanks to HESK 2.7.0
        //$ticketsRS = $this->executeQuery("SELECT `id`, `status` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets`;");
        //while ($currentResult = hesk_dbFetchAssoc($ticketsRS)) {
        //
        //    $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `status_int` = " . $currentResult['status'] . " WHERE `id` = " . $currentResult['id']);
        //}
    }

    function down($hesk_settings) {
        // We no longer need to do this thanks to HESK 2.7.0
        //$this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` CHANGE COLUMN `status_int` `status` INT NOT NULL");
    }
}