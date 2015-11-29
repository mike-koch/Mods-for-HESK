<?php
require_once(API_PATH . 'dao/ticket_dao.php');

function get_ticket_for_staff($hesk_settings, $id = NULL) {
    $tickets = get_ticket_for_id($hesk_settings, $id);

    if ($id === NULL) {
        foreach ($tickets as $ticket) {
            $ticket = remove_common_properties($ticket);
            $ticket['suggestedArticles'] = $ticket['articles'];
            unset($ticket['articles']);
            $ticket['legacyAuditTrail'] = $ticket['history'];
            unset($ticket['history']);
            $ticket['linkedTo'] = $ticket['parent'];
            unset($ticket['parent']);
        }
    } else {
        $tickets = remove_common_properties($tickets);

        $tickets['suggestedArticles'] = $tickets['articles'];
        unset($tickets['articles']);
        $tickets['legacyAuditTrail'] = $tickets['history'];
        unset($tickets['history']);
        $tickets['linkedTo'] = $tickets['parent'];
        unset($tickets['parent']);
    }


    return $tickets;
}

function remove_common_properties($ticket) {
    unset($ticket['lastchange']);
    unset($ticket['firstreply']);
    unset($ticket['closedat']);
    unset($ticket['openedby']);
    unset($ticket['firstreplyby']);
    unset($ticket['closedby']);
    unset($ticket['replies']);
    unset($ticket['staffreplies']);
    unset($ticket['lastreplier']);
    unset($ticket['replierid']);

    return $ticket;
}

function get_ticket($hesk_settings, $id) {
    $ticket = get_ticket_for_id($hesk_settings, $id);
    $ticket = remove_common_properties($ticket);
    $ticket = remove_staff_specific_properties($ticket);

    return $ticket;
}

function remove_staff_specific_properties($ticket) {
    unset($ticket['articles']);
    unset($ticket['ip']);
    unset($ticket['language']);
    unset($ticket['owner']);
    unset($ticket['time_worked']);
    unset($ticket['history']);
    unset($ticket['latitude']);
    unset($ticket['longitude']);
    unset($ticket['user_agent']);
    unset($ticket['screen_resolution_width']);
    unset($ticket['screen_resolution_height']);
    unset($ticket['parent']);

    return $ticket;
}