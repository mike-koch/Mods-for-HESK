<?php

namespace v200;


class AddMissingKeyToTickets extends \AbstractMigration {

    function up($hesk_settings) {
        $keyRs = $this->executeQuery("SHOW KEYS FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE Key_name='statuses'");
        if (hesk_dbNumRows($keyRs) == 0) {
            //-- Add the key
            $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD KEY `statuses` (`status`)");
        }
    }

    function down($hesk_settings) {
        // HESK uses this key, so don't drop it.
    }
}