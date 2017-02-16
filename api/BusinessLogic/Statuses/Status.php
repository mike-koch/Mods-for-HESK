<?php

namespace BusinessLogic\Statuses;


class Status {
    /**
     * @var $id int
     */
    public $id;

    /**
     * @var $textColor string
     */
    public $textColor;

    /**
     * @var $defaultActions DefaultStatusForAction[]
     */
    public $defaultActions;

    /**
     * @var $closable Closable
     */
    public $closable;

    /**
     * @var $sort int
     */
    public $sort;

    /**
     * @var $name string[]
     */
    public $localizedNames;
}