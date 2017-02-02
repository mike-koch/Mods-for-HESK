<?php

function get_ticket_template($hesk_settings, $id = NULL) {
    $sql = "SELECT `id`, `message`, `title`, `tpl_order` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "ticket_templates` ";
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
        $row['displayOrder'] = intval($row['tpl_order']);
        unset($row['tpl_order']);
        $row['title'] = hesk_html_entity_decode($row['title']);
        $row['message'] = hesk_html_entity_decode($row['message']);
        $results[] = $row;
    }

    return $id == NULL ? $results : $results[0];
}