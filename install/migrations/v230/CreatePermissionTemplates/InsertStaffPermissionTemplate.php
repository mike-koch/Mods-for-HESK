<?php

namespace v230\CreatePermissionTemplates;


class InsertStaffPermissionTemplate extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "permission_templates` (`name`, `heskprivileges`, `categories`)
        VALUES ('Staff', 'can_view_tickets,can_reply_tickets,can_change_cat,can_assign_self,can_view_unassigned,can_view_online', '1')");
    }

    function down($hesk_settings) {
    }
}