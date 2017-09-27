<?php

namespace BusinessLogic\Tickets;


class AuditTrail extends \BaseClass {
    /* @var $id int */
    public $id;

    /* @var $entityId int */
    public $entityId;

    /* @var $entityType string */
    public $entityType;

    /* @var $languageKey string */
    public $languageKey;

    /* @var $date string */
    public $date;

    /* @var $replacementValues string[] */
    public $replacementValues;
}