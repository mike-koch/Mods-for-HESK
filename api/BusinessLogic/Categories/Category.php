<?php

namespace BusinessLogic\Categories;

class Category extends \BaseClass {
    /**
     * @var int The Categories ID
     */
    public $id;

    /* @var $name string */
    public $name;

    /**
     * @var int Categories order number
     */
    public $catOrder;

    /**
     * @var bool Tickets autoassigned in this Categories
     */
    public $autoAssign;

    /**
     * @var int The type of Categories (1 = Private, 0 = Public)
     */
    public $type;

    /**
     * @var int The Categories's usage (0 = Tickets and Events, 1 = Tickets, 2 = Events)
     */
    public $usage;

    /**
     * @var string
     */
    public $backgroundColor;

    /**
     * @var string
     */
    public $foregroundColor;

    /**
     * @var bool
     */
    public $displayBorder;

    /**
     * @var int The default Tickets priority
     */
    public $priority;

    /**
     * @var int|null The manager for the Categories, if applicable
     */
    public $manager;

    /**
     * @var bool Indication if the user has access to the Categories
     */
    public $accessible;

    /**
     * @var string
     */
    public $description;

    /**
     * @var int
     */
    public $numberOfTickets;

    /**
     * @var int
     */
    public $categoryGroupId;
}