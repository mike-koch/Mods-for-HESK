<?php

namespace BusinessLogic\ServiceMessages;


class ServiceMessage extends \BaseClass {
    /* @var $id int */
    public $id;

    /* @var $dateCreated string */
    public $dateCreated;

    /* @var $createdBy int */
    public $createdBy;

    /* @var $title string */
    public $title;

    /* @var $message string */
    public $message;

    /* @var $style string */
    public $style;

    /* @var $published bool */
    public $published;

    /* @var $order int */
    public $order;

    /* @var $icon string */
    public $icon;

    /* @var $locations string[] */
    public $locations;
}