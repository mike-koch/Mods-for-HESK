<?php

function get_ticket_for_id($hesk_settings, $id = NULL) {
    $sql = "SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ";
    if ($id != NULL) {
        $sql .= "WHERE `id` = ".intval($id);
    }

    $response = hesk_dbQuery($sql);

    if (hesk_dbNumRows($response) == 0) {
        return NULL;
    }

    $results = [];
    while ($row = hesk_dbFetchAssoc($response)) {
        $row['id'] = intval($row['id']);
        $row['category'] = intval($row['category']);
        $row['priority'] = intval($row['priority']);
        $row['status'] = intval($row['status']);
        $row['openedby'] = intval($row['openedby']);
        $row['lastreplier'] = intval($row['lastreplier']);
        $row['replierid'] = intval($row['replierid']);
        $row['archive'] = $row['archive'] == true;
        $row['locked'] = $row['locked'] == true;
        $row['html'] = $row['html'] == true;
        $row['screen_resolution_height'] = $row['screen_resolution_height'] != NULL
            ? intval($row['screen_resolution_height'])
            : NULL;
        $row['screen_resolution_width'] = $row['screen_resolution_width'] != NULL
            ? intval($row['screen_resolution_width'])
            : NULL;

        $results[] = $row;
    }

    return $id == NULL ? $results : $results[0];
}