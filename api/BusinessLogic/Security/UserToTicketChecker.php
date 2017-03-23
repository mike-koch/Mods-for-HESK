<?php

namespace BusinessLogic\Security;


use BusinessLogic\Tickets\Ticket;

class UserToTicketChecker {

    /**
     * @param $user UserContext
     * @param $ticket Ticket
     * @param $isEditing bool true if editing a ticket, false if creating
     * @param $heskSettings array
     * @return bool
     */
    function isTicketWritableToUser($user, $ticket, $isEditing, $heskSettings) {
        $hasAccess = $user->admin === true ||
            (in_array($ticket->categoryId, $user->categories) &&
                in_array(UserPrivilege::CAN_VIEW_TICKETS, $user->permissions));

        return $isEditing
            ? $hasAccess && in_array(UserPrivilege::CAN_EDIT_TICKETS, $user->permissions)
            : $hasAccess;
    }
}