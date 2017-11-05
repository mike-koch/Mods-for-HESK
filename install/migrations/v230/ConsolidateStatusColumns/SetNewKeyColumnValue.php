<?php

namespace v230\ConsolidateStatusColumns;


class SetNewKeyColumnValue extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` SET `Key` = `ShortNameContentKey`");
    }

    function down($hesk_settings) {
        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` SET `TicketViewContentKey` = `Key`, `ShortNameContentKey` = `Key`");
    }
}