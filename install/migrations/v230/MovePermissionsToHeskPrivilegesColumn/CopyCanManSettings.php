<?php

namespace v230;


class CopyCanManSettings extends \AbstractMigration {

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
    }

    function down($hesk_settings) {
        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` 
            SET `can_manage_settings` = '0'
            WHERE `heskprivileges` NOT LIKE '%can_man_settings%'");
    }
}