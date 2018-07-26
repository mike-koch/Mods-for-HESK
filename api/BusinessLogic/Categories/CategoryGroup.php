<?php

namespace BusinessLogic\Categories;


class CategoryGroup extends \BaseClass {
    /* @var $id int|null */
    public $id;

    /* @var $names string[] */
    public $names;

    /* @var $parentId int|null */
    public $parentId;

    /* @var $numberOfCategories int */
    public $numberOfCategories;

    /* @var $sort int|null */
    public $sort;
}