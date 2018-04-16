<?php

namespace v340;


class CreateCategoryGroupsI18nTable extends \AbstractUpdatableMigration {

    function innerUp($hesk_settings) {
        hesk_dbQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mfh_category_groups_i18n` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `category_group_id` INT NOT NULL,
                          `language` VARCHAR(100) NOT NULL, `text` VARCHAR(255) NOT NULL)
          ENGINE = MyISAM
          COLLATE = utf8_unicode_ci");
    }

    function innerDown($hesk_settings) {
        hesk_dbQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mfh_category_groups_i18n`");
    }
}