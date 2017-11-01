<?php

namespace v310\AddCustomNavElements;


class CreateCustomNavElementTable extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_nav_element` 
            (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
             image_url TEXT,
             font_icon VARCHAR(200),
             place INT NOT NULL,
             url VARCHAR(500) NOT NULL,
             sort INT NOT NULL)");
    }

    function down($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_nav_element`");
    }
}