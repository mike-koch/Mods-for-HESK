<?php

function get_ticket_for_id($hesk_settings, $user, $id = NULL) {
    $sql = "SELECT `tickets`.* FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` AS `tickets` ";
    $sql .= "INNER JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` AS `users` ON `users`.`id` = " . intval($user['id']) . " ";
    $used_where_clause = false;
    if ($id != NULL) {
        $used_where_clause = true;
        $sql .= "WHERE `tickets`.`id` = " . intval($id);
    }

    if (!$user['isadmin']) {
        $clause = $used_where_clause ? ' AND ' : ' WHERE ';
        $used_where_clause = true;

        $sql .= $clause . ' `category` IN (' . $user['categories'] . ')';
        $sql .= " AND ((`heskprivileges` LIKE '%can_view_tickets%' AND `owner` = " . intval($user['id']) . ")";
        $sql .= " OR (`heskprivileges` LIKE '%can_view_unassigned%' AND `owner` = 0)";
        $sql .= " OR (`heskprivileges` LIKE '%can_view_ass_others%' AND `owner` <> " . intval($user['id']) . "))";
    }

    $response = hesk_dbQuery($sql);

    if (hesk_dbNumRows($response) == 0) {
        return NULL;
    }

    $results = build_results($response);

    return $id == NULL ? $results : $results[0];
}

function build_results($response) {
    $results = array();
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
        $row['overdue_email_sent'] = $row['overdue_email_sent'] == true;


        $results[] = $row;
    }

    return $results;
}

function convert_to_int($item) {
    return $item != NULL ? intval($item) : NULL;
}