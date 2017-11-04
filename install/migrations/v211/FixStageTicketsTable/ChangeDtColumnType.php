<?php

namespace v211\FixStageTicketsTable;

class ChangeDtColumnType extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` CHANGE `dt` `dt` TIMESTAMP NOT NULL DEFAULT '2000-01-01 00:00:00'");
    }

    function down($hesk_settings) {
        // NOOP
    }
}