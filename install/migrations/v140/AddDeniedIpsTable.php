<?php

namespace v140;


class AddDeniedIpsTable extends \AbstractMigration {
    function up($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_ips` (
	  `ID` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	  `RangeStart` VARCHAR(100) NOT NULL,
	  `RangeEnd` VARCHAR(100) NOT NULL)");
    }

    function down($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_ips`");
    }
}