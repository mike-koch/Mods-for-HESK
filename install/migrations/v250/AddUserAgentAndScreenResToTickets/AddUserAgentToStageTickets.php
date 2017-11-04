<?php

namespace v250\AddUserAgentAndScreenResToTickets;


class AddUserAgentToStageTickets extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` ADD COLUMN `user_agent` TEXT");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` DROP COLUMN `user_agent`");
    }
}