<?php

function get_events($start, $end, $hesk_settings) {

    $sql = "SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event` WHERE `start` >= FROM_UNIXTIME(" . intval($start)
        . " / 1000) AND `end` <= FROM_UNIXTIME(" . intval($end) . " / 1000)";

    $rs = hesk_dbQuery($sql);

    $events = [];
    while ($row = hesk_dbFetchAssoc($rs)) {
        $event['id'] = intval($row['id']);
        $event['start'] = intval($row['start']);
        $event['end'] = intval($row['end']);
        $event['all_day'] = $row['all_day'] ? true : false;
        $event['name'] = $row['name'];
        $event['location'] = $row['location'];
        $event['comments'] = $row['comments'];
        $event['create_ticket_date'] = $row['create_ticket_date'] != null ? intval($row['create_ticket_date']) : null;
        $event['create_ticket_assign_to'] = $row['create_ticket_assign_to'] != null ? intval($row['create_ticket_assign_to']) : null;
        $events[] = $event;
    }

    return $events;
}

/**
 * @param $event. All times must be in milliseconds since epoch time.
 * @param $hesk_settings
 * @return int the event id
 */
function create_event($event, $hesk_settings) {

    $event['start'] = date('Y-m-d H:i:s', strtotime($event['start']));
    $event['end'] = date('Y-m-d H:i:s', strtotime($event['end']));
    $event['create_ticket_date'] = date('Y-m-d H:i:s', strtotime($event['create_ticket_date']));
    $event['all_day'] = $event['all_day'] ? 1 : 0;
    $event['assign_to'] = $event['assign_to'] != null ? intval($event['assign_to']) : 'NULL';

    $sql = "INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event` (`start`, `end`, `all_day`,
    `name`, `location`, `comments`, `create_ticket_date`, `create_ticket_assign_to`) VALUES (
    '" . hesk_dbEscape($event['start']) . "', '" . hesk_dbEscape($event['end']) . "', '" . hesk_dbEscape($event['all_day']) . "',
    '" . hesk_dbEscape($event['title']) . "', '" . hesk_dbEscape($event['location']) . "', '" . hesk_dbEscape($event['comments']) . "',
    '" . hesk_dbEscape($event['create_ticket_date']) . "', " . $event['assign_to'] . ")";

    hesk_dbQuery($sql);
    return hesk_dbInsertID();
}
