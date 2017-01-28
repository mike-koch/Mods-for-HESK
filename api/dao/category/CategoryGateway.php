<?php

namespace DataAccess;

use BusinessObjects\Category;
use Exception;

class CategoryGateway extends CommonDao {
    function getAllCategories($hesk_settings) {
        $this->init();

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

        $this->close();

        return $results;
    }
}