<?php

namespace v211\FixStageTicketsTable;

class FixStageTicketsTable extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets`
					CHANGE `email` `email` VARCHAR( 1000 ) NOT NULL DEFAULT '',
					CHANGE `ip` `ip` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
					ADD `firstreply` TIMESTAMP NULL DEFAULT NULL AFTER `lastchange`,
					ADD `closedat` TIMESTAMP NULL DEFAULT NULL AFTER `firstreply`,
					ADD `articles` VARCHAR(255) NULL DEFAULT NULL AFTER `closedat`,
					ADD `openedby` MEDIUMINT(8) DEFAULT '0' AFTER `status`,
					ADD `firstreplyby` SMALLINT(5) UNSIGNED NULL DEFAULT NULL AFTER `openedby`,
					ADD `closedby` MEDIUMINT(8) NULL DEFAULT NULL AFTER `firstreplyby`,
					ADD `replies` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `closedby`,
					ADD `staffreplies` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `replies`,
					ADD INDEX ( `openedby` , `firstreplyby` , `closedby` ),
					ADD INDEX(`dt`)");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets`
                    CHANGE `email` `email` VARCHAR(255) NOT NULL DEFAULT '',
                    CHANGE `ip` `ip` VARCHAR(46) NOT NULL DEFAULT '',
                    DROP `firstreply`,
                    DROP `closedat`,
                    DROP `articles`,
                    DROP `firstreplyby`,
                    DROP `closedby`,
                    DROP `replies`,
                    DROP `staffreplies`");
    }
}