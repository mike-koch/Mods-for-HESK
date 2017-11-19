<?php

namespace v330\ServiceMessagesImprovements;


class AddLanguageColumnToServiceMessages extends \AbstractUpdatableMigration {

    function innerUp($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages`
            ADD COLUMN `mfh_language` VARCHAR(255) NOT NULL DEFAULT 'ALL'");
    }

    function innerDown($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages`
            DROP COLUMN `mfh_language`");
    }
}