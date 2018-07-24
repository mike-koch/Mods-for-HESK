<?php

namespace BusinessLogic\Categories;


class CategoryGroup extends \BaseClass {
    /* @var $id int */
    public $id;

    /* @var $names string[] */
    public $names;

    /* @var $parentId ?int */
    public $parentId;

    /* @var $numberOfCategories int */
    public $numberOfCategories;
}