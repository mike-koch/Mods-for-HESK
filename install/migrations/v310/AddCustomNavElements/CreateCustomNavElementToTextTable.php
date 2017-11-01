<?php

namespace v310\AddCustomNavElements;


class CreateCustomNavElementToTextTable extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_nav_element_to_text`
            (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
             nav_element_id INT NOT NULL,
             language VARCHAR(200) NOT NULL,
             text VARCHAR(200) NOT NULL,
             subtext VARCHAR(200))");
    }

    function down($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_nav_element_to_text`");
    }
}