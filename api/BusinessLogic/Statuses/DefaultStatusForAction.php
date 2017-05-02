<?php

namespace BusinessLogic\Statuses;


class DefaultStatusForAction {
    const NEW_TICKET = "IsNewTicketStatus";
    const CLOSED_STATUS = "IsClosed";
    const CLOSED_BY_CLIENT = "IsClosedByClient";
    const CUSTOMER_REPLY = "IsCustomerReplyStatus";
    const CLOSED_BY_STAFF = "IsStaffClosedOption";
    const REOPENED_BY_STAFF = "IsStaffReopenedStatus";
    const DEFAULT_STAFF_REPLY = "IsDefaultStaffReplyStatus";
    const LOCKED_TICKET = "LockedTicketStatus";
    const AUTOCLOSE_STATUS = "IsAutocloseOption";

    static function getAll() {
        return array(
            self::NEW_TICKET,
            self::CLOSED_STATUS,
            self::CLOSED_BY_CLIENT,
            self::CUSTOMER_REPLY,
            self::CLOSED_BY_STAFF,
            self::REOPENED_BY_STAFF,
            self::DEFAULT_STAFF_REPLY,
            self::LOCKED_TICKET,
            self::AUTOCLOSE_STATUS
        );
    }
}