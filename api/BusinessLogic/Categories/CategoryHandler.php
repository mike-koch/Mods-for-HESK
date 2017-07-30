<?php

namespace BusinessLogic\Categories;


use DataAccess\Categories\CategoryGateway;

class CategoryHandler {
    /* @var $categoryGateway CategoryGateway */
    private $categoryGateway;

    function __construct($categoryGateway) {
        $this->categoryGateway = $categoryGateway;
    }

    /**
     * @param $category Category
     * @param $heskSettings array
     */
    function createCategory($category, $heskSettings) {
        $this->categoryGateway->createCategory($category, $heskSettings);
    }

    function editCategory($category, $heskSettings) {


    }
}