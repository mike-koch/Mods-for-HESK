<?php

namespace Controllers\Categories;

use BusinessLogic\Categories\CategoryRetriever;

class CategoryController {
    function get($id) {
        $categories = self::getAllCategories();
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