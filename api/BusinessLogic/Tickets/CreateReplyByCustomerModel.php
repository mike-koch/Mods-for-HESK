<?php

namespace BusinessLogic\Tickets;


class CreateReplyByCustomerModel extends \BaseClass {
    /* @var $id int */
    public $id;

    /* @var $trackingId string */
    public $trackingId;

    /* @var $emailAddress string|null */
    public $emailAddress;

    /* @var $message string */
    public $message;

    /* @var $html bool */
    public $html;
}