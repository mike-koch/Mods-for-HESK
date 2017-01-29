<?php

namespace BusinessObjects;

class Category {
    /**
     * @var int The category ID
     */
    public $id;

    /**
     * @var int Category order number
     */
    public $catOrder;

    /**
     * @var bool Tickets autoassigned in this category
     */
    public $autoAssign;

    /**
     * @var int The type of category (1 = Private, 2 = Public)
     */
    public $type;

    /**
     * @var int The category's usage (0 = Tickets and Events, 1 = Tickets, 2 = Events)
     */
    public $usage;

    /**
     * @var string? The color of the category
     */
    public $color;

    /**
     * @var int The default ticket priority
     */
    public $priority;

    /**
     * @var int|null The manager for the category, if applicable
     */
    public $manager;

    /**
     * @var bool Indication if the user has access to the category
     */
    public $accessible;
}