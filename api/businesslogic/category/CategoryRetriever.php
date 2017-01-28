<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 1/16/17
 * Time: 10:10 PM
 */

namespace BusinessLogic\Category;

use DataAccess\CategoryGateway;

class CategoryRetriever {
    static function get_all_categories($hesk_settings) {
        require_once(__DIR__ . '/../../dao/category/CategoryGateway.php');

        return CategoryGateway::getAllCategories($hesk_settings);
    }
}