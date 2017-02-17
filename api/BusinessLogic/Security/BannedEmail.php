<?php

namespace BusinessLogic\Security;


class BannedEmail {
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $email;

    /**
     * @var int|null The user who banned the email, or null if the user was deleted
     */
    public $bannedById;

    /**
     * @var string
     */
    public $dateBanned;
}