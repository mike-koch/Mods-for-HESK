<?php
/**
 *
 * This file is part of HESK - PHP Help Desk Software.
 *
 * (c) Copyright Klemen Stirn. All rights reserved.
 * https://www.hesk.com
 *
 * For the full copyright and license agreement information visit
 * https://www.hesk.com/eula.php
 *
 */

define('IN_SCRIPT', 1);
define('HESK_PATH', '../');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
hesk_load_database_functions();
require(HESK_PATH . 'inc/email_functions.inc.php');

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();
$modsForHesk_settings = mfh_getSettings();

/* Check permissions for this feature */
if (hesk_checkPermission('can_change_cat', 0)) {
    hesk_checkPermission('can_change_own_cat');
}

/* A security check */
hesk_token_check('POST');

/* Ticket ID */
$trackingID = hesk_cleanID() or die($hesklang['int_error'] . ': ' . $hesklang['no_trackID']);

/* Category ID */
$category = intval(hesk_POST('category', -1));
if ($category < 1) {
    hesk_process_messages($hesklang['incat'], 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . rand(10000, 99999), 'NOTICE');
}

/* Get new category details */
$res = hesk_dbQuery("SELECT `name`,`autoassign` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` WHERE `id`='{$category}' LIMIT 1");
if (hesk_dbNumRows($res) != 1) {
    hesk_error("$hesklang[int_error]: $hesklang[kb_cat_inv].");
}
$row = hesk_dbFetchAssoc($res);

/* Should tickets in new category be auto-assigned if necessary? */
if (!$row['autoassign']) {
    $hesk_settings['autoassign'] = false;
}

/* Is user allowed to view tickets in new category? */
$category_ok = hesk_okCategory($category, 0);

// Is user allowed to move tickets to this category?
if (!$category_ok && !hesk_checkPermission('can_submit_any_cat', 0)) {
    hesk_process_messages($hesklang['noauth_move'],'admin_main.php');
}

/* Get details about the original ticket */
$res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `trackid`='" . hesk_dbEscape($trackingID) . "' LIMIT 1");
if (hesk_dbNumRows($res) != 1) {
    hesk_error($hesklang['ticket_not_found']);
}
$ticket = hesk_dbFetchAssoc($res);

/* Log that ticket is being moved */
mfh_insert_audit_trail_record($ticket['id'], 'TICKET', 'audit_moved_category', hesk_date(), array(
    0 => $_SESSION['name'] . ' (' . $_SESSION['user'] . ')',
    1 => $row['name']
));

/* Is the ticket assigned to someone? If yes, check that the user has access to category or change to unassigned */
$need_to_reassign = 0;
if ($ticket['owner']) {
    if ($ticket['owner'] == $_SESSION['id'] && !$category_ok) {
        $need_to_reassign = 1;
    } else {
        $res = hesk_dbQuery("SELECT `isadmin`,`categories` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `id`='" . intval($ticket['owner']) . "' LIMIT 1");
        if (hesk_dbNumRows($res) != 1) {
            $need_to_reassign = 1;
        } else {
            $tmp = hesk_dbFetchAssoc($res);
            if (!hesk_okCategory($category, 0, $tmp['isadmin'], explode(',', $tmp['categories']))) {
                $need_to_reassign = 1;
            }
        }
    }
}

/* Reassign automatically if possible */
if ($need_to_reassign || !$ticket['owner']) {
    $need_to_reassign = 1;
    $autoassign_owner = hesk_autoAssignTicket($category);
    if ($autoassign_owner) {
        $ticket['owner'] = $autoassign_owner['id'];
        mfh_insert_audit_trail_record($ticket['id'], 'TICKET', 'audit_autoassigned', hesk_date(), array(
                0 => $autoassign_owner['name'] . ' (' . $autoassign_owner['user'] . ')'
        ));
    } else {
        $ticket['owner'] = 0;
    }
}

hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `category`='" . intval($category) . "', `owner`='" . intval($ticket['owner']) . "' WHERE `trackid`='" . hesk_dbEscape($trackingID) . "'");

$ticket['category'] = $category;

/* --> Prepare message */

// 1. Generate the array with ticket info that can be used in emails
$info = array(
    'email' => $ticket['email'],
    'category' => $ticket['category'],
    'priority' => $ticket['priority'],
    'owner' => $ticket['owner'],
    'trackid' => $ticket['trackid'],
    'status' => $ticket['status'],
    'name' => $ticket['name'],
    'lastreplier' => $ticket['lastreplier'],
    'subject' => $ticket['subject'],
    'message' => $ticket['message'],
    'attachments' => $ticket['attachments'],
    'dt' => hesk_date($ticket['dt'], true),
    'lastchange' => hesk_date($ticket['lastchange'], true),
    'id' => $ticket['id'],
);

// 2. Add custom fields to the array
foreach ($hesk_settings['custom_fields'] as $k => $v) {
    $info[$k] = $v['use'] ? $ticket[$k] : '';
}

// 3. Make sure all values are properly formatted for email
$ticket = hesk_ticketToPlain($info, 1, 0);

/* Need to notify any staff? */
/* --> From autoassign? */
if ($need_to_reassign && !empty($autoassign_owner['email'])) {
    hesk_notifyAssignedStaff($autoassign_owner, 'ticket_assigned_to_you', $modsForHesk_settings);
} /* --> No autoassign, find and notify appropriate staff */
elseif (!$ticket['owner']) {
    hesk_notifyStaff('category_moved', "`notify_new_unassigned`='1' AND `id`!=" . intval($_SESSION['id']), $modsForHesk_settings);
}

/* Is the user allowed to view tickets in the new category? */
if ($category_ok) {
    /* Ticket has an owner */
    if ($ticket['owner']) {
        /* Staff is owner or can view tickets assigned to others */
        if ($ticket['owner'] == $_SESSION['id'] || hesk_checkPermission('can_view_ass_others', 0)) {
            hesk_process_messages($hesklang['moved_to'], 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . rand(10000, 99999), 'SUCCESS');
        } else {
            hesk_process_messages($hesklang['moved_to'], 'admin_main.php', 'SUCCESS');
        }
    } /* Ticket is unassigned, staff can view unassigned tickets */
    elseif (hesk_checkPermission('can_view_unassigned', 0)) {
        hesk_process_messages($hesklang['moved_to'], 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . rand(10000, 99999), 'SUCCESS');
    } /* Ticket is unassigned, staff cannot view unassigned tickets */
    else {
        hesk_process_messages($hesklang['moved_to'], 'admin_main.php', 'SUCCESS');
    }
} else {
    hesk_process_messages($hesklang['moved_to'], 'admin_main.php', 'SUCCESS');
}
?>
