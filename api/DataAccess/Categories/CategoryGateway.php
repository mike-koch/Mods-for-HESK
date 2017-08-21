<?php

namespace DataAccess\Categories;

use BusinessLogic\Categories\Category;
use DataAccess\CommonDao;
use DataAccess\Logging\LoggingGateway;
use Exception;

class CategoryGateway extends CommonDao {
    /**
     * @param $hesk_settings
     * @return Category[]
     */
    function getAllCategories($hesk_settings) {
        $this->init();

        $sql = 'SELECT * FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'categories`';

        $response = hesk_dbQuery($sql);

        $results = array();
        while ($row = hesk_dbFetchAssoc($response)) {
            $category = new Category();

            $category->id = intval($row['id']);
            $category->name = $row['name'];
            $category->catOrder = intval($row['cat_order']);
            $category->autoAssign = $row['autoassign'] == 1;
            $category->type = intval($row['type']);
            $category->usage = intval($row['usage']);
            $category->backgroundColor = $row['background_color'];
            $category->foregroundColor = $row['foreground_color'];
            $category->displayBorder = $row['display_border_outline'] === '1';
            $category->priority = intval($row['priority']);
            $category->manager = intval($row['manager']) == 0 ? NULL : intval($row['manager']);
            $category->description = $row['mfh_description'];
            $results[$category->id] = $category;
        }

        $this->close();

        return $results;
    }

    /**
     * @param $category Category
     * @param $heskSettings array
     * @return int The ID of the newly created category
     */
    function createCategory($category, $heskSettings) {
        $this->init();

        $newOrderRs = hesk_dbQuery("SELECT `cat_order` FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "categories` ORDER BY `cat_order` DESC LIMIT 1");
        $newOrder = hesk_dbFetchAssoc($newOrderRs);

        $sql = "INSERT INTO `" . hesk_dbEscape($heskSettings['db_pfix']) . "categories` 
            (`name`, `cat_order`, `autoassign`, `type`, `priority`, `manager`, `background_color`, `usage`, 
                `foreground_color`, `display_border_outline`, `mfh_description`)
            VALUES ('" . hesk_dbEscape($category->name) . "', " . intval($newOrder['cat_order']) . ",
                '" . ($category->autoAssign ? 1 : 0) . "', '" . intval($category->type) . "',
                '" . intval($category->priority) . "', " . ($category->manager === null ? 0 : intval($category->manager)) . ",
                '" . hesk_dbEscape($category->backgroundColor)  . "', " . intval($category->usage) . ",
                '" . hesk_dbEscape($category->foregroundColor) . "', '" . ($category->displayBorder ? 1 : 0) . "',
                '" . hesk_dbEscape($category->description) . "')";

        hesk_dbQuery($sql);

        $id = hesk_dbInsertID();

        $this->close();

        return $id;
    }

    /**
     * @param $category Category
     * @param $heskSettings array
     */
    function updateCategory($category, $heskSettings) {
        $this->init();

        $sql = "UPDATE `" . hesk_dbEscape($heskSettings['db_pfix']) . "categories` SET 
            `name` = '" . hesk_dbEscape($category->name) . "',
            `cat_order` = " . intval($category->catOrder) . ",
            `autoassign` = '" . ($category->autoAssign ? 1 : 0) . "',
            `type` = '" . intval($category->type) . "', 
            `priority` = '" . intval($category->priority) . "', 
            `manager` = " . ($category->manager === null ? 0 : intval($category->manager)) . ", 
            `background_color` = '" . hesk_dbEscape($category->backgroundColor)  . "', 
            `usage` = " . intval($category->usage) . ", 
            `foreground_color` = '" . hesk_dbEscape($category->foregroundColor) . "', 
            `display_border_outline` = '" . ($category->displayBorder ? 1 : 0) . "', 
            `mfh_description` = '" . hesk_dbEscape($category->description) . "'
            WHERE `id` = " . intval($category->id);

        hesk_dbQuery($sql);

        $this->close();
    }

    function resortAllCategories($heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT `id` FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "categories`
            ORDER BY `cat_order` ASC");

        $sortValue = 10;
        while ($row = hesk_dbFetchAssoc($rs)) {
            hesk_dbQuery("UPDATE `" . hesk_dbEscape($heskSettings['db_pfix']) . "categories`
                SET `cat_order` = " . intval($sortValue) . "
                WHERE `id` = " . intval($row['id']));

            $sortValue += 10;
        }

        $this->close();
    }
}