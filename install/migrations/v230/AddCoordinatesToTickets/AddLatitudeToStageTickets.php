<?php

namespace v230\AddCoordinatesToTickets;


class AddLatitudeToStageTickets extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` ADD COLUMN `latitude` VARCHAR(100) NOT NULL DEFAULT 'E-0'");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` DROP COLUMN `latitude`");
    }
}