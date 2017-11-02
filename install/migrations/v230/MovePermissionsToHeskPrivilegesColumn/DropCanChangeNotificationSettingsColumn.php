<?php

namespace v230\MovePermissionsToHeskPrivilegesColumn;


class DropCanChangeNotificationSettingsColumn extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `can_change_notification_settings`");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `can_change_notification_settings` ENUM('0', '1') NOT NULL DEFAULT '1'");
    }
}