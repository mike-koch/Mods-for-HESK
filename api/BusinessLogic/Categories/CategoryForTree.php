<?php

namespace BusinessLogic\Categories;


class CategoryForTree {
    /* @var $id int */
    public $id;

    /* @var $text string */
    public $text;

    /* @var $parent int|string */
    public $parent;

    /* @var $data CategoryForTreeData */
    public $data;
}