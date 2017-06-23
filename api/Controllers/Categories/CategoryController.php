<?php

namespace Controllers\Categories;

use BusinessLogic\Categories\CategoryRetriever;
use BusinessLogic\Exceptions\ApiFriendlyException;

class CategoryController {
    function get($id) {
        $categories = self::getAllCategories();

        if (!isset($categories[$id])) {
            throw new ApiFriendlyException("Category {$id} not found!", "Category Not Found", 404);
        }

        output($categories[$id]);
    }

    static function printAllCategories() {
        output(self::getAllCategories());
    }

    private static function getAllCategories() {
        global $hesk_settings, $applicationContext, $userContext;

        /* @var $categoryRetriever CategoryRetriever */
        $categoryRetriever = $applicationContext->get[CategoryRetriever::class];

        return $categoryRetriever->getAllCategories($hesk_settings, $userContext);
    }
}