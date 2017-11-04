<?php

namespace Pre140\Statuses;


class InsertStatusRecords extends \AbstractMigration {

    function up($hesk_settings) {
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
    }

    function down($hesk_settings) {
        $this->executeQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses`");
    }
}