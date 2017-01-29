<?php

namespace BusinessLogic\Category;

use BusinessLogic\Security\UserContext;
use DataAccess\CategoryGateway;

class CategoryRetriever {
    /**
     * @var CategoryGateway
     */
    private $categoryGateway;

    function __construct($categoryGateway) {
        $this->categoryGateway = $categoryGateway;
    }

    /**
     * @param $heskSettings array
     * @param $userContext UserContext
     * @return array
     */
    function getAllCategories($heskSettings, $userContext) {
        $categories = $this->categoryGateway->getAllCategories($heskSettings);

        foreach ($categories as $category) {
            $category->accessible = $userContext->admin ||
                in_array($category->id, $userContext->categories);
        }

        return $categories;
    }
}