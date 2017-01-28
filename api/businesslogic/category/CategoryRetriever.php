<?php

namespace BusinessLogic\Category;

use DataAccess\CategoryGateway;

class CategoryRetriever {
    /**
     * @var CategoryGateway
     */
    private $categoryGateway;

    function __construct($categoryGateway) {
        $this->categoryGateway = $categoryGateway;
    }

    function getAllCategories($hesk_settings) {
        return $this->categoryGateway->getAllCategories($hesk_settings);
    }
}