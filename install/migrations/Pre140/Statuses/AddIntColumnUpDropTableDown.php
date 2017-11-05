<?php

namespace Pre140\Statuses;

use AbstractMigration;

class AddIntColumnUpDropTableDown extends AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `status_int` INT NOT NULL DEFAULT 0 AFTER `status`;");
    }

    function down($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses`");
    }
}