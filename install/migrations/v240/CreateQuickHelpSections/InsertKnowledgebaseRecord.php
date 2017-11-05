<?php

namespace v240\CreateQuickHelpSections;


class InsertKnowledgebaseRecord extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` (`location`, `show`)
      VALUES ('knowledgebase', '1')");
    }

    function down($hesk_settings) {
        $this->executeQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` 
            WHERE `location` = 'knowledgebase'");
    }
}