<?php

namespace BusinessLogic\Tickets;


class Autoassigner {
    /**
     * @param $categoryId int
     * @param $heskSettings array
     * @return int|null The user ID, or null if no user found
     */
    function getNextUserForTicket($categoryId, $heskSettings) {
        return 0;
    }
}