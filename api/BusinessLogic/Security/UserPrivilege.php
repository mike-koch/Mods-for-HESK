<?php
/**
 * Created by PhpStorm.
 * User: mkoch
 * Date: 3/12/2017
 * Time: 12:11 PM
 */

namespace BusinessLogic\Security;


class UserPrivilege extends \BaseClass {
    const CAN_VIEW_TICKETS = 'can_view_tickets';
    const CAN_REPLY_TO_TICKETS = 'can_reply_tickets';
    const CAN_EDIT_TICKETS = 'can_edit_tickets';
    const CAN_DELETE_TICKETS = 'can_del_tickets';
    const CAN_MANAGE_CATEGORIES = 'can_man_cat';
    const CAN_VIEW_ASSIGNED_TO_OTHER = 'can_view_ass_others';
    const CAN_VIEW_UNASSIGNED = 'can_view_unassigned';
    const CAN_VIEW_ASSIGNED_BY_ME = 'can_view_ass_by';
    const CAN_MANAGE_SERVICE_MESSAGES = 'can_service_msg';
}