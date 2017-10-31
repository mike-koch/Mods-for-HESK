<?php

namespace v300\MigrateAutorefreshOption;


class DropOldColumn extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `autorefresh`");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `autorefresh` BIGINT NOT NULL DEFAULT 0");
    }
}