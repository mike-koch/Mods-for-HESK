<?php

namespace BusinessLogic\Security;


class UserContextNotifications extends \BaseClass {
    public $newUnassigned;
    public $newAssignedToMe;
    public $replyUnassigned;
    public $replyToMe;
    public $ticketAssignedToMe;
    public $privateMessage;
    public $noteOnTicketAssignedToMe;
    public $noteOnTicketNotAssignedToMe;
    public $overdueTicketUnassigned;
}