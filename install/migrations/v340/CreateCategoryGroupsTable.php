<?php

namespace v340;


class CreateCategoryGroupsTable extends \AbstractUpdatableMigration {

    function innerUp($hesk_settings) {
        hesk_dbQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix'])  ."mfh_category_groups` (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
            `parent_id` INT,
            `sort` INT NOT NULL)
          ENGINE = MyISAM
          COLLATE = utf8_unicode_ci");
    }

    function innerDown($hesk_settings) {
        hesk_dbQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix'])  ."mfh_category_groups`");
    }
}