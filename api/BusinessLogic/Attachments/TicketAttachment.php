<?php

namespace BusinessLogic\Attachments;


class TicketAttachment extends Attachment {
    /* @var $ticketTrackingId string */
    public $ticketTrackingId;

    /* @var $type int [use <code>AttachmentType</code>] */
    public $type;
}