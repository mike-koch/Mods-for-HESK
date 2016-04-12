<?php

function get_events($start, $end, $hesk_settings, $staff = true) {
    $sql = "SELECT `events`.*, `categories`.`name` AS `category_name`, `categories`.`color` AS `category_color` ";

    if ($staff) {
        $sql .= ",`reminders`.`amount` AS `reminder_value`, `reminders`.`unit` AS `reminder_unit` ";
    }

    $sql .= "FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event` AS `events`
        INNER JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` AS `categories`
            ON `events`.`category` = `categories`.`id` ";

    if ($staff) {
        $sql .= "LEFT JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event_reminder` AS `reminders` ON
        `reminders`.`user_id` = " . intval($_SESSION['id']) . " AND `reminders`.`event_id` = `events`.`id`";
    }
    $sql .= "WHERE `start` >= FROM_UNIXTIME(" . hesk_dbEscape($start)
        . " / 1000) AND `end` <= FROM_UNIXTIME(" . hesk_dbEscape($end) . " / 1000) AND `categories`.`usage` <> 1";

    if (!$staff) {
        $sql .= " AND `categories`.`type` = '0'";
    }

    $rs = hesk_dbQuery($sql);

    $events = [];
    while ($row = hesk_dbFetchAssoc($rs)) {
        $event['type'] = 'CALENDAR';
        $event['id'] = intval($row['id']);
        $event['startTime'] = $row['start'];
        $event['endTime'] = $row['end'];
        $event['allDay'] = $row['all_day'] ? true : false;
        $event['title'] = $row['name'];
        $event['location'] = $row['location'];
        $event['comments'] = $row['comments'];
        $event['categoryId'] = $row['category'];
        $event['categoryName'] = $row['category_name'];
        $event['categoryColor'] = $row['category_color'];

        if ($staff) {
            $event['reminderValue'] = $row['reminder_value'];
            $event['reminderUnits'] = $row['reminder_unit'];
        }

        $events[] = $event;
    }

    if ($staff) {
        $sql = "SELECT `trackid`, `subject`, `due_date`, `category`, `categories`.`name` AS `category_name`, `categories`.`color` AS `category_color`,
          CASE WHEN `due_date` < CURDATE() THEN 1 ELSE 0 END AS `overdue`
        FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` AS `tickets`
        INNER JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` AS `categories`
            ON `categories`.`id` = `tickets`.`category`
        WHERE `due_date` >= FROM_UNIXTIME(" . intval($start) . " / 1000)
        AND `due_date` <= FROM_UNIXTIME(" . intval($end) . " / 1000)
        AND `status` IN (SELECT `id` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` WHERE `IsClosed` = 0) ";

        $rs = hesk_dbQuery($sql);
        while ($row = hesk_dbFetchAssoc($rs)) {
            $event['type'] = 'TICKET';
            $event['trackingId'] = $row['trackid'];
            $event['title'] = '[' . $row['trackid'] . '] ' . $row['subject'];
            $event['startTime'] = $row['due_date'];
            $event['url'] = $hesk_settings['hesk_url'] . '/' . $hesk_settings['admin_dir'] . '/admin_ticket.php?track=' . $event['trackingId'];
            $event['categoryId'] = $row['category'];
            $event['categoryName'] = $row['category_name'];
            $event['categoryColor'] = $row['overdue'] ? '#dd0000' : $row['category_color'];
            $events[] = $event;
        }
    }

    return $events;
}

function create_event($event, $hesk_settings) {

    $event['start'] = date('Y-m-d H:i:s', strtotime($event['start']));
    $event['end'] = date('Y-m-d H:i:s', strtotime($event['end']));
    $event['all_day'] = $event['all_day'] ? 1 : 0;

    $sql = "INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event` (`start`, `end`, `all_day`,
    `name`, `location`, `comments`, `category`) VALUES (
    '" . hesk_dbEscape($event['start']) . "', '" . hesk_dbEscape($event['end']) . "', '" . hesk_dbEscape($event['all_day']) . "',
    '" . hesk_dbEscape($event['title']) . "', '" . hesk_dbEscape($event['location']) . "', '" . hesk_dbEscape($event['comments']) . "',
    " . intval($event['category']) . ")";

    hesk_dbQuery($sql);
    $event_id = hesk_dbInsertID();

    if ($event['reminder_amount'] != null) {
        $sql = "INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event_reminder` (`user_id`, `event_id`,
        `amount`, `unit`) VALUES (" . intval($event['reminder_user']) . ", " . intval($event_id) . ", " . intval($event['reminder_amount']) . ",
        " . intval($event['reminder_units']) . ")";

        hesk_dbQuery($sql);
    }

    return $event_id;
}

function update_event($event, $hesk_settings) {
    $event['start'] = date('Y-m-d H:i:s', strtotime($event['start']));
    $event['end'] = date('Y-m-d H:i:s', strtotime($event['end']));
    if ($event['create_ticket_date'] != null) {
        $event['create_ticket_date'] = date('Y-m-d H:i:s', strtotime($event['create_ticket_date']));
    }
    $event['all_day'] = $event['all_day'] ? 1 : 0;
    $event['assign_to'] = $event['assign_to'] != null ? intval($event['assign_to']) : 'NULL';

    $sql = "UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event` SET `start` = '" . hesk_dbEscape($event['start'])
        . "', `end` = '" . hesk_dbEscape($event['end']) . "', `all_day` = '" . hesk_dbEscape($event['all_day']) . "', `name` = '"
        . hesk_dbEscape($event['title']) . "', `location` = '" . hesk_dbEscape($event['location']) . "', `comments` = '"
        . hesk_dbEscape($event['comments']) . "', `category` = " . intval($event['category']) . " WHERE `id` = " . intval($event['id']);

    if ($event['reminder_amount'] != null) {
        $delete_sql = "DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event_reminder` WHERE `event_id` = " . intval($event['id'])
            . " AND `user_id` = " . intval($event['reminder_user']);
        hesk_dbQuery($delete_sql);
        $insert_sql = "INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event_reminder` (`user_id`, `event_id`,
        `amount`, `unit`) VALUES (" . intval($event['reminder_user']) . ", " . intval($event['id']) . ", " . intval($event['reminder_amount']) . ",
        " . intval($event['reminder_units']) . ")";
        hesk_dbQuery($insert_sql);
    }

    hesk_dbQuery($sql);
}

function delete_event($id, $hesk_settings) {
    $sql = "DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event` WHERE `id` = " . intval($id);

    hesk_dbQuery($sql);
}

function update_ticket_due_date($ticket, $hesk_settings) {
    $due_date = 'NULL';
    if ($ticket['due_date'] != NULL) {
        $due_date = "'" . date('Y-m-d H:i:s', strtotime($ticket['due_date'])) . "'";
    }
    $sql = "UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `due_date` = {$due_date}
        WHERE `trackid` = '" . hesk_dbEscape($ticket['trackid']) . "'";

    hesk_dbQuery($sql);
}