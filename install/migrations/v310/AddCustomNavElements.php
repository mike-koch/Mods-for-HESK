<?php

namespace v310;


class AddCustomNavElements extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_nav_element` 
            (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
             image_url TEXT,
             font_icon VARCHAR(200),
             place INT NOT NULL,
             url VARCHAR(500) NOT NULL,
             sort INT NOT NULL)");
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_nav_element_to_text`
            (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
             nav_element_id INT NOT NULL,
             language VARCHAR(200) NOT NULL,
             text VARCHAR(200) NOT NULL,
             subtext VARCHAR(200))");
    }

    function down($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_nav_element`");
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_nav_element_to_text`");
    }
}