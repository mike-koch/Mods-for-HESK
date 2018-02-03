<?php

namespace Pre140\Statuses;

use AbstractMigration;

class AddIntColumnUpDropTableDown extends AbstractMigration {

    function up($hesk_settings) {
        // We no longer need to do this thanks to HESK 2.7.0
        //$this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `status_int` INT NOT NULL DEFAULT 0 AFTER `status`;");
    }

    function down($hesk_settings) {
        // Moved to migration #2 for clarity
        //$this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses`");
    }
}