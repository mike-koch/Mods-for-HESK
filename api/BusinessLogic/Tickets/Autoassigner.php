<?php

namespace BusinessLogic\Tickets;


use BusinessLogic\Security\UserContext;
use DataAccess\Categories\CategoryGateway;
use DataAccess\Security\UserGateway;

class Autoassigner {
    /* @var $categoryGateway CategoryGateway */
    private $categoryGateway;

    /* @var $userGateway UserGateway */
    private $userGateway;

    function __construct($categoryGateway, $userGateway) {
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

        $potentialUsers = $this->userGateway->getUsersByNumberOfOpenTickets($heskSettings);


        return $potentialUsers[0];
    }
}