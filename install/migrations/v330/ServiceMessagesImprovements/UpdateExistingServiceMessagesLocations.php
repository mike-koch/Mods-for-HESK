<?php

namespace v330\ServiceMessagesImprovements;

class UpdateExistingServiceMessagesLocations extends \AbstractUpdatableMigration {

    function innerUp($hesk_settings) {
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mfh_service_message_to_location` (`service_message_id`, `location`)
            SELECT `id`, 'CUSTOMER_HOME' FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages`");
    }

    function innerDown($hesk_settings) {
        $this->executeQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mfh_service_message_to_location`
            WHERE `service_message_id` IN (SELECT `id` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages`)");
    }
}