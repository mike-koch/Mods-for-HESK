<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 1/19/17
 * Time: 8:51 PM
 */

namespace BusinessLogic\Security;


class UserContextNotifications {
    public $newUnassigned;
    public $newAssignedToMe;
    public $replyUnassigned;
    public $replyToMe;
    public $privateMessage;
    public $noteOnTicketAssignedToMe;
    public $noteOnTicketNotAssignedToMe;
    public $overdueTicketUnassigned;
}