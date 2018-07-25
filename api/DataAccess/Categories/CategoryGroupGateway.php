<?php

namespace DataAccess\Categories;


use BusinessLogic\Categories\CategoryGroup;
use DataAccess\CommonDao;

class CategoryGroupGateway extends CommonDao {
    public function getAllCategoryGroups($heskSettings) {
        $this->init();

        $sql = "SELECT `cat_group`.*, `i18n`.`language`, `i18n`.`text`, COUNT(`cat`.`id`) AS `number_of_categories`
            FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "mfh_category_groups` `cat_group`
            INNER JOIN `" . hesk_dbEscape($heskSettings['db_pfix']) . "mfh_category_groups_i18n` `i18n`
                ON `cat_group`.`id` = `i18n`.`category_group_id`
            LEFT JOIN `" . hesk_dbEscape($heskSettings['db_pfix']) . "categories` `cat`
                ON `cat_group`.`id` = `cat`.`mfh_category_group_id`
            GROUP BY `cat_group`.`id`, `i18n`.`language`, `i18n`.`text`
            ORDER BY `cat_group`.`sort` ASC";

        // TODO Verify the query!
        return array();
    }

    public function createCategoryGroup($heskSettings, CategoryGroup $categoryGroup) {
        $this->init();

        $parentId = $categoryGroup->parentId === null ? "NULL" : intval($categoryGroup->parentId);
        $newOrderRs = hesk_dbQuery("SELECT `sort` FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "mfh_category_groups` ORDER BY `sort` DESC LIMIT 1");
        $newOrder = hesk_dbFetchAssoc($newOrderRs);

        $sql = "INSERT INTO `" . hesk_dbEscape($heskSettings['db_pfix']) . "mfh_category_groups` (`parent_id`, `sort`)
            VALUES (" . $parentId . ", " . intval($newOrder['sort']) . ")";
        hesk_dbQuery($sql);

        $id = hesk_dbInsertID();

        // i18n
        foreach ($categoryGroup->names as $language => $name) {
            $sql = "INSERT INTO `" . hesk_dbEscape($heskSettings['db_pfix']) . "mfh_category_groups_i18n` (`category_group_id`, `language`, `text`)
                VALUES (" . $id . ", '" . hesk_dbEscape($language) . "', '" . hesk_dbEscape($name) . "')";
            hesk_dbQuery($sql);
        }

        $this->close();

        $categoryGroup->id = $id;

        return $categoryGroup;
    }
}