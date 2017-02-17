<?php

namespace BusinessLogic\Security;


class BannedIp {
    /**
     * @var int
     */
    public $id;

    /**
     * @var int the lower bound of the IP address range
     */
    public $ipFrom;

    /**
     * @var int the upper bound of the IP address range
     */
    public $ipTo;

    /**
     * @var string the display of the IP ban to be shown to the user
     */
    public $ipDisplay;

    /**
     * @var int|null The user who banned the IP, or null if the user was deleted
     */
    public $bannedById;

    /**
     * @var string
     */
    public $dateBanned;
}