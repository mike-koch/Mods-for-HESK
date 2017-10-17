<?php

namespace v230;


class MovePermissionsToHeskPrivilegesColumn extends \AbstractMigration {

    function up($hesk_settings) {
        // Move can_manage_settings and can_change_notification_settings into the heskprivileges list
        $res = $this->executeQuery("SELECT `id`, `heskprivileges` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `isadmin` = '0'
        AND `can_manage_settings` = '1'");
        while ($row = hesk_dbFetchAssoc($res)) {
            if ($row['heskprivileges'] != '') {
                $currentPrivileges = explode(',', $row['heskprivileges']);
                array_push($currentPrivileges, 'can_man_settings');
                $newPrivileges = implode(',', $currentPrivileges);
                $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `heskprivileges` = '" . hesk_dbEscape($newPrivileges) . "'
            WHERE `id` = " . intval($row['id']));
            } else {
                $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `heskprivileges` = 'can_man_settings'
            WHERE `id` = " . intval($row['id']));
            }
        }
        $res = $this->executeQuery("SELECT `id`, `heskprivileges` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `isadmin` = '0'
        AND `can_change_notification_settings` = '1'");
        while ($row = hesk_dbFetchAssoc($res)) {
            if ($row['heskprivileges'] != '') {
                $currentPrivileges = explode(',', $row['heskprivileges']);
                array_push($currentPrivileges, 'can_change_notification_settings');
                $newPrivileges = implode(',', $currentPrivileges);
                $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `heskprivileges` = '" . hesk_dbEscape($newPrivileges) . "'
            WHERE `id` = " . intval($row['id']));
            } else {
                $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `heskprivileges` = 'can_change_notification_settings'
            WHERE `id` = " . intval($row['id']));
            }
        }
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `can_manage_settings`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `can_change_notification_settings`");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `can_change_notification_settings` ENUM('0', '1') NOT NULL DEFAULT '1'");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `can_manage_settings` ENUM ('0', '1') NOT NULL DEFAULT '1'");

        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` 
            SET `can_manage_settings` = '0'
            WHERE `heskprivileges` NOT LIKE '%can_man_settings%'");
        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users`
            SET `can_change_notification_settings` = '0'
            WHERE `heskprivileges` NOT LIKE '%can_change_notification_settings%'");
    }
}