<?php

namespace BusinessLogic\Attachments;


class CreateAttachmentForTicketModel extends CreateAttachmentModel {
    /* @var $ticketId int */
    public $ticketId;

    /* @var $type int [use <code>AttachmentType</code] */
    public $type;
}