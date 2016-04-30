<?php
/*******************************************************************************
 *  Title: Help Desk Software HESK
 *  Version: 2.6.5 from 28th August 2015
 *  Author: Klemen Stirn
 *  Website: http://www.hesk.com
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

// Get all the required files and functions
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
hesk_load_database_functions();
require(HESK_PATH . 'inc/email_functions.inc.php');
require(HESK_PATH . 'inc/htmLawed.php');
require(HESK_PATH . 'inc/posting_functions.inc.php');

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();
$modsForHesk_settings = mfh_getSettings();

// We only allow POST requests from the HESK form to this file
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: admin_main.php');
    exit();
}

// Check for POST requests larger than what the server can handle
if (empty($_POST) && !empty($_SERVER['CONTENT_LENGTH'])) {
    hesk_error($hesklang['maxpost']);
}

$hesk_error_buffer = array();

if ($hesk_settings['can_sel_lang']) {
    $tmpvar['language'] = hesk_POST('customerLanguage');
}
$tmpvar['name'] = hesk_input(hesk_POST('name')) or $hesk_error_buffer['name'] = $hesklang['enter_your_name'];
$tmpvar['email'] = hesk_POST('email');
if ($hesk_settings['multi_eml']) {
    $tmpvar['email'] = str_replace(';',',', $tmpvar['email']);
}
$tmpvar['category'] = intval(hesk_POST('category')) or $hesk_error_buffer['category'] = $hesklang['sel_app_cat'];
$tmpvar['priority'] = hesk_POST('priority');
$tmpvar['priority'] = strlen($tmpvar['priority']) ? intval($tmpvar['priority']) : -1;

if ($tmpvar['priority'] < 0 || $tmpvar['priority'] > 3) {
    // If we are showing "Click to select" priority needs to be selected
    if ($hesk_settings['select_pri']) {
        $tmpvar['priority'] = -1;
        $hesk_error_buffer['priority'] = $hesklang['select_priority'];
    } else {
        $tmpvar['priority'] = 3;
    }
}

$tmpvar['subject'] = hesk_input(hesk_POST('subject')) or $hesk_error_buffer['subject'] = $hesklang['enter_ticket_subject'];
$tmpvar['message'] = hesk_input(hesk_POST('message')) or $hesk_error_buffer['message'] = $hesklang['enter_message'];

// Is category a valid choice?
if ($tmpvar['category']) {
    hesk_verifyCategory(1);

    // Is auto-assign of tickets disabled in this category?
    if (empty($hesk_settings['category_data'][$tmpvar['category']]['autoassign'])) {
        $hesk_settings['autoassign'] = false;
    }
}

// Custom fields
foreach ($hesk_settings['custom_fields'] as $k => $v) {
    if ($v['use'] && isset($_POST[$k])) {
        // Date will be handled by the jQuery datepicker
        if ($v['type'] == 'date' && $_POST[$k] != '') {
            $tmpvar[$k] = strtotime($_POST[$k]);
        } else if (is_array($_POST[$k])) {
            $tmpvar[$k] = '';
            foreach ($_POST[$k] as $myCB) {
                $tmpvar[$k] .= (is_array($myCB) ? '' : hesk_input($myCB)) . '<br />';
            }
            $tmpvar[$k] = substr($tmpvar[$k], 0, -6);
        } else {
            $tmpvar[$k] = hesk_makeURL(nl2br(hesk_input($_POST[$k])));
        }
    } else {
        $tmpvar[$k] = '';
    }
}

// Generate tracking ID
$tmpvar['trackid'] = hesk_createID();

// Log who submitted ticket
$tmpvar['history'] = sprintf($hesklang['thist7'], hesk_date(), $_SESSION['name'] . ' (' . $_SESSION['user'] . ')');
$tmpvar['openedby'] = $_SESSION['id'];

// Owner
$tmpvar['owner'] = 0;
if (hesk_checkPermission('can_assign_others', 0)) {
    $tmpvar['owner'] = intval(hesk_POST('owner'));

    // If ID is -1 the ticket will be unassigned
    if ($tmpvar['owner'] == -1) {
        $tmpvar['owner'] = 0;
    } // Automatically assign owner?
    elseif ($tmpvar['owner'] == -2 && $hesk_settings['autoassign'] == 1) {
        $autoassign_owner = hesk_autoAssignTicket($tmpvar['category']);
        if ($autoassign_owner) {
            $tmpvar['owner'] = intval($autoassign_owner['id']);
            $tmpvar['history'] .= sprintf($hesklang['thist10'], hesk_date(), $autoassign_owner['name'] . ' (' . $autoassign_owner['user'] . ')');
        } else {
            $tmpvar['owner'] = 0;
        }
    } // Check for invalid owner values
    elseif ($tmpvar['owner'] < 1) {
        $tmpvar['owner'] = 0;
    } else {
        // Has the new owner access to the selected category?
        $res = hesk_dbQuery("SELECT `name`,`isadmin`,`categories` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `id`='{$tmpvar['owner']}' LIMIT 1");
        if (hesk_dbNumRows($res) == 1) {
            $row = hesk_dbFetchAssoc($res);
            if (!$row['isadmin']) {
                $row['categories'] = explode(',', $row['categories']);
                if (!in_array($tmpvar['category'], $row['categories'])) {
                    $_SESSION['isnotice'][] = 'category';
                    $hesk_error_buffer['owner'] = $hesklang['onasc'];
                }
            }
        } else {
            $_SESSION['isnotice'][] = 'category';
            $hesk_error_buffer['owner'] = $hesklang['onasc'];
        }
    }
} elseif (hesk_checkPermission('can_assign_self', 0) && hesk_okCategory($tmpvar['category'], 0) && !empty($_POST['assing_to_self'])) {
    $tmpvar['owner'] = intval($_SESSION['id']);
}

// Notify customer of the ticket?
$notify = (!empty($_POST['notify']) && !empty($tmpvar['email'])) ? 1 : 0;

// Show ticket after submission?
$show = !empty($_POST['show']) ? 1 : 0;

// Attachments
if ($hesk_settings['attachments']['use']) {
    require_once(HESK_PATH . 'inc/attachments.inc.php');

    $attachments = array();
    $trackingID = $tmpvar['trackid'];

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
$tmpvar['attachments'] = '';

// If we have any errors lets store info in session to avoid re-typing everything
if (count($hesk_error_buffer) != 0) {
    $_SESSION['iserror'] = array_keys($hesk_error_buffer);

    $_SESSION['as_name'] = hesk_POST('name');
    $_SESSION['as_email'] = hesk_POST('email');
    $_SESSION['as_category'] = hesk_POST('category');
    $_SESSION['as_priority'] = $tmpvar['priority'];
    $_SESSION['as_subject'] = hesk_POST('subject');
    $_SESSION['as_message'] = hesk_POST('message');
    $_SESSION['as_owner'] = $tmpvar['owner'];
    $_SESSION['as_notify'] = $notify;
    $_SESSION['as_show'] = $show;

    foreach ($hesk_settings['custom_fields'] as $k => $v) {
        if ($v['use']) {
            $_SESSION["as_$k"] = ($v['type'] == 'checkbox') ? hesk_POST_array($k) : hesk_POST($k);
        }
    }

    $tmp = '';
    foreach ($hesk_error_buffer as $error) {
        $tmp .= "<li>$error</li>\n";
    }
    $hesk_error_buffer = $tmp;

    // Remove any successfully uploaded attachments
    if ($hesk_settings['attachments']['use']) {
        hesk_removeAttachments($attachments);
    }

    $hesk_error_buffer = $hesklang['pcer'] . '<br /><br /><ul>' . $hesk_error_buffer . '</ul>';
    hesk_process_messages($hesk_error_buffer, 'new_ticket.php');
}

if ($hesk_settings['attachments']['use'] && !empty($attachments)) {
    foreach ($attachments as $myatt) {
        hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` (`ticket_id`,`saved_name`,`real_name`,`size`) VALUES ('" . hesk_dbEscape($tmpvar['trackid']) . "','" . hesk_dbEscape($myatt['saved_name']) . "','" . hesk_dbEscape($myatt['real_name']) . "','" . intval($myatt['size']) . "')");
        $tmpvar['attachments'] .= hesk_dbInsertID() . '#' . $myatt['real_name'] . '#' . $myatt['saved_name'] . ',';
    }
}

if (!$modsForHesk_settings['rich_text_for_tickets']) {
    $tmpvar['message'] = hesk_makeURL($tmpvar['message']);
    $tmpvar['message'] = nl2br($tmpvar['message']);
}

$tmpvar['latitude'] = hesk_POST('latitude', 'E-4');
$tmpvar['longitude'] = hesk_POST('longitude', 'E-4');

$tmpvar['html'] = $modsForHesk_settings['rich_text_for_tickets'];
$tmpvar['due_date'] = hesk_POST('due-date');

// Set user agent and screen res to null
$tmpvar['user_agent'] = NULL;
$tmpvar['screen_resolution_height'] = "NULL";
$tmpvar['screen_resolution_width'] = "NULL";

// Insert ticket to database
$ticket = hesk_newTicket($tmpvar);

// Notify the customer about the ticket?
if ($notify) {
    hesk_notifyCustomer($modsForHesk_settings);
}

// If ticket is assigned to someone notify them?
if ($ticket['owner'] && $ticket['owner'] != intval($_SESSION['id'])) {
    // If we don't have info from auto-assign get it from database
    if (!isset($autoassign_owner['email'])) {
        hesk_notifyAssignedStaff(false, 'ticket_assigned_to_you', $modsForHesk_settings);
    } else {
        hesk_notifyAssignedStaff($autoassign_owner, 'ticket_assigned_to_you', $modsForHesk_settings);
    }
} // Ticket unassigned, notify everyone that selected to be notified about unassigned tickets
elseif (!$ticket['owner']) {
    hesk_notifyStaff('new_ticket_staff', " `id` != " . intval($_SESSION['id']) . " AND `notify_new_unassigned` = '1' ", $modsForHesk_settings);
}

// Unset temporary variables
unset($tmpvar);
hesk_cleanSessionVars('tmpvar');
hesk_cleanSessionVars('as_name');
hesk_cleanSessionVars('as_email');
hesk_cleanSessionVars('as_category');
hesk_cleanSessionVars('as_priority');
hesk_cleanSessionVars('as_subject');
hesk_cleanSessionVars('as_message');
hesk_cleanSessionVars('as_owner');
hesk_cleanSessionVars('as_notify');
hesk_cleanSessionVars('as_show');
foreach ($hesk_settings['custom_fields'] as $k => $v) {
    if ($v['use']) {
        hesk_cleanSessionVars("as_$k");
    }
}

// If ticket has been assigned to the person submitting it lets show a message saying so
if ($ticket['owner'] && $ticket['owner'] == intval($_SESSION['id'])) {
    $hesklang['new_ticket_submitted'] .= '<br />&nbsp;<br />
    <span class="glyphicon glyphicon-comment"></span> <b>' . (isset($autoassign_owner) ? $hesklang['taasy'] : $hesklang['tasy']) . '</b>';
}

// Show the ticket or just the success message
if ($show) {
    hesk_process_messages($hesklang['new_ticket_submitted'], 'admin_ticket.php?track=' . $ticket['trackid'] . '&Refresh=' . mt_rand(10000, 99999), 'SUCCESS');
} else {
    hesk_process_messages($hesklang['new_ticket_submitted'] . '. <a href="admin_ticket.php?track=' . $ticket['trackid'] . '&Refresh=' . mt_rand(10000, 99999) . '">' . $hesklang['view_ticket'] . '</a>', 'new_ticket.php', 'SUCCESS');
}
?>
