<?php
/*******************************************************************************
 *  Title: Help Desk Software HESK
 *  Version: 2.6.1 from 26th February 2015
 *  Author: Klemen Stirn
 *  Website: https://www.hesk.com
 ********************************************************************************
 *  COPYRIGHT AND TRADEMARK NOTICE
 *  Copyright 2005-2015 Klemen Stirn. All Rights Reserved.
 *  HESK is a registered trademark of Klemen Stirn.
 *  The HESK may be used and modified free of charge by anyone
 *  AS LONG AS COPYRIGHT NOTICES AND ALL THE COMMENTS REMAIN INTACT.
 *  By using this code you agree to indemnify Klemen Stirn from any
 *  liability that might arise from it's use.
 *  Selling the code for this program, in part or full, without prior
 *  written consent is expressly forbidden.
 *  Using this code, in part or full, to create derivate work,
 *  new scripts or products is expressly forbidden. Obtain permission
 *  before redistributing this software over the Internet or in
 *  any other medium. In all cases copyright and header must remain intact.
 *  This Copyright is in full effect in any country that has International
 *  Trade Agreements with the United States of America or
 *  with the European Union.
 *  Removing any of the copyright notices without purchasing a license
 *  is expressly forbidden. To remove HESK copyright notice you must purchase
 *  a license for this script. For more information on how to obtain
 *  a license please visit the page below:
 *  https://www.hesk.com/buy.php
 *******************************************************************************/

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

$can_assign_others = hesk_checkPermission('can_assign_others', 0);
if ($can_assign_others) {
    $can_assign_self = TRUE;
} else {
    $can_assign_self = hesk_checkPermission('can_assign_self', 0);
}

/* A security check */
hesk_token_check();

/* Ticket ID */
$trackingID = hesk_cleanID() or die($hesklang['int_error'] . ': ' . $hesklang['no_trackID']);

$res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `trackid`='" . hesk_dbEscape($trackingID) . "' LIMIT 1");
if (hesk_dbNumRows($res) != 1) {
    hesk_error($hesklang['ticket_not_found']);
}
$ticket = hesk_dbFetchAssoc($res);

$_SERVER['PHP_SELF'] = 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . rand(10000, 99999);

/* New owner ID */
$owner = intval(hesk_REQUEST('owner'));

/* If ID is -1 the ticket will be unassigned */
if ($owner == -1) {
    $revision = sprintf($hesklang['thist2'], hesk_date(), '<i>' . $hesklang['unas'] . '</i>', $_SESSION['name'] . ' (' . $_SESSION['user'] . ')');
    $res = hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `owner`=0 , `history`=CONCAT(`history`,'" . hesk_dbEscape($revision) . "') WHERE `trackid`='" . hesk_dbEscape($trackingID) . "'");

    hesk_process_messages($hesklang['tunasi2'], $_SERVER['PHP_SELF'], 'SUCCESS');
} elseif ($owner < 1) {
    hesk_process_messages($hesklang['nose'], $_SERVER['PHP_SELF'], 'NOTICE');
}

/* Verify the new owner and permissions */
$res = hesk_dbQuery("SELECT `id`,`user`,`name`,`email`,`isadmin`,`categories`,`notify_assigned` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `id`='{$owner}' LIMIT 1");
$row = hesk_dbFetchAssoc($res);

/* Has new owner access to the category? */
if (!$row['isadmin']) {
    $row['categories'] = explode(',', $row['categories']);
    if (!in_array($ticket['category'], $row['categories'])) {
        hesk_error($hesklang['unoa']);
    }
}

// Make sure two people don't assign a ticket to a different user at the same time
if ($ticket['owner'] && $ticket['owner'] != $owner && hesk_REQUEST('unassigned') && hesk_GET('confirm') != 'Y') {
    $new_owner = ($owner == $_SESSION['id']) ? $hesklang['scoy'] : sprintf($hesklang['scot'], $row['name']);

    $res = hesk_dbQuery("SELECT `name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `id`='{$ticket['owner']}' LIMIT 1");

    if (hesk_dbNumRows($res) == 1) {
        $row = hesk_dbFetchAssoc($res);

        hesk_process_messages(
            sprintf($hesklang['taat'], $row['name']) .
            '<br /><br />' .
            $new_owner .
            '<br /><br />' .
            '<a href="assign_owner.php?track='.$ticket['trackid'].'&amp;owner='.$owner.'&amp;token='.hesk_token_echo(0).'&amp;unassigned=1&amp;confirm=Y">'.$hesklang['ycto'].'</a> | ' .
            '<a href="admin_ticket.php?track='.$ticket['trackid'].'">'.$hesklang['ncto'].'</a>',
            $_SERVER['PHP_SELF'], 'NOTICE'
        );
    }
}

/* Assigning to self? */
if ($can_assign_others || ($owner == $_SESSION['id'] && $can_assign_self)) {
    $revision = sprintf($hesklang['thist2'], hesk_date(), $row['name'] . ' (' . $row['user'] . ')', $_SESSION['name'] . ' (' . $_SESSION['user'] . ')');
    $res = hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `owner`={$owner} , `history`=CONCAT(`history`,'" . hesk_dbEscape($revision) . "') WHERE `trackid`='" . hesk_dbEscape($trackingID) . "'");

    if ($owner != $_SESSION['id'] && !hesk_checkPermission('can_view_ass_others', 0)) {
        $_SERVER['PHP_SELF'] = 'admin_main.php';
    }
} else {
    hesk_error($hesklang['no_permission']);
}

$ticket['owner'] = $owner;

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

/* Notify the new owner? */
if ($ticket['owner'] != intval($_SESSION['id'])) {
    hesk_notifyAssignedStaff(false, 'ticket_assigned_to_you', $modsForHesk_settings);
}

$tmp = ($owner == $_SESSION['id']) ? $hesklang['tasy'] : $hesklang['taso'];
hesk_process_messages($tmp, $_SERVER['PHP_SELF'], 'SUCCESS');
?>
