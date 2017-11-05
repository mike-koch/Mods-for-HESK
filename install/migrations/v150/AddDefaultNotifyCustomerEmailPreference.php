<?php

namespace v150;


class AddDefaultNotifyCustomerEmailPreference extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `default_notify_customer_email` ENUM ('0', '1') NOT NULL DEFAULT '1'");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `default_notify_customer_email`");
    }
}