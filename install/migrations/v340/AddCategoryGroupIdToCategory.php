<?php

namespace v340;


class AddCategoryGroupIdToCategory extends \AbstractUpdatableMigration {

    function innerUp($hesk_settings) {
        hesk_dbQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ADD `mfh_category_group_id` INT NULL");
    }

    function innerDown($hesk_settings) {
        hesk_dbQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` DROP `mfh_category_group_id`");
    }
}