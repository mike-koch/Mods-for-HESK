<?php

function get_category($hesk_settings, $id = NULL) {
    $sql = "SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ";
    if ($id != NULL) {
        $sql .= "WHERE `id` = ".intval($id);
    }

    $response = hesk_dbQuery($sql);

    if (hesk_dbNumRows($response) == 0) {
        return NULL;
    }

    $results = array();
    while ($row = hesk_dbFetchAssoc($response)) {
        $row['id'] = intval($row['id']);
        $row['displayOrder'] = intval($row['cat_order']);
        unset($row['cat_order']);
        $row['autoassign'] = $row['autoassign'] == 1;
        $row['type'] = intval($row['type']);
        $row['priority'] = intval($row['priority']);
        $row['manager'] = intval($row['manager']) == 0 ? NULL : intval($row['manager']);
        $results[] = $row;
    }

    return $id == NULL ? $results : $results[0];
}