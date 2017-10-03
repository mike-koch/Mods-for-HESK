<?php

namespace v250;


class AddUserAgentAndScreenResToTickets extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `user_agent` TEXT");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `screen_resolution_width` INT");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `screen_resolution_height` INT");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` ADD COLUMN `user_agent` TEXT");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` ADD COLUMN `screen_resolution_width` INT");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` ADD COLUMN `screen_resolution_height` INT");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('display_user_agent_information', '0')");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `user_agent`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `screen_resolution_width`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `screen_resolution_height`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` DROP COLUMN `user_agent`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` DROP COLUMN `screen_resolution_width`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` DROP COLUMN `screen_resolution_height`");
        $this->executeQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` WHERE `Key` = 'display_user_agent_information'");
    }
}