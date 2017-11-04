<?php

namespace v260;


class AddPrimaryKeyToSettings extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` ADD PRIMARY KEY (`Key`)");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` DROP PRIMARY KEY");
    }
}