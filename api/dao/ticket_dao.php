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

    $results = build_results($response);

    return $id == NULL ? $results : $results[0];
}

function build_results($response) {
    $results = [];
    while ($row = hesk_dbFetchAssoc($response)) {
        $row['id'] = intval($row['id']);
        $row['category'] = intval($row['category']);
        $row['priority'] = intval($row['priority']);
        $row['status'] = intval($row['status']);
        $row['replierid'] = intval($row['replierid']);
        $row['archive'] = $row['archive'] == true;
        $row['locked'] = $row['locked'] == true;
        $row['html'] = $row['html'] == true;
        $row['screen_resolution_height'] = convert_to_int($row['screen_resolution_height']);
        $row['screen_resolution_width'] = convert_to_int($row['screen_resolution_width']);
        $row['owner'] = convert_to_int($row['owner']);
        $row['parent'] = convert_to_int($row['parent']);


        $results[] = $row;
    }

    return $results;
}

function convert_to_int($item) {
    return $item != NULL ? intval($item) : NULL;
}