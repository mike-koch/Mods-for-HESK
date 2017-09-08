<?php

namespace BusinessLogic\Security;


use BusinessLogic\Tickets\Ticket;
use DataAccess\Security\UserGateway;

class UserToTicketChecker {
    /* @var $userGateway UserGateway */
    private $userGateway;

    function __construct(UserGateway $userGateway) {
        $this->userGateway = $userGateway;
    }

    /**
     * @param $user UserContext
     * @param $ticket Ticket
     * @param $heskSettings array
     * @param $extraPermissions UserPrivilege[] additional privileges the user needs besides CAN_VIEW_TICKETS (if not an admin)
     *     for this to return true
     * @return bool
     */
    function isTicketAccessibleToUser($user, $ticket, $heskSettings, $extraPermissions = array()) {
        if ($user->admin === true) {
            return true;
        }

        if (!in_array($ticket->categoryId, $user->categories)) {
            return false;
        }

        $categoryManagerId = $this->userGateway->getManagerForCategory($ticket->categoryId, $heskSettings);
        
        if ($user->id === $categoryManagerId) {
            return true;
        }

        $extraPermissions[] = UserPrivilege::CAN_VIEW_TICKETS;

        foreach ($extraPermissions as $permission) {
            if (!in_array($permission, $user->permissions)) {
                return false;
            }
        }

        return true;
    }
}