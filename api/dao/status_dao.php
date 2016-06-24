<?php

function get_status($hesk_settings, $id = NULL) {
    $sql = "SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ";
    if ($id != NULL) {
        $sql .= "WHERE `ID` = ".intval($id);
    }

    $response = hesk_dbQuery($sql);

    if (hesk_dbNumRows($response) == 0) {
        return NULL;
    }

    $results = array();
    while ($row = hesk_dbFetchAssoc($response)) {
        $row['id'] = intval($row['ID']);
        unset($row['ID']);
        $row['sort'] = intval($row['sort']);
        foreach ($row as $key => $value) {
            if ($key != 'id') {
                $lowercase_key = lcfirst($key);
                $row[$lowercase_key] = $row[$key];
                unset($row[$key]);
            }
            if ($key == 'id' || $lowercase_key == 'closable'
                || $lowercase_key == 'key' || $lowercase_key == 'sort'
                || $lowercase_key == 'textColor') {
                continue;
            }
            $row[$lowercase_key] = $row[$lowercase_key] == true;
        }

        $language_sql = "SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "text_to_status_xref` "
            . "WHERE `status_id` = ".intval($row['id']);

        $language_rs = hesk_dbQuery($language_sql);
        if (hesk_dbNumRows($language_rs) > 0) {
            $row['key'] = NULL;
            $row['keys'] = array();
        }
        while ($language_row = hesk_dbFetchAssoc($language_rs)) {
            unset($language_row['id']);
            unset($language_row['status_id']);
            $row['keys'][] = $language_row;
        }

        $results[] = $row;
    }

    return $id == NULL ? $results : $results[0];
}