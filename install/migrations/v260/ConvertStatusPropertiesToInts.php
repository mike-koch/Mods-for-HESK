<?php

namespace v260;


class ConvertStatusPropertiesToInts extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` CHANGE  `IsNewTicketStatus`  `IsNewTicketStatus` INT( 1 ) NOT NULL DEFAULT  '0',
            CHANGE  `IsClosed`  `IsClosed` INT( 1 ) NOT NULL DEFAULT  '0',
            CHANGE  `IsClosedByClient`  `IsClosedByClient` INT( 1 ) NOT NULL DEFAULT  '0',
            CHANGE  `IsCustomerReplyStatus`  `IsCustomerReplyStatus` INT( 1 ) NOT NULL DEFAULT  '0',
            CHANGE  `IsStaffClosedOption`  `IsStaffClosedOption` INT( 1 ) NOT NULL DEFAULT  '0',
            CHANGE  `IsStaffReopenedStatus`  `IsStaffReopenedStatus` INT( 1 ) NOT NULL DEFAULT  '0',
            CHANGE  `IsDefaultStaffReplyStatus`  `IsDefaultStaffReplyStatus` INT( 1 ) NOT NULL DEFAULT  '0',
            CHANGE  `LockedTicketStatus`  `LockedTicketStatus` INT( 1 ) NOT NULL DEFAULT  '0'");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` CHANGE  `IsNewTicketStatus`  `IsNewTicketStatus` INT( 1 ) NOT NULL DEFAULT  '0',
            CHANGE  `IsClosed`  `IsClosed` ENUM('0','1') NOT NULL DEFAULT  '0',
            CHANGE  `IsClosedByClient`  `IsClosedByClient` ENUM('0','1') NOT NULL DEFAULT  '0',
            CHANGE  `IsCustomerReplyStatus`  `IsCustomerReplyStatus` ENUM('0','1') NOT NULL DEFAULT  '0',
            CHANGE  `IsStaffClosedOption`  `IsStaffClosedOption` ENUM('0','1') NOT NULL DEFAULT  '0',
            CHANGE  `IsStaffReopenedStatus`  `IsStaffReopenedStatus` ENUM('0','1') NOT NULL DEFAULT  '0',
            CHANGE  `IsDefaultStaffReplyStatus`  `IsDefaultStaffReplyStatus` ENUM('0','1') NOT NULL DEFAULT  '0',
            CHANGE  `LockedTicketStatus`  `LockedTicketStatus` ENUM('0','1') NOT NULL DEFAULT  '0'");
    }
}