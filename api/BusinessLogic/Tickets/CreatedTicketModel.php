<?php

namespace BusinessLogic\Tickets;


class CreatedTicketModel {
    /* @var $ticket Ticket */
    public $ticket;

    /* @var $emailVerified bool */
    public $emailVerified;

    function __construct($ticket, $emailVerified) {
        $this->ticket = $ticket;
        $this->emailVerified = $emailVerified;
    }
}