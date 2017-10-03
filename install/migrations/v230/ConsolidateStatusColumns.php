<?php

namespace v230;


class ConsolidateStatusColumns extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ADD COLUMN `Key` TEXT");
        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` SET `Key` = `ShortNameContentKey`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` DROP COLUMN `ShortNameContentKey`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` DROP COLUMN `TicketViewContentKey`");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ADD COLUMN `TicketViewContentKey` TEXT");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ADD COLUMN `ShortNameContentKey` TEXT");
        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` SET `TicketViewContentKey` = `Key`, `ShortNameContentKey` = `Key`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` DROP COLUMN `Key`");
    }
}