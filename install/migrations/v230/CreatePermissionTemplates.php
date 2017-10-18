<?php

namespace v230;


class CreatePermissionTemplates extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `permission_template` INT");
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "permission_templates` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(255) NOT NULL,
                    `heskprivileges` VARCHAR(1000),
                    `categories` VARCHAR(500))");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "permission_templates` (`name`, `heskprivileges`, `categories`)
        VALUES ('Administrator', 'ALL', 'ALL')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "permission_templates` (`name`, `heskprivileges`, `categories`)
        VALUES ('Staff', 'can_view_tickets,can_reply_tickets,can_change_cat,can_assign_self,can_view_unassigned,can_view_online', '1')");
        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `permission_template` = 1 WHERE `isadmin` = '1'");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `permission_template`");
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "permission_templates`");
    }
}