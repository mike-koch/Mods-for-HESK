<?php

namespace Pre140\Statuses;


class CreateStatusesTable extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` (
                      `ID` INT NOT NULL,
                      `ShortNameContentKey` TEXT NOT NULL,
                      `TicketViewContentKey` TEXT NOT NULL,
                      `TextColor` TEXT NOT NULL,
                      `IsNewTicketStatus` INT NOT NULL DEFAULT 0,
                      `IsClosed` INT NOT NULL DEFAULT 0,
                      `IsClosedByClient` INT NOT NULL DEFAULT 0,
                      `IsCustomerReplyStatus` INT NOT NULL DEFAULT 0,
                      `IsStaffClosedOption` INT NOT NULL DEFAULT 0,
                      `IsStaffReopenedStatus` INT NOT NULL DEFAULT 0,
                      `IsDefaultStaffReplyStatus` INT NOT NULL DEFAULT 0,
                      `LockedTicketStatus` INT NOT NULL DEFAULT 0,
                        PRIMARY KEY (`ID`))");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `status_int` INT NOT NULL AFTER `status`;");
    }
}