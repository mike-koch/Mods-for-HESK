<?php

namespace v230\CreatePermissionTemplates;


class InsertAdminPermissionTemplate extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "permission_templates` (`name`, `heskprivileges`, `categories`)
        VALUES ('Administrator', 'ALL', 'ALL')");
    }

    function down($hesk_settings) {
    }
}