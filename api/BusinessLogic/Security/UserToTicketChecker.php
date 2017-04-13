<?php

namespace BusinessLogic\Security;


use BusinessLogic\Tickets\Ticket;
use DataAccess\Security\UserGateway;

class UserToTicketChecker {
    /* @var $userGateway UserGateway */
    private $userGateway;

    function __construct($userGateway) {
        $this->userGateway = $userGateway;
    }

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

        if ($isEditing) {
            $categoryManagerId = $this->userGateway->getManagerForCategory($ticket->categoryId, $heskSettings);

            $hasAccess = $hasAccess &&
                ($user->admin === true
                    || in_array(UserPrivilege::CAN_EDIT_TICKETS, $user->permissions)
                    || $categoryManagerId == $user->id);
        }

        return $hasAccess;
    }
}