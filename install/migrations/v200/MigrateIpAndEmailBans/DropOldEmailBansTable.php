<?php

namespace v200\MigrateIpAndEmailBans;


class DropOldEmailBansTable extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_emails`");
    }

    function down($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_emails` (
            ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
            Email VARCHAR(100) NOT NULL);");
    }
}