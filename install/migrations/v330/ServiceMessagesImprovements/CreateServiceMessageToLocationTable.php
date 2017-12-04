<?php

namespace v330\ServiceMessagesImprovements;


class CreateServiceMessageToLocationTable extends \AbstractUpdatableMigration {

    function innerUp($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mfh_service_message_to_location`
            (`service_message_id` INT NOT NULL, `location` VARCHAR(100) NOT NULL)");
    }

    function innerDown($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mfh_service_message_to_location`");
    }
}