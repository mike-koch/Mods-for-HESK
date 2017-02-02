<?php

namespace Controllers\Category;

use BusinessLogic\Category\CategoryRetriever;

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
        $categoryRetriever = $applicationContext->get['CategoryRetriever'];

        return $categoryRetriever->getAllCategories($hesk_settings, $userContext);
    }
}