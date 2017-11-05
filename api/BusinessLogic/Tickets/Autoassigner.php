<?php

namespace BusinessLogic\Tickets;


use BusinessLogic\Security\UserContext;
use BusinessLogic\Security\UserPrivilege;
use DataAccess\Categories\CategoryGateway;
use DataAccess\Security\UserGateway;

class Autoassigner extends \BaseClass {
    /* @var $categoryGateway CategoryGateway */
    private $categoryGateway;

    /* @var $userGateway UserGateway */
    private $userGateway;

    function __construct(CategoryGateway $categoryGateway,
                         UserGateway $userGateway) {
        $this->categoryGateway = $categoryGateway;
        $this->userGateway = $userGateway;
    }

    /**
     * @param $categoryId int
     * @param $heskSettings array
     * @return UserContext the user who is assigned, or null if no user should be assigned
     */
    function getNextUserForTicket($categoryId, $heskSettings) {
        if (!$heskSettings['autoassign']) {
            return null;
        }

        $potentialUsers = $this->userGateway->getUsersByNumberOfOpenTicketsForAutoassign($heskSettings);

        foreach ($potentialUsers as $potentialUser) {
            if ($potentialUser->admin ||
                (in_array($categoryId, $potentialUser->categories) &&
                    in_array(UserPrivilege::CAN_VIEW_TICKETS, $potentialUser->permissions) &&
                    in_array(UserPrivilege::CAN_REPLY_TO_TICKETS, $potentialUser->permissions))) {
                return $potentialUser;
            }
        }


        return null;
    }
}