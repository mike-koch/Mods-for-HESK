<?php

namespace vv202010;


class AddAssignedByToStageTickets extends \AbstractUpdatableMigration {

    function innerUp($hesk_settings) {
        hesk_dbQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` ADD `assignedby` MEDIUMINT(8) NULL DEFAULT NULL AFTER `owner`");
    }

    function innerDown($hesk_settings) {
        hesk_dbQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` DROP `assignedby`");
    }
}