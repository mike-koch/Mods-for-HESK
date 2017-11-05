<?php

namespace v160;


class CreateSettingsTable extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key` NVARCHAR(200) NOT NULL, `Value` NVARCHAR(200) NOT NULL)");
    }

    function down($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings`");
    }
}