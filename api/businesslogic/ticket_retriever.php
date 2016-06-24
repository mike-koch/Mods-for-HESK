<?php
require_once(API_PATH . 'dao/ticket_dao.php');

function get_ticket_for_staff($hesk_settings, $user, $id = NULL) {
    $tickets = get_ticket_for_id($hesk_settings, $user, $id);

    if ($tickets == NULL) {
        return NULL;
    }

    if ($id === NULL) {
        $original_tickets = $tickets;
        $tickets = array();
        foreach ($original_tickets as $ticket) {
            $ticket = remove_common_properties($ticket);
            $ticket = convert_to_camel_case($ticket);
            $ticket = handle_dates($ticket);
            $tickets[] = $ticket;
        }
    } else {
        $tickets = remove_common_properties($tickets);
        $tickets = handle_dates($tickets);
        $tickets = convert_to_camel_case($tickets);
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

function handle_dates($ticket) {
    $ticket['dt'] = hesk_date($ticket['dt'], true);

    return $ticket;
}

function convert_to_camel_case($ticket) {
    if (isset($ticket['articles'])) {
        $ticket['suggestedArticles'] = $ticket['articles'];
        unset($ticket['articles']);
    }
    $ticket['legacyAuditTrail'] = $ticket['history'];
    unset($ticket['history']);
    $ticket['linkedTo'] = $ticket['parent'];
    unset($ticket['parent']);
    $ticket['timeWorked'] = $ticket['time_worked'];
    unset($ticket['time_worked']);
    $ticket['userAgent'] = $ticket['user_agent'];
    unset($ticket['user_agent']);
    $ticket['screenResolutionWidth'] = $ticket['screen_resolution_width'];
    unset($ticket['screen_resolution_width']);
    $ticket['screenResolutionHeight'] = $ticket['screen_resolution_height'];
    unset($ticket['screen_resolution_height']);
    $ticket['trackingId'] = $ticket['trackid'];
    unset($ticket['trackid']);
    $ticket['dateCreated'] = $ticket['dt'];
    unset($ticket['dt']);
    $ticket['dueDate'] = $ticket['due_date'];
    unset($ticket['due_date']);
    $ticket['overdueEmailSent'] = $ticket['overdue_email_sent'];


    return $ticket;
}

function get_ticket($hesk_settings, $id) {
    $ticket = get_ticket_for_id($hesk_settings, $id);
    $ticket = remove_common_properties($ticket);
    $ticket = remove_staff_specific_properties($ticket);
    $ticket = convert_to_camel_case($ticket);

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
    unset($ticket['due_date']);
    unset($ticket['overdue_email_sent']);

    return $ticket;
}