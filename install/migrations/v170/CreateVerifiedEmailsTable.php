<?php

namespace v170;

class CreateVerifiedEmailsTable extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "verified_emails` (`Email` VARCHAR(255) NOT NULL)");
    }

    function down($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "verified_emails`");
    }

}