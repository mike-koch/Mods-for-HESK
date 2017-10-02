<?php

namespace v140;

use AbstractMigration;

class AddAutorefreshColumn extends AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `autorefresh` BIGINT NOT NULL DEFAULT 0 AFTER `replies`;");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `autorefresh`;");
    }
}