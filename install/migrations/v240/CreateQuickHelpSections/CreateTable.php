<?php

namespace v240\CreateQuickHelpSections;


class CreateTable extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` (
          `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `location` VARCHAR(100) NOT NULL,
          `show` ENUM('0','1') NOT NULL
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` (`location`, `show`)
      VALUES ('create_ticket', '1')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` (`location`, `show`)
      VALUES ('view_ticket_form', '1')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` (`location`, `show`)
      VALUES ('view_ticket', '1')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` (`location`, `show`)
      VALUES ('knowledgebase', '1')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` (`location`, `show`)
      VALUES ('staff_create_ticket', '1')");
    }

    function down($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections`");
    }
}