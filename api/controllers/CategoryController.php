<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 1/16/17
 * Time: 10:12 PM
 */

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
        global $hesk_settings;
        require_once(__DIR__ . '/../businesslogic/category/CategoryRetriever.php');

        return CategoryRetriever::get_all_categories($hesk_settings);
    }
}