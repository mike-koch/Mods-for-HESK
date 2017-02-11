<?php

namespace BusinessLogic\Tickets;


class TrackingIdGenerator {
    private $ticketGateway;

    function __construct($ticketGateway) {
        $this->ticketGateway = $ticketGateway;
    }

    function generateTrackingId() {

    }
}