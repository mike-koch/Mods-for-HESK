<?php

namespace BusinessLogic\Tickets;


class CreateReplyRequest {
    public $trackingId;
    public $emailAddress;
    public $replyMessage;
    public $hasHtml;
    public $ipAddress;
}