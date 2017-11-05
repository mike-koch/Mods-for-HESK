<?php

namespace v230\CreatePermissionTemplates;


class UpdateAdminUsersTemplate extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `permission_template` = 1 WHERE `isadmin` = '1'");
    }

    function down($hesk_settings) {
    }
}