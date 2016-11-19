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
require(HESK_PATH . 'inc/posting_functions.inc.php');
require(HESK_PATH . 'inc/htmLawed.php');

// We only allow POST requests from the HESK form to this file
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: admin_main.php');
    exit();
}

// Check for POST requests larger than what the server can handle
if (empty($_POST) && !empty($_SERVER['CONTENT_LENGTH'])) {
    hesk_error($hesklang['maxpost']);
}

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

/* Check permissions for this feature */
if (!isset($_REQUEST['isManager']) || !$_REQUEST['isManager']) {
    hesk_checkPermission('can_reply_tickets');
}

/* A security check */
# hesk_token_check('POST');

/* Original ticket ID */
$replyto = intval(hesk_POST('orig_id', 0)) or die($hesklang['int_error']);

/* Get details about the original ticket */
$result = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `id`='{$replyto}' LIMIT 1");
if (hesk_dbNumRows($result) != 1) {
    hesk_error($hesklang['ticket_not_found']);
}
$ticket = hesk_dbFetchAssoc($result);
$trackingID = $ticket['trackid'];

// Do we require owner before allowing to reply?
if ($hesk_settings['require_owner'] && ! $ticket['owner']) {
    hesk_process_messages($hesklang['atbr'],'admin_ticket.php?track='.$ticket['trackid'].'&Refresh='.rand(10000,99999));
}

$hesk_error_buffer = array();

// Get the message
$message = hesk_input(hesk_POST('message'));

// Submit as customer?
$submit_as_customer = isset($_POST['submit_as_customer']) ? true : false;

$modsForHesk_settings = mfh_getSettings();
if (strlen($message)) {
    // Save message for later and ignore the rest?
    if (isset($_POST['save_reply'])) {
        // Delete any existing drafts from this owner for this ticket
        hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "reply_drafts` WHERE `owner`=" . intval($_SESSION['id']) . " AND `ticket`=" . intval($ticket['id']));

        // Save the message draft
        hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "reply_drafts` (`owner`, `ticket`, `message`) VALUES (" . intval($_SESSION['id']) . ", " . intval($ticket['id']) . ", '" . hesk_dbEscape($message) . "')");

        /* Set reply submitted message */
        $_SESSION['HESK_SUCCESS'] = TRUE;
        $_SESSION['HESK_MESSAGE'] = $hesklang['reply_saved'];

        /* What to do after reply? */
        if ($_SESSION['afterreply'] == 1) {
            header('Location: admin_main.php');
        } elseif ($_SESSION['afterreply'] == 2) {
            /* Get the next open ticket that needs a reply */
            $res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `owner` IN ('0','" . intval($_SESSION['id']) . "')
                    AND " . hesk_myCategories() . " AND `status` IN (SELECT `ID` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses`
                        WHERE `IsNewTicketStatus` = 1 OR `IsCustomerReplyStatus` = 1 OR `IsStaffReopenedStatus` = 1)
                    ORDER BY `owner` DESC, `priority` ASC LIMIT 1");

            if (hesk_dbNumRows($res) == 1) {
                $row = hesk_dbFetchAssoc($res);
                $_SESSION['HESK_MESSAGE'] .= '<br /><br />' . $hesklang['rssn'];
                header('Location: admin_ticket.php?track=' . $row['trackid'] . '&Refresh=' . rand(10000, 99999));
            } else {
                header('Location: admin_main.php');
            }
        } else {
            header('Location: admin_ticket.php?track=' . $ticket['trackid'] . '&Refresh=' . rand(10000, 99999));
        }
        exit();
    }

    // Attach signature to the message?
    if (!$submit_as_customer && !empty($_POST['signature'])) {
        if ($modsForHesk_settings['rich_text_for_tickets']) {
            $signature = nl2br($_SESSION['signature']);
            $signature = hesk_htmlspecialchars($signature);
            $message .= "<br><br>" . $signature . "<br>";
        } else {
            $message .= "\n\n" . addslashes($_SESSION['signature']) . "\n";
        }
    }

    if (!$modsForHesk_settings['rich_text_for_tickets']) {
        // Make links clickable
        $message = hesk_makeURL($message);

        // Turn newlines into <br /> tags
        $message = nl2br($message);
    }
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

/* Time spent working on ticket */
$time_worked = hesk_getTime(hesk_POST('time_worked'));

/* Any errors? */
if (count($hesk_error_buffer) != 0) {
    $_SESSION['ticket_message'] = hesk_POST('message');
    $_SESSION['time_worked'] = $time_worked;

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
    hesk_process_messages($hesk_error_buffer, 'admin_ticket.php?track=' . $ticket['trackid'] . '&Refresh=' . rand(10000, 99999));
}

if ($hesk_settings['attachments']['use'] && !empty($attachments)) {
    foreach ($attachments as $myatt) {
        hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` (`ticket_id`,`saved_name`,`real_name`,`size`) VALUES ('" . hesk_dbEscape($trackingID) . "','" . hesk_dbEscape($myatt['saved_name']) . "','" . hesk_dbEscape($myatt['real_name']) . "','" . intval($myatt['size']) . "')");
        $myattachments .= hesk_dbInsertID() . '#' . $myatt['real_name'] . '#' . $myatt['saved_name'] . ',';
    }
}

// Add reply
$html = $modsForHesk_settings['rich_text_for_tickets'];
if ($submit_as_customer) {
    hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` (`replyto`,`name`,`message`,`dt`,`attachments`,`html`) VALUES ('" . intval($replyto) . "','" . hesk_dbEscape(addslashes($ticket['name'])) . "','" . hesk_dbEscape($message . "<br /><br /><i>{$hesklang['creb']} {$_SESSION['name']}</i>") . "',NOW(),'" . hesk_dbEscape($myattachments) . "', '" . $html . "')");
} else {
    hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` (`replyto`,`name`,`message`,`dt`,`attachments`,`staffid`,`html`) VALUES ('" . intval($replyto) . "','" . hesk_dbEscape(addslashes($_SESSION['name'])) . "','" . hesk_dbEscape($message) . "',NOW(),'" . hesk_dbEscape($myattachments) . "','" . intval($_SESSION['id']) . "', '" . $html . "')");
}

/* Track ticket status changes for history */
$revision = '';

/* Change the status of priority? */
if (!empty($_POST['set_priority'])) {
    $priority = intval(hesk_POST('priority'));
    if ($priority < 0 || $priority > 3) {
        hesk_error($hesklang['select_priority']);
    }

    $options = array(
        0 => '<span class="critical">' . $hesklang['critical'] . '</span>',
        1 => '<span class="important">' . $hesklang['high'] . '</span>',
        2 => '<span class="medium">' . $hesklang['medium'] . '</span>',
        3 => $hesklang['low']
    );

    $revision = sprintf($hesklang['thist8'], hesk_date(), $options[$priority], $_SESSION['name'] . ' (' . $_SESSION['user'] . ')');

    $priority_sql = ",`priority`='$priority', `history`=CONCAT(`history`,'" . hesk_dbEscape($revision) . "') ";
} else {
    $priority_sql = "";
}

/* Update the original ticket */
$defaultStatusReplyStatus = hesk_dbFetchAssoc(hesk_dbQuery("SELECT `ID`, `IsClosed` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` WHERE `IsDefaultStaffReplyStatus` = 1 LIMIT 1"));
$staffClosedCheckboxStatus = hesk_dbFetchAssoc(hesk_dbQuery("SELECT `ID`, `IsClosed` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` WHERE `IsStaffClosedOption` = 1 LIMIT 1"));
$lockedTicketStatus = hesk_dbFetchAssoc(hesk_dbQuery("SELECT `ID` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` WHERE `LockedTicketStatus` = 1 LIMIT 1"));

// Get new ticket status
$sql_status = '';
$change_status = true;
// -> If locked, keep it resolved
if ($ticket['locked']) {
    $new_status = $lockedTicketStatus['ID'];
} elseif (isset($_POST['submit_as_status'])) {
    $new_status = $_POST['submit_as_status'];

    if ($ticket['status'] != $new_status) {
        // Does this status close the ticket?
        $newStatusRs = hesk_dbQuery('SELECT `IsClosed`, `Key` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'statuses` WHERE `ID` = ' . hesk_dbEscape($new_status));
        $newStatus = hesk_dbFetchAssoc($newStatusRs);

        if ($newStatus['IsClosed'] && hesk_checkPermission('can_resolve', 0)) {
            $revision = sprintf($hesklang['thist3'], hesk_date(), $_SESSION['name'] . ' (' . $_SESSION['user'] . ')');
            $sql_status = " , `closedat`=NOW(), `closedby`=" . intval($_SESSION['id']) . ", `history`=CONCAT(`history`,'" . hesk_dbEscape($revision) . "') ";

            // Lock the ticket if customers are not allowed to reopen tickets
            if ($hesk_settings['custopen'] != 1) {
                $sql_status .= " , `locked`='1' ";
            }
        } else {
            // Ticket isn't being closed, just add the history to the sql query (or tried to close but doesn't have permission)
            $revision = sprintf($hesklang['thist9'], hesk_date(), $hesklang[$newStatus['Key']], $_SESSION['name'] . ' (' . $_SESSION['user'] . ')');
            $sql_status = " , `history`=CONCAT(`history`,'" . hesk_dbEscape($revision) . "') ";
        }
    }
} // -> Submit as Customer reply
elseif ($submit_as_customer) {
    //Get the status ID for customer replies
    $customerReplyStatusRs = hesk_dbQuery('SELECT `ID` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'statuses` WHERE `IsCustomerReplyStatus` = 1 LIMIT 1');
    $customerReplyStatus = hesk_dbFetchAssoc($customerReplyStatusRs);
    $new_status = $customerReplyStatus['ID'];

    if ($ticket['status'] != $new_status) {
        $revision = sprintf($hesklang['thist9'], hesk_date(), $hesklang['wait_reply'], $_SESSION['name'] . ' (' . $_SESSION['user'] . ')');
        $sql_status = " , `history`=CONCAT(`history`,'" . hesk_dbEscape($revision) . "') ";
    }
} // -> Default: submit as "Replied by staff"
else {
    //Get the status ID for staff replies
    $staffReplyStatusRs = hesk_dbQuery('SELECT `ID` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'statuses` WHERE `IsDefaultStaffReplyStatus` = 1 LIMIT 1');
    $staffReplyStatus = hesk_dbFetchAssoc($staffReplyStatusRs);
    $new_status = $staffReplyStatus['ID'];
}

$sql = "UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `status`='{$new_status}',";
$sql .= $submit_as_customer ? "`lastreplier`='0', `replierid`='0' " : "`lastreplier`='1', `replierid`='" . intval($_SESSION['id']) . "' ";


/* Update time_worked or force update lastchange */
if ($time_worked == '00:00:00') {
    $sql .= ", `lastchange` = NOW() ";
} else {
    $sql .= ",`time_worked` = ADDTIME(`time_worked`,'" . hesk_dbEscape($time_worked) . "') ";
}

if (!empty($_POST['assign_self']) && (hesk_checkPermission('can_assign_self', 0) || (isset($_REQUEST['isManager']) && $_REQUEST['isManager']))) {
    $revision = sprintf($hesklang['thist2'], hesk_date(), $_SESSION['name'] . ' (' . $_SESSION['user'] . ')', $_SESSION['name'] . ' (' . $_SESSION['user'] . ')');
    $sql .= " , `owner`=" . intval($_SESSION['id']) . ", `history`=CONCAT(`history`,'" . hesk_dbEscape($revision) . "') ";
}

$sql .= " $priority_sql ";
$sql .= " $sql_status ";


if (!$ticket['firstreplyby']) {
    $sql .= " , `firstreply`=NOW(), `firstreplyby`=" . intval($_SESSION['id']) . " ";
}

// Keep track of replies to this ticket for easier reporting
$sql .= " , `replies`=`replies`+1 ";
$sql .= $submit_as_customer ? '' : " , `staffreplies`=`staffreplies`+1 ";

// End and execute the query
$sql .= " WHERE `id`='{$replyto}'";
hesk_dbQuery($sql);
unset($sql);

/* Update number of replies in the users table */
hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `replies`=`replies`+1 WHERE `id`='" . intval($_SESSION['id']) . "'");

// --> Prepare reply message

// 1. Generate the array with ticket info that can be used in emails
$info = array(
    'email' => $ticket['email'],
    'category' => $ticket['category'],
    'priority' => $ticket['priority'],
    'owner' => $ticket['owner'],
    'trackid' => $ticket['trackid'],
    'status' => $new_status,
    'name' => $ticket['name'],
    'lastreplier' => ($submit_as_customer ? $ticket['name'] : $_SESSION['name']),
    'subject' => $ticket['subject'],
    'message' => stripslashes($message),
    'attachments' => $myattachments,
    'dt' => hesk_date($ticket['dt'], true),
    'lastchange' => hesk_date($ticket['lastchange'], true),
    'id' => $ticket['id'],
    'language' => $ticket['language']
);

// 2. Add custom fields to the array
foreach ($hesk_settings['custom_fields'] as $k => $v) {
    $info[$k] = $v['use'] ? $ticket[$k] : '';
}

// 3. Make sure all values are properly formatted for email
$ticket = hesk_ticketToPlain($info, 1, 0);

// Notify the assigned staff?
if ($submit_as_customer) {
    if ($ticket['owner'] && $ticket['owner'] != $_SESSION['id']) {
        hesk_notifyAssignedStaff(false, 'new_reply_by_customer', $modsForHesk_settings, 'notify_reply_my');
    }
} // Notify customer?
elseif (!isset($_POST['no_notify']) || intval(hesk_POST('no_notify')) != 1) {
    hesk_notifyCustomer($modsForHesk_settings, 'new_reply_by_staff');
}

// Delete any existing drafts from this owner for this ticket
hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "reply_drafts` WHERE `owner`=" . intval($_SESSION['id']) . " AND `ticket`=" . intval($ticket['id']));

/* Set reply submitted message */
$_SESSION['HESK_SUCCESS'] = TRUE;
$_SESSION['HESK_MESSAGE'] = $hesklang['reply_submitted'];

/* What to do after reply? */
if ($_SESSION['afterreply'] == 1) {
    header('Location: admin_main.php');
} elseif ($_SESSION['afterreply'] == 2) {
    /* Get the next open ticket that needs a reply */
    $res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `owner` IN ('0','" . intval($_SESSION['id']) . "') AND " . hesk_myCategories() . " AND `status` IN (SELECT `ID` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses`
                        WHERE `IsNewTicketStatus` = 1 OR `IsCustomerReplyStatus` = 1 OR `IsStaffReopenedStatus` = 1) ORDER BY `owner` DESC, `priority` ASC LIMIT 1");

    if (hesk_dbNumRows($res) == 1) {
        $row = hesk_dbFetchAssoc($res);
        $_SESSION['HESK_MESSAGE'] .= '<br /><br />' . $hesklang['rssn'];
        header('Location: admin_ticket.php?track=' . $row['trackid'] . '&Refresh=' . rand(10000, 99999));
    } else {
        header('Location: admin_main.php');
    }
} else {
    header('Location: admin_ticket.php?track=' . $ticket['trackid'] . '&Refresh=' . rand(10000, 99999));
}
exit();
?>
