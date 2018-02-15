<?php

namespace v330;


class AddParentIdToCategory extends \AbstractUpdatableMigration {

    function innerUp($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` 
            ADD COLUMN `mfh_parent_id` INT");
    }

    function innerDown($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` 
            DROP COLUMN `mfh_parent_id`");
    }
}