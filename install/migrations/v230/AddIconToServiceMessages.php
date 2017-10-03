<?php

namespace v230;


class AddIconToServiceMessages extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages` ADD COLUMN `icon` VARCHAR(150)");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages` DROP COLUMN `icon`");
    }
}