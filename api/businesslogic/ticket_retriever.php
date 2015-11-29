<?php
require_once(API_PATH . 'dao/ticket_dao.php');

function get_ticket($hesk_settings, $id = NULL) {
    $tickets = get_ticket_for_id($hesk_settings, $id);

    if ($id === NULL) {
        foreach ($tickets as $ticket) {
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

            $ticket['suggestedArticles'] = $ticket['articles'];
            unset($ticket['articles']);
            $ticket['legacyAuditTrail'] = $ticket['history'];
            unset($ticket['history']);
            $ticket['linkedTo'] = $ticket['parent'];
            unset($ticket['parent']);
        }
    } else {
        unset($tickets['lastchange']);
        unset($tickets['firstreply']);
        unset($tickets['closedat']);
        unset($tickets['openedby']);
        unset($tickets['firstreplyby']);
        unset($tickets['closedby']);
        unset($tickets['replies']);
        unset($tickets['staffreplies']);
        unset($tickets['lastreplier']);
        unset($tickets['replierid']);

        $tickets['suggestedArticles'] = $tickets['articles'];
        unset($tickets['articles']);
        $tickets['legacyAuditTrail'] = $tickets['history'];
        unset($tickets['history']);
        $tickets['linkedTo'] = $tickets['parent'];
        unset($tickets['parent']);
    }


    return $tickets;
}