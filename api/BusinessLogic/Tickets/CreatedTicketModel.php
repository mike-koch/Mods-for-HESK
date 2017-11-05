<?php

namespace BusinessLogic\Tickets;


class CreatedTicketModel extends \BaseClass {
    /* @var $ticket Ticket */
    public $ticket;

    /* @var $emailVerified bool */
    public $emailVerified;

    function __construct($ticket, $emailVerified) {
        $this->ticket = $ticket;
        $this->emailVerified = $emailVerified;
    }
}