<?php

namespace Pre140\Statuses;


class RenameTempColumn extends \AbstractMigration {

    function up($hesk_settings) {
        // We no longer need to do this thanks to HESK 2.7.0
        //$this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` CHANGE COLUMN `status_int` `status` INT NOT NULL");
    }

    function down($hesk_settings) {
        // We no longer need to do this thanks to HESK 2.7.0
        //$this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `status`");
    }
}