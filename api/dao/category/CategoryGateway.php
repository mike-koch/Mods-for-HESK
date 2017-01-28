<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 1/16/17
 * Time: 10:06 PM
 */

namespace DataAccess;

use BusinessObjects\Category;
use Exception;

class CategoryGateway {
    static function getAllCategories($hesk_settings) {
        require_once(__DIR__ . '/../../businesslogic/category/Category.php');

        if (!function_exists('hesk_dbConnect')) {
            throw new Exception('Database not loaded!');
        }
        hesk_dbConnect();

        $sql = 'SELECT * FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'categories`';

        $response = hesk_dbQuery($sql);

        $results = array();
        while ($row = hesk_dbFetchAssoc($response)) {
            $category = new Category();

            $category->id = intval($row['id']);
            $category->catOrder = intval($row['cat_order']);
            $category->autoAssign = $row['autoassign'] == 1;
            $category->type = intval($row['type']);
            $category->usage = intval($row['usage']);
            $category->color = $row['color'];
            $category->priority = intval($row['priority']);
            $category->manager = intval($row['manager']) == 0 ? NULL : intval($row['manager']);
            $results[$category->id] = $category;
        }

        hesk_dbClose();

        return $results;
    }
}