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
define('HESK_PATH', './');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');

// Are we in maintenance mode?
hesk_check_maintenance();

hesk_load_database_functions();
require(HESK_PATH . 'inc/email_functions.inc.php');
require(HESK_PATH . 'inc/posting_functions.inc.php');
require(HESK_PATH . 'inc/htmLawed.php');

// We only allow POST requests to this file
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: index.php');
    exit();
}

// Check for POST requests larger than what the server can handle
if (empty($_POST) && !empty($_SERVER['CONTENT_LENGTH'])) {
    hesk_error($hesklang['maxpost']);
}

hesk_session_start();

/* A security check */
# hesk_token_check('POST');


/* Connect to database */
hesk_dbConnect();
$hesk_error_buffer = array();

// Tracking ID
$trackingID = hesk_cleanID('orig_track') or die($hesklang['int_error'] . ': No orig_track');

// Email required to view ticket?
$my_email = hesk_getCustomerEmail();

// Setup required session vars
$_SESSION['t_track'] = $trackingID;
$_SESSION['t_email'] = $my_email;

// Get message
$message = hesk_input(hesk_POST('message'));

// If the message was entered, further parse it
$modsForHesk_settings = mfh_getSettings();
if (strlen($message) && !$modsForHesk_settings['rich_text_for_tickets_for_customers']) {
    // Make links clickable
    $message = hesk_makeURL($message);

    // Turn newlines into <br />
    $message = nl2br($message);
} else {
    $hesk_error_buffer[] = $hesklang['enter_message'];
}

/* Attachments */
if ($hesk_settings['attachments']['use']) {
    require(HESK_PATH . 'inc/attachments.inc.php');
    $attachments = array();

    $use_legacy_attachments = hesk_POST('use-legacy-attachments', 0);
    if ($use_legacy_attachments) {
        for ($i = 1; $i <= $hesk_settings['attachments']['max_number']; $i++) {
            $att = hesk_uploadFile($i);
            if ($att !== false && !empty($att)) {
                $attachments[$i] = $att;
            }
        }
    } else {
        // The user used the new drag-and-drop system.
        $temp_attachment_ids = hesk_POST_array('attachment-ids');
        foreach ($temp_attachment_ids as $temp_attachment_id) {
            // Simply get the temp info and move it to the attachments table
            $temp_attachment = mfh_getTemporaryAttachment($temp_attachment_id);
            $attachments[] = $temp_attachment;
            mfh_deleteTemporaryAttachment($temp_attachment_id);
        }
    }
}
$myattachments = '';

/* Any errors? */
if (count($hesk_error_buffer) != 0) {
    $_SESSION['ticket_message'] = hesk_POST('message');

    // If this was a reply after re-opening a ticket, force the form at the top
    if (hesk_POST('reopen') == 1) {
        $_SESSION['force_form_top'] = true;
    }

    // Remove any successfully uploaded attachments
    if ($hesk_settings['attachments']['use']) {
        hesk_removeAttachments($attachments);
    }

    $tmp = '';
    foreach ($hesk_error_buffer as $error) {
        $tmp .= "<li>$error</li>\n";
    }
    $hesk_error_buffer = $tmp;

    $hesk_error_buffer = $hesklang['pcer'] . '<br /><br /><ul>' . $hesk_error_buffer . '</ul>';
    hesk_process_messages($hesk_error_buffer,'ticket.php');
}

// Check if this IP is temporarily locked out
$res = hesk_dbQuery("SELECT `number` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "logins` WHERE `ip`='" . hesk_dbEscape($_SERVER['REMOTE_ADDR']) . "' AND `last_attempt` IS NOT NULL AND DATE_ADD(`last_attempt`, INTERVAL " . intval($hesk_settings['attempt_banmin']) . " MINUTE ) > NOW() LIMIT 1");
if (hesk_dbNumRows($res) == 1) {
    if (hesk_dbResult($res) >= $hesk_settings['attempt_limit']) {
        unset($_SESSION);
        hesk_error(sprintf($hesklang['yhbb'], $hesk_settings['attempt_banmin']), 0);
    }
}

/* Get details about the original ticket */
$res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `trackid`='{$trackingID}' LIMIT 1");
if (hesk_dbNumRows($res) != 1) {
    hesk_error($hesklang['ticket_not_found']);
}
$ticket = hesk_dbFetchAssoc($res);

/* If we require e-mail to view tickets check if it matches the one in database */
hesk_verifyEmailMatch($trackingID, $my_email, $ticket['email']);

/* Ticket locked? */
if ($ticket['locked']) {
    hesk_process_messages($hesklang['tislock2'],'ticket.php');
    exit();
}

// Prevent flooding ticket replies
$res = hesk_dbQuery("SELECT `staffid` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` WHERE `replyto`='{$ticket['id']}' AND `dt` > DATE_SUB(NOW(), INTERVAL 10 MINUTE) ORDER BY `id` ASC");
if (hesk_dbNumRows($res) > 0) {
    $sequential_customer_replies = 0;
    while ($tmp = hesk_dbFetchAssoc($res)) {
        $sequential_customer_replies = $tmp['staffid'] ? 0 : $sequential_customer_replies + 1;
    }
    if ($sequential_customer_replies > 10) {
        hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "logins` (`ip`, `number`) VALUES ('" . hesk_dbEscape($_SERVER['REMOTE_ADDR']) . "', " . intval($hesk_settings['attempt_limit'] + 1) . ")");
        hesk_error(sprintf($hesklang['yhbr'], $hesk_settings['attempt_banmin']), 0);
    }
}

/* Insert attachments */
if ($hesk_settings['attachments']['use'] && !empty($attachments)) {
    foreach ($attachments as $myatt) {
        hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` (`ticket_id`,`saved_name`,`real_name`,`size`) VALUES ('{$trackingID}','" . hesk_dbEscape($myatt['saved_name']) . "','" . hesk_dbEscape($myatt['real_name']) . "','" . intval($myatt['size']) . "')");
        $myattachments .= hesk_dbInsertID() . '#' . $myatt['real_name'] . '#' . $myatt['saved_name'] . ',';
    }
}

// If staff hasn't replied yet, don't change the status; otherwise set it to the status for customer replies.
$rs = hesk_dbQuery("SELECT `Closable` from `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` WHERE `ID` = " . intval($ticket['status']));
$is_status_changable = hesk_dbFetchAssoc($rs);
if ($is_status_changable['Closable'] == 'yes' || $is_status_changable['Closable'] == 'conly') {
    $customerReplyStatusQuery = 'SELECT `ID` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'statuses` WHERE `IsCustomerReplyStatus` = 1';
    $defaultNewTicketStatusQuery = 'SELECT `ID` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'statuses` WHERE `IsNewTicketStatus` = 1';
    $newStatusRs = hesk_dbQuery($customerReplyStatusQuery);
    $newStatus = hesk_dbFetchAssoc($newStatusRs);
    $defaultNewTicketStatusRs = hesk_dbQuery($defaultNewTicketStatusQuery);
    $defaultNewTicketStatus = hesk_dbFetchAssoc($defaultNewTicketStatusRs);

    $ticket['status'] = $ticket['status'] == $defaultNewTicketStatus['ID'] ? $defaultNewTicketStatus['ID'] : $newStatus['ID'];
}

/* Update ticket as necessary */
$res = hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `lastchange`=NOW(), `status`='{$ticket['status']}', `replies`=`replies`+1, `lastreplier`='0' WHERE `id`='{$ticket['id']}'");

// Insert reply into database
$modsForHesk_settings = mfh_getSettings();
$html = $modsForHesk_settings['rich_text_for_tickets_for_customers'];
hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` (`replyto`,`name`,`message`,`dt`,`attachments`, `html`) VALUES ({$ticket['id']},'" . hesk_dbEscape($ticket['name']) . "','" . hesk_dbEscape($message) . "',NOW(),'" . hesk_dbEscape($myattachments) . "','" . $html . "')");


/*** Need to notify any staff? ***/

// --> Prepare reply message

// 1. Generate the array with ticket info that can be used in emails
$info = array(
    'email' => $ticket['email'],
    'category' => $ticket['category'],
    'priority' => $ticket['priority'],
    'owner' => $ticket['owner'],
    'trackid' => $ticket['trackid'],
    'status' => $ticket['status'],
    'name' => $ticket['name'],
    'lastreplier' => $ticket['name'],
    'subject' => $ticket['subject'],
    'message' => stripslashes($message),
    'attachments' => $myattachments,
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

// --> If ticket is assigned just notify the owner
if ($ticket['owner']) {
    hesk_notifyAssignedStaff(false, 'new_reply_by_customer', $modsForHesk_settings, 'notify_reply_my');
} // --> No owner assigned, find and notify appropriate staff
else {
    hesk_notifyStaff('new_reply_by_customer', "`notify_reply_unassigned`='1'", $modsForHesk_settings);
}

/* Clear unneeded session variables */
hesk_cleanSessionVars('ticket_message');

/* Show the ticket and the success message */
hesk_process_messages($hesklang['reply_submitted_success'],'ticket.php','SUCCESS');
exit();
