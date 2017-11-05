<?php

namespace v300;


class MigrateHeskCustomStatuses extends \AbstractMigration {

    function up($hesk_settings) {
        $hesk_statuses = $this->executeQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_statuses` ORDER BY `order`");

        $next_status_id_rs = $this->executeQuery("SELECT MAX(`ID`) AS `last_id` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses`");
        $next_status_id_row = hesk_dbFetchAssoc($next_status_id_rs);
        $next_status_id = intval($next_status_id_row['last_id']) + 1;

        $next_sort_rs = $this->executeQuery("SELECT MAX(`sort`) AS `last_sort` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses`");
        $next_sort_row = hesk_dbFetchAssoc($next_sort_rs);
        $next_sort = intval($next_sort_row['last_sort']) + 10;

        while ($row = hesk_dbFetchAssoc($hesk_statuses)) {
            $closable = $row['can_customers_change'] == '1' ? 'yes' : 'sonly';

            $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` (`ID`,
                `TextColor`,
                `IsNewTicketStatus`,
                `IsClosed`,
                `IsClosedByClient`,
                `IsCustomerReplyStatus`,
                `IsStaffClosedOption`,
                `IsStaffReopenedStatus`,
                `IsDefaultStaffReplyStatus`,
                `LockedTicketStatus`,
                `IsAutocloseOption`,
                `Closable`,
                `Key`,
                `sort`)
                VALUES (" . $next_status_id . ",
                '#" . $row['color'] . "',
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                '" . $closable . "',
                'STORED IN XREF TABLE',
                " . $next_sort . ")");

            $languages = json_decode($row['name']);
            foreach ($languages as $language => $text) {
                $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "text_to_status_xref` (`language`, `text`, `status_id`)
            VALUES ('" . $language . "', '" . $text . "', " . $next_status_id . ")");
            }

            // Increment the next ID and sort
            $next_status_id++;
            $next_sort += 10;
        }
    }

    function down($hesk_settings) {
    }
}