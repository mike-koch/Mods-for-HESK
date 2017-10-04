<?php

namespace v300;


class MigrateAutorefreshOption extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `autoreload` = `autorefresh` / 10");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `autorefresh`");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `autorefresh` BIGINT NOT NULL DEFAULT 0");
        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `autorefresh` = `autoreload` * 10");
    }
}