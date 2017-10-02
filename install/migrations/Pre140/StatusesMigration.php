<?php

namespace Pre140;

use AbstractMigration;

class StatusesMigration extends AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `status_int` INT NOT NULL DEFAULT 0 AFTER `status`;");

        $ticketsRS = $this->executeQuery("SELECT `id`, `status` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets`;");
        while ($currentResult = hesk_dbFetchAssoc($ticketsRS)) {

            $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `status_int` = " . $currentResult['status'] . " WHERE `id` = " . $currentResult['id']);
        }
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `status`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` CHANGE COLUMN `status_int` `status` INT NOT NULL");

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
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (0, 'open', 'open', '#FF0000', 1, 0, 0, 0, 0, 0, 0, 0);");

        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (1, 'wait_reply', 'wait_staff_reply', '#FF9933', 0, 0, 0, 1, 0, 1, 0, 0);");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (2, 'replied', 'wait_cust_reply', '#0000FF', 0, 0, 0, 0, 0, 0, 1, 0);");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (3, 'resolved', 'resolved', '#008000', 0, 1, 1, 0, 1, 0, 0, 1);");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (4, 'in_progress', 'in_progress', '#000000', 0, 0, 0, 0, 0, 0, 0, 0);");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (5, 'on_hold', 'on_hold', '#000000', 0, 0, 0, 0, 0, 0, 0, 0);");

        $keyRs = $this->executeQuery("SHOW KEYS FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE Key_name='statuses'");
        if (hesk_dbNumRows($keyRs) == 0) {
            //-- Add the key
            $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD KEY `statuses` (`status`)");
        }
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `status_int` INT NOT NULL AFTER `status`;");
        $ticketsRS = $this->executeQuery("SELECT `id`, `status` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets`;");
        while ($currentResult = hesk_dbFetchAssoc($ticketsRS)) {

            $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `status_int` = '" . intval($currentResult['status']) . "' WHERE `id` = " . $currentResult['id']);
        }
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `status`");
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` CHANGE COLUMN `status_int` `status` INT NOT NULL");
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses`");
    }
}