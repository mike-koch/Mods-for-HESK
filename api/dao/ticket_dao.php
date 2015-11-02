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
        $results[] = $row;
    }

    return $id == NULL ? $results : $results[0];
}