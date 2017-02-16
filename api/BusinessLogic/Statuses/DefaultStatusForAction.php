<?php
/**
 * Created by PhpStorm.
 * User: mkoch
 * Date: 2/15/2017
 * Time: 9:46 PM
 */

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
    const AUTOCLOSE_STATUS = "IsAutoCloseOption";
}