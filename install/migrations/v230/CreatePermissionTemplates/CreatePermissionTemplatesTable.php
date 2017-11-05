<?php

namespace v230\CreatePermissionTemplates;


class CreatePermissionTemplatesTable extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "permission_templates` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(255) NOT NULL,
                    `heskprivileges` VARCHAR(1000),
                    `categories` VARCHAR(500))");
    }

    function down($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "permission_templates`");
    }
}