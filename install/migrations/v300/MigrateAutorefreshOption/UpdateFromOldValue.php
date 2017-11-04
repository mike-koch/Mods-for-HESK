<?php

namespace v300\MigrateAutorefreshOption;


class UpdateFromOldValue extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `autoreload` = `autorefresh` / 10");
    }

    function down($hesk_settings) {
        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `autorefresh` = `autoreload` * 10");
    }
}