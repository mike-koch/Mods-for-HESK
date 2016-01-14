<?php

function get_events($start, $end, $hesk_settings) {

    $sql = "SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event` WHERE `start` >= " . intval($start)
        . " AND `end` <= " . intval($end);

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