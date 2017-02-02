<?php

function get_user($hesk_settings, $id = NULL) {
    $sql = "SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ";

    if ($id != NULL) {
        $sql .= "WHERE `id` = " . intval($id);
    }

    $response = hesk_dbQuery($sql);

    if (hesk_dbNumRows($response) == 0) {
        return NULL;
    }

    $results = array();
    while ($row = hesk_dbFetchAssoc($response)) {
        $row['id'] = intval($row['id']);
        $row['isadmin'] = get_boolean($row['isadmin']);
        $row['signature'] = hesk_html_entity_decode($row['signature']);
        $row['afterreply'] = intval($row['afterreply']);
        $row['autostart'] = get_boolean($row['autostart']);
        $row['notify_customer_new'] = get_boolean($row['notify_customer_new']);
        $row['notify_customer_reply'] = get_boolean($row['notify_customer_reply']);
        $row['show_suggested'] = get_boolean($row['show_suggested']);
        $row['notify_new_unassigned'] = get_boolean($row['notify_new_unassigned']);
        $row['notify_new_my'] = get_boolean($row['notify_new_my']);
        $row['notify_reply_unassigned'] = get_boolean($row['notify_reply_unassigned']);
        $row['notify_reply_my'] = get_boolean($row['notify_reply_my']);
        $row['notify_assigned'] = get_boolean($row['notify_assigned']);
        $row['notify_pm'] = get_boolean($row['notify_pm']);
        $row['notify_note'] = get_boolean($row['notify_note']);
        $row['notify_note_unassigned'] = get_boolean($row['notify_note_unassigned']);
        $row['autoassign'] = get_boolean($row['autoassign']);
        $row['ratingneg'] = intval($row['ratingneg']);
        $row['ratingpos'] = intval($row['ratingpos']);
        $row['autorefresh'] = intval($row['autorefresh']);
        $row['active'] = get_boolean($row['active']);
        $row['default_calendar_view'] = intval($row['default_calendar_view']);
        $row['notify_overdue_unassigned'] = get_boolean($row['notify_overdue_unassigned']);


        // TODO: Remove this once GitHub #346 is complete
        $row['categories'] = explode(',', $row['categories']);
        $row['heskprivileges'] = explode(',', $row['heskprivileges']);


        $results[] = $row;
    }

    return $id == NULL ? $results : $results[0];
}

function get_boolean($value, $truthy_value = true) {
    return $value == $truthy_value;
}