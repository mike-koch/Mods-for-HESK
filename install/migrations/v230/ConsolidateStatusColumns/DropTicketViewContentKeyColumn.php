<?php

namespace v230\ConsolidateStatusColumns;


class DropTicketViewContentKeyColumn extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` DROP COLUMN `TicketViewContentKey`");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ADD COLUMN `TicketViewContentKey` TEXT");
    }
}