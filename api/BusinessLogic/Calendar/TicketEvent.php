<?php

namespace BusinessLogic\Calendar;


class TicketEvent extends AbstractEvent {
    public $type = 'TICKET';

    public $trackingId;

    public $subject;

    public $url;

    public $owner;

    public $priority;

    public $status;
}