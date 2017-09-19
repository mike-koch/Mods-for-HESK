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
define('WYSIWYG', 1);
define('VALIDATOR', 1);
define('MFH_PAGE_LAYOUT', 'TOP_AND_SIDE');

define('EXTRA_JS', '<script src="'.HESK_PATH.'internal-api/js/admin-ticket.js"></script><script src="'.HESK_PATH.'js/jquery.dirtyforms.min.js"></script>');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/status_functions.inc.php');
require(HESK_PATH . 'inc/view_attachment_functions.inc.php');
require(HESK_PATH . 'inc/mail_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

/* Check permissions for this feature */
hesk_checkPermission('can_view_tickets');
$modsForHesk_settings = mfh_getSettings();

$can_del_notes = hesk_checkPermission('can_del_notes', 0);
$can_reply = hesk_checkPermission('can_reply_tickets', 0);
$can_delete = hesk_checkPermission('can_del_tickets', 0);
$can_edit = hesk_checkPermission('can_edit_tickets', 0);
$can_archive = hesk_checkPermission('can_add_archive', 0);
$can_assign_self = hesk_checkPermission('can_assign_self', 0);
$can_view_unassigned = hesk_checkPermission('can_view_unassigned', 0);
$can_change_cat = hesk_checkPermission('can_change_cat', 0);
$can_change_own_cat  = hesk_checkPermission('can_change_own_cat',0);
$can_ban_emails = hesk_checkPermission('can_ban_emails', 0);
$can_unban_emails = hesk_checkPermission('can_unban_emails', 0);
$can_ban_ips = hesk_checkPermission('can_ban_ips', 0);
$can_unban_ips = hesk_checkPermission('can_unban_ips', 0);
$can_resolve = hesk_checkPermission('can_resolve', 0);

// Get ticket ID
$trackingID = hesk_cleanID() or print_form();

// Load custom fields
require_once(HESK_PATH . 'inc/custom_fields.inc.php');

// Load statuses
//require_once(HESK_PATH . 'inc/statuses.inc.php');

$_SERVER['PHP_SELF'] = 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999);

/* We will need timer function */
define('TIMER', 1);

/* Get ticket info */
$res = hesk_dbQuery("SELECT `t1`.* , `t2`.name AS `repliername` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` AS `t1` LEFT JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` AS `t2` ON `t1`.`replierid` = `t2`.`id` WHERE `trackid`='" . hesk_dbEscape($trackingID) . "' LIMIT 1");

/* Ticket found? */
if (hesk_dbNumRows($res) != 1) {
    /* Ticket not found, perhaps it was merged with another ticket? */
    $res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `merged` LIKE '%#" . hesk_dbEscape($trackingID) . "#%' LIMIT 1");

    if (hesk_dbNumRows($res) == 1) {
        /* OK, found in a merged ticket. Get info */
        $ticket = hesk_dbFetchAssoc($res);
        hesk_process_messages(sprintf($hesklang['tme'], $trackingID, $ticket['trackid']), 'NOREDIRECT', 'NOTICE');
        $trackingID = $ticket['trackid'];
    } else {
        /* Nothing found, error out */
        hesk_process_messages($hesklang['ticket_not_found'], 'NOREDIRECT');
        print_form();
    }
} else {
    /* We have a match, get ticket info */
    $ticket = hesk_dbFetchAssoc($res);
}

/* Permission to view this ticket? */
if ($ticket['owner'] && $ticket['owner'] != $_SESSION['id'] && !hesk_checkPermission('can_view_ass_others', 0)) {
    hesk_error($hesklang['ycvtao']);
}

if (!$ticket['owner'] && !$can_view_unassigned) {
    hesk_error($hesklang['ycovtay']);
}

/* Set last replier name */
if ($ticket['lastreplier']) {
    if (empty($ticket['repliername'])) {
        $ticket['repliername'] = $hesklang['staff'];
    }
} else {
    $ticket['repliername'] = $ticket['name'];
}

/* Get category name and ID */
$result = hesk_dbQuery("SELECT `id`, `name`, `manager` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` WHERE `id`='" . intval($ticket['category']) . "' LIMIT 1");

/* If this category has been deleted use the default category with ID 1 */
if (hesk_dbNumRows($result) != 1) {
    $result = hesk_dbQuery("SELECT `id`, `name`, `manager` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` WHERE `id`='1' LIMIT 1");
}

$category = hesk_dbFetchAssoc($result);
$managerRS = hesk_dbQuery('SELECT * FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'users` WHERE `id` = ' . intval($_SESSION['id']));
$managerRow = hesk_dbFetchAssoc($managerRS);
$isManager = $managerRow['id'] == $category['manager'];
if ($isManager) {
    $can_del_notes =
    $can_reply =
    $can_delete =
    $can_edit =
    $can_archive =
    $can_assign_self =
    $can_view_unassigned =
    $can_change_own_cat =
    $can_change_cat =
    $can_ban_emails =
    $can_unban_emails =
    $can_ban_ips =
    $can_unban_ips =
    $can_resolve = true;
}

/* Is this user allowed to view tickets inside this category? */
hesk_okCategory($category['id']);

/* Delete post action */
if (isset($_GET['delete_post']) && $can_delete && hesk_token_check()) {
    $n = intval(hesk_GET('delete_post'));
    if ($n) {
        /* Get last reply ID, we'll need it later */
        $res = hesk_dbQuery("SELECT `id` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` WHERE `replyto`='" . intval($ticket['id']) . "' ORDER BY `id` DESC LIMIT 1");
        $last_reply_id = hesk_dbResult($res, 0, 0);

        // Was this post submitted by staff and does it have any attachments?
        $res = hesk_dbQuery("SELECT `dt`, `staffid`, `attachments` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` WHERE `id`='" . intval($n) . "' AND `replyto`='" . intval($ticket['id']) . "' LIMIT 1");
        $reply = hesk_dbFetchAssoc($res);

        // If the reply was by a staff member update the appropriate columns
        if ($reply['staffid']) {
            // Is this the only staff reply? Delete "firstreply" and "firstreplyby" columns
            if ($ticket['staffreplies'] <= 1) {
                $staffreplies_sql = ' , `firstreply`=NULL, `firstreplyby`=NULL, `staffreplies`=0 ';
            } // Are we deleting the first staff reply? Update "firstreply" and "firstreplyby" columns
            elseif ($reply['dt'] == $ticket['firstreply'] && $reply['staffid'] == $ticket['firstreplyby']) {
                // Get the new first reply info
                $res = hesk_dbQuery("SELECT `dt`, `staffid` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` WHERE `replyto`='" . intval($ticket['id']) . "' AND `id`!='" . intval($n) . "' AND `staffid`!=0 ORDER BY `id` ASC LIMIT 1");

                // Did we find the new first reply?
                if (hesk_dbNumRows($res)) {
                    $firstreply = hesk_dbFetchAssoc($res);
                    $staffreplies_sql = " , `firstreply`='" . hesk_dbEscape($firstreply['dt']) . "', `firstreplyby`='" . hesk_dbEscape($firstreply['staffid']) . "', `staffreplies`=`staffreplies`-1 ";
                } // The count must have been wrong, update it
                else {
                    $staffreplies_sql = ' , `firstreply`=NULL, `firstreplyby`=NULL, `staffreplies`=0 ';
                }
            } // OK, this is not the first and not the only staff reply, just reduce number
            else {
                $staffreplies_sql = ' , `staffreplies`=`staffreplies`-1 ';
            }
        } else {
            $staffreplies_sql = '';
        }

        /* Delete any attachments to this post */
        if (strlen($reply['attachments'])) {
            $hesk_settings['server_path'] = dirname(dirname(__FILE__));

            /* List of attachments */
            $att = explode(',', substr($reply['attachments'], 0, -1));
            foreach ($att as $myatt) {
                list($att_id, $att_name) = explode('#', $myatt);

                /* Delete attachment files */
                $res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` WHERE `att_id`='" . intval($att_id) . "' LIMIT 1");
                if (hesk_dbNumRows($res) && $file = hesk_dbFetchAssoc($res)) {
                    hesk_unlink($hesk_settings['server_path'] . '/' . $hesk_settings['attach_dir'] . '/' . $file['saved_name']);
                }

                /* Delete attachments info from the database */
                hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` WHERE `att_id`='" . intval($att_id) . "'");
            }
        }

        /* Delete this reply */
        hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` WHERE `id`='" . intval($n) . "' AND `replyto`='" . intval($ticket['id']) . "'");

        /* Reply wasn't deleted */
        if (hesk_dbAffectedRows() != 1) {
            hesk_process_messages($hesklang['repl1'], $_SERVER['PHP_SELF']);
        } else {
            $closed_sql = '';
            $changeStatusRs = hesk_dbQuery('SELECT `id`, `LockedTicketStatus`, `IsCustomerReplyStatus`, `IsDefaultStaffReplyStatus`, `IsNewTicketStatus`
                                                  FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'statuses`
                                                  WHERE `LockedTicketStatus` = 1
                                                    OR `IsCustomerReplyStatus` = 1
                                                    OR `IsDefaultStaffReplyStatus` = 1
                                                    OR `IsNewTicketStatus` = 1');
            $lockedTicketStatus = '';
            $customerReplyStatus = '';
            $defaultStaffReplyStatus = '';
            $newTicketStatus = '';
            while ($row = hesk_dbFetchAssoc($changeStatusRs)) {
                if ($row['LockedTicketStatus']) {
                    $lockedTicketStatus = $row['id'];
                } elseif ($row['IsCustomerReplyStatus']) {
                    $customerReplyStatus = $row['id'];
                } elseif ($row['IsDefaultStaffReplyStatus']) {
                    $defaultStaffReplyStatus = $row['id'];
                } elseif ($row['IsNewTicketStatus']) {
                    $newTicketStatus = $row['id'];
                }
            }

            /* Reply deleted. Need to update status and last replier? */
            $res = hesk_dbQuery("SELECT `dt`, `staffid` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` WHERE `replyto`='" . intval($ticket['id']) . "' ORDER BY `id` DESC LIMIT 1");
            if (hesk_dbNumRows($res)) {
                $replier_id = hesk_dbResult($res, 0, 1);
                $last_replier = $replier_id ? 1 : 0;

                /* Change status? */
                $status_sql = '';
                if ($last_reply_id == $n) {
                    $status = $ticket['locked'] ? $lockedTicketStatus : ($last_replier ? $defaultStaffReplyStatus : $customerReplyStatus);
                    $status_sql = " , `status`='" . intval($status) . "' ";

                    // Update closedat and closedby columns as required
                    if ($status == $lockedTicketStatus) {
                        $closed_sql = " , `closedat`=NOW(), `closedby`=" . intval($_SESSION['id']) . " ";
                    }
                }

                hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `lastchange`=NOW(), `lastreplier`='{$last_replier}', `replierid`='" . intval($replier_id) . "', `replies`=`replies`-1 $status_sql $closed_sql $staffreplies_sql WHERE `id`='" . intval($ticket['id']) . "'");
            } else {
                // Update status, closedat and closedby columns as required
                if ($ticket['locked']) {
                    $status = $lockedTicketStatus;
                    $closed_sql = " , `closedat`=NOW(), `closedby`=" . intval($_SESSION['id']) . " ";
                } else {
                    $status = $newTicketStatus;
                    $closed_sql = " , `closedat`=NULL, `closedby`=NULL ";
                }

                hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `lastchange`=NOW(), `lastreplier`='0', `status`='$status', `replies`=0 $staffreplies_sql WHERE `id`='" . intval($ticket['id']) . "'");
            }

            hesk_process_messages($hesklang['repl'], $_SERVER['PHP_SELF'], 'SUCCESS');
        }
    } else {
        hesk_process_messages($hesklang['repl0'], $_SERVER['PHP_SELF']);
    }
}

/* Delete notes action */
if (isset($_GET['delnote']) && hesk_token_check()) {
    $n = intval(hesk_GET('delnote'));
    if ($n) {
        // Get note info
        $res = hesk_dbQuery("SELECT `who`, `attachments` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` WHERE `id`={$n}");

        if (hesk_dbNumRows($res)) {
            $note = hesk_dbFetchAssoc($res);

            // Permission to delete note?
            if ($can_del_notes || $note['who'] == $_SESSION['id']) {
                // Delete note
                hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` WHERE `id`='" . intval($n) . "'");

                // Delete attachments
                if (strlen($note['attachments'])) {
                    $hesk_settings['server_path'] = dirname(dirname(__FILE__));

                    $attachments = array();

                    $att = explode(',', substr($note['attachments'], 0, -1));
                    foreach ($att as $myatt) {
                        list($att_id, $att_name) = explode('#', $myatt);
                        $attachments[] = intval($att_id);
                    }

                    if (count($attachments)) {
                        $res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` WHERE `att_id` IN (" . implode(',', $attachments) . ") ");
                        while ($file = hesk_dbFetchAssoc($res)) {
                            hesk_unlink($hesk_settings['server_path'] . '/' . $hesk_settings['attach_dir'] . '/' . $file['saved_name']);
                        }
                        hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` WHERE `att_id` IN (" . implode(',', $attachments) . ") ");
                    }
                }
            }
        }
    }

    header('Location: admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999));
    exit();
}

/* Add a note action */
if (isset($_POST['notemsg']) && hesk_token_check('POST')) {
    // Error buffer
    $hesk_error_buffer = array();

    // Get message
    $msg = hesk_input(hesk_POST('notemsg'));

    // Get attachments
    if ($hesk_settings['attachments']['use']) {
        require(HESK_PATH . 'inc/posting_functions.inc.php');
        require(HESK_PATH . 'inc/htmLawed.php');
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

    // We need message and/or attachments to accept note
    if (count($attachments) || strlen($msg) || count($hesk_error_buffer)) {
        // Any errors?
        if (count($hesk_error_buffer) != 0) {
            $_SESSION['note_message'] = hesk_POST('notemsg');

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

        // Process attachments
        if ($hesk_settings['attachments']['use'] && !empty($attachments)) {
            foreach ($attachments as $myatt) {
                hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` (`ticket_id`,`saved_name`,`real_name`,`size`,`type`) VALUES ('" . hesk_dbEscape($trackingID) . "','" . hesk_dbEscape($myatt['saved_name']) . "','" . hesk_dbEscape($myatt['real_name']) . "','" . intval($myatt['size']) . "', '1')");
                $myattachments .= hesk_dbInsertID() . '#' . $myatt['real_name'] . '#' . $myatt['saved_name'] . ',';
            }
        }

        // Add note to database
        $msg = nl2br(hesk_makeURL($msg));
        hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` (`ticket`,`who`,`dt`,`message`,`attachments`) VALUES ('" . intval($ticket['id']) . "','" . intval($_SESSION['id']) . "',NOW(),'" . hesk_dbEscape($msg) . "','" . hesk_dbEscape($myattachments) . "')");

        /* Notify assigned staff that a note has been added if needed */
        $users = hesk_dbQuery("SELECT `email`, `notify_note` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE (`id`='" . intval($ticket['owner']) . "' OR (`isadmin` = '1' AND `notify_note_unassigned` = '1')) AND `id` <> '" . intval($_SESSION['id']) . "'");

        if (hesk_dbNumRows($users) > 0) {
            // 1. Generate the array with ticket info that can be used in emails
            $info = array(
                'email' => $ticket['email'],
                'category' => $ticket['category'],
                'priority' => $ticket['priority'],
                'owner' => $ticket['owner'],
                'trackid' => $ticket['trackid'],
                'status' => $ticket['status'],
                'name' => $_SESSION['name'],
                'lastreplier' => $ticket['lastreplier'],
                'subject' => $ticket['subject'],
                'message' => stripslashes($msg),
                'dt' => hesk_date($ticket['dt'], true),
                'lastchange' => hesk_date($ticket['lastchange'], true),
                'attachments' => $myattachments,
                'id' => $ticket['id'],
            );

            // 2. Add custom fields to the array
            foreach ($hesk_settings['custom_fields'] as $k => $v) {
                $info[$k] = $v['use'] ? $ticket[$k] : '';
            }

            // 3. Make sure all values are properly formatted for email
            $ticket = hesk_ticketToPlain($info, 1, 0);

            /* Get email functions */
            require(HESK_PATH . 'inc/email_functions.inc.php');

            /* Format email subject and message for staff */
            $subject = hesk_getEmailSubject('new_note', $ticket);
            $message = hesk_getEmailMessage('new_note', $ticket, $modsForHesk_settings, 1);
            $htmlMessage = hesk_getHtmlMessage('new_note', $ticket, $modsForHesk_settings, 1);
            $hasMessage = hesk_doesTemplateHaveTag('new_note', '%%MESSAGE%%', $modsForHesk_settings);


            /* Send email to staff */
            while ($user = hesk_dbFetchAssoc($users)) {
                hesk_mail($user['email'], $subject, $message, $htmlMessage, $modsForHesk_settings, array(), array(), $hasMessage);
            }
        }
    }
    header('Location: admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999));
    exit();
}

/* Update time worked */
if ($hesk_settings['time_worked'] && ($can_reply || $can_edit) && isset($_POST['h']) && isset($_POST['m']) && isset($_POST['s']) && hesk_token_check('POST')) {
    $h = intval(hesk_POST('h'));
    $m = intval(hesk_POST('m'));
    $s = intval(hesk_POST('s'));

    /* Get time worked in proper format */
    $time_worked = hesk_getTime($h . ':' . $m . ':' . $s);

    /* Update database */
    $revision = sprintf($hesklang['thist14'], hesk_date(), $time_worked, $_SESSION['name'] . ' (' . $_SESSION['user'] . ')');
    hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `time_worked`='" . hesk_dbEscape($time_worked) . "', `history`=CONCAT(`history`,'" . hesk_dbEscape($revision) . "') WHERE `trackid`='" . hesk_dbEscape($trackingID) . "'");

    /* Show ticket */
    hesk_process_messages($hesklang['twu'], 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999), 'SUCCESS');
}

/* Add child action */
if (($can_reply || $can_edit) && isset($_POST['childTrackingId'])) {
    //-- Make sure this isn't the same ticket or one of its merged tickets.
    $mergedTickets = hesk_dbQuery('SELECT * FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'tickets` WHERE `trackid` =
        \'' . hesk_dbEscape($trackingID) . '\' AND `merged` LIKE \'%#' . hesk_dbEscape($_POST['childTrackingId']) . '#%\'');
    if ($_POST['childTrackingId'] == $trackingID || hesk_dbNumRows($mergedTickets) > 0) {
        hesk_process_messages($hesklang['cannot_link_ticket_to_itself'], 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999));
    }

    //-- Does the child exist?
    $existRs = hesk_dbQuery('SELECT * FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'tickets` WHERE `trackid` = \'' . hesk_dbEscape($_POST['childTrackingId']) . '\'');
    if (hesk_dbNumRows($existRs) == 0) {
        //-- Maybe it was merged?
        $existRs = hesk_dbQuery('SELECT `trackid` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'tickets` WHERE `merged` LIKE \'#' . hesk_dbEscape($_POST['childTrackingId']) . '#\'');
        if (hesk_dbNumRows($existRs) > 0) {
            //-- Yes, it was merged. Set the child to the "new" ticket; not the merged one.
            $exist = hesk_dbFetchAssoc($existRs);
            $_POST['childTrackingId'] = $exist['trackid'];
        } else {
            hesk_process_messages(sprintf($hesklang['linked_ticket_does_not_exist'], $_POST['childTrackingId']), 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999));
        }
    }

    //-- Check if the ticket is already a child.
    $childRs = hesk_dbQuery('SELECT * FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'tickets` WHERE `parent` = ' . intval($ticket['id']) . ' AND `trackid` = \'' . hesk_dbEscape(hesk_POST('childTrackingId')) . '\'');
    if (hesk_dbNumRows($childRs) > 0) {
        hesk_process_messages(sprintf($hesklang['is_already_linked'], $_POST['childTrackingId']), 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999), 'NOTICE');
    }

    hesk_dbQuery('UPDATE `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'tickets` SET `parent` = ' . intval($ticket['id']) . ' WHERE `trackid` = \'' . hesk_dbEscape(hesk_POST('childTrackingId')) . '\'');
    hesk_process_messages(sprintf($hesklang['link_added'], $_POST['childTrackingId']), 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999), 'SUCCESS');
}

/* Delete child action */
if (($can_reply || $can_edit) && isset($_GET['deleteChild'])) {
    //-- Delete the relationship
    hesk_dbQuery('UPDATE `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'tickets` SET `parent` = NULL WHERE `ID` = ' . hesk_dbEscape($_GET['deleteChild']));
    hesk_process_messages($hesklang['ticket_no_longer_linked'], 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999), 'SUCCESS');

} elseif (($can_reply || $can_edit) && isset($_GET['deleteParent'])) {
    //-- Delete the relationship
    hesk_dbQuery('UPDATE `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'tickets` SET `parent` = NULL WHERE `ID` = ' . hesk_dbEscape($ticket['id']));
    hesk_process_messages($hesklang['ticket_no_longer_linked'], 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999), 'SUCCESS');
}

/* Delete attachment action */
if (isset($_GET['delatt']) && hesk_token_check()) {
    if (!$can_delete || !$can_edit) {
        hesk_process_messages($hesklang['no_permission'], 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999));
    }

    $att_id = intval(hesk_GET('delatt')) or hesk_error($hesklang['inv_att_id']);

    $reply = intval(hesk_GET('reply', 0));
    if ($reply < 1) {
        $reply = 0;
    }

    $note = intval(hesk_GET('note', 0));
    if ($note < 1) {
        $note = 0;
    }

    /* Get attachment info */
    $res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` WHERE `att_id`='" . intval($att_id) . "' LIMIT 1");
    if (hesk_dbNumRows($res) != 1) {
        hesk_process_messages($hesklang['id_not_valid'] . ' (att_id)', 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999));
    }
    $att = hesk_dbFetchAssoc($res);

    /* Is ticket ID valid for this attachment? */
    if ($att['ticket_id'] != $trackingID) {
        hesk_process_messages($hesklang['trackID_not_found'], 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999));
    }

    /* Delete file from server */
    hesk_unlink(HESK_PATH . $hesk_settings['attach_dir'] . '/' . $att['saved_name']);

    /* Delete attachment from database */
    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` WHERE `att_id`='" . intval($att_id) . "'");

    /* Update ticket or reply in the database */
    $revision = sprintf($hesklang['thist12'], hesk_date(), $att['real_name'], $_SESSION['name'] . ' (' . $_SESSION['user'] . ')');
    if ($reply) {
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` SET `attachments`=REPLACE(`attachments`,'" . hesk_dbEscape($att_id . '#' . $att['real_name'] . '#' . $att['saved_name']) . ",','') WHERE `id`='" . intval($reply) . "'");
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `history`=CONCAT(`history`,'" . hesk_dbEscape($revision) . "') WHERE `id`='" . intval($ticket['id']) . "'");
    } elseif ($note) {
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` SET `attachments`=REPLACE(`attachments`,'" . hesk_dbEscape($att_id . '#' . $att['real_name'] . '#' . $att['saved_name']) . ",','') WHERE `id`={$note} LIMIT 1");
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` SET `attachments`=REPLACE(`attachments`,'" . hesk_dbEscape($att_id . '#' . $att['real_name']) . ",','') WHERE `id`={$note}");
    } else {
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `attachments`=REPLACE(`attachments`,'" . hesk_dbEscape($att_id . '#' . $att['real_name'] . '#' . $att['saved_name']) . ",','') WHERE `id`='" . intval($ticket['id']) . "'");
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `attachments`=REPLACE(`attachments`,'" . hesk_dbEscape($att_id . '#' . $att['real_name']) . ",',''), `history`=CONCAT(`history`,'" . hesk_dbEscape($revision) . "') WHERE `id`='" . intval($ticket['id']) . "'");
    }

    hesk_process_messages($hesklang['kb_att_rem'], 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999), 'SUCCESS');
}

//-- Update location action
if (isset($_POST['latitude']) && isset($_POST['longitude'])) {
    hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `latitude` = '" . hesk_dbEscape($_POST['latitude']) . "',
        `longitude` = '" . hesk_dbEscape($_POST['longitude']) . "' WHERE `ID` = " . intval($ticket['id']));

    //redirect
    hesk_process_messages($hesklang['ticket_location_updated'], 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999), 'SUCCESS');
}

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* List of categories */
$orderBy = $modsForHesk_settings['category_order_column'];
if ($can_change_cat) {
    $result = hesk_dbQuery("SELECT `id`,`name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` WHERE `usage` <> 2 ORDER BY `cat_order` ASC");
} else {
    $result = hesk_dbQuery("SELECT `id`,`name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` WHERE `usage` <> 2 AND ".hesk_myCategories('id')." ORDER BY `cat_order` ASC");
}
$categories_options = '';
while ($row = hesk_dbFetchAssoc($result)) {
    $selected = '';
    if ($row['id'] == $ticket['category']) {
        $selected = 'selected';
    }
    $categories_options .= '<option value="' . $row['id'] . '" ' . $selected . '>' . $row['name'] . '</option>';
}

/* List of users */
$admins = array();
$result = hesk_dbQuery("SELECT `id`,`name`,`isadmin`,`categories`,`heskprivileges` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `active` = '1' ORDER BY `name` ASC");
while ($row = hesk_dbFetchAssoc($result)) {
    /* Is this an administrator? */
    if ($row['isadmin']) {
        $admins[$row['id']] = $row['name'];
        continue;
    }

    /* Not admin, is user allowed to view tickets? */
    if (strpos($row['heskprivileges'], 'can_view_tickets') !== false) {
        /* Is user allowed to access this category? */
        $cat = substr($row['categories'], 0);
        $row['categories'] = explode(',', $cat);
        if (in_array($ticket['category'], $row['categories'])) {
            $admins[$row['id']] = $row['name'];
            continue;
        }
    }
}

/* Get replies */
if ($ticket['replies']) {
    $reply = '';
    $result = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` WHERE `replyto`='" . intval($ticket['id']) . "' ORDER BY `id` " . ($hesk_settings['new_top'] ? 'DESC' : 'ASC'));
} else {
    $reply = false;
}

// Demo mode
if (defined('HESK_DEMO')) {
    $ticket['email'] = 'hidden@demo.com';
    $ticket['ip'] = '127.0.0.1';
}

// If an email address is tied to this ticket, check if there are any others
$recentTickets = NULL;
if ($ticket['email'] != '') {
    $recentTicketsSql = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets`
    WHERE `email` = '" . hesk_dbEscape($ticket['email']) . "' AND `trackid` <> '" . hesk_dbEscape($trackingID) . "' ORDER BY `lastchange` DESC LIMIT 5");
    while ($recentRow = hesk_dbFetchAssoc($recentTicketsSql)) {
        if ($recentTickets === NULL) {
            $recentTickets = array();
        }
        array_push($recentTickets, $recentRow);
    }

    if ($recentTickets !== NULL) {
        $recentTicketsWithStatuses = array();
        foreach ($recentTickets as $recentTicket) {
            $newRecentTicket = $recentTicket;
            $thisTicketStatusRS = hesk_dbQuery("SELECT `ID`, `TextColor` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` WHERE `ID` = " . intval($recentTicket['status']));
            $theStatusRow = hesk_dbFetchAssoc($thisTicketStatusRS);
            $newRecentTicket['statusText'] = mfh_getDisplayTextForStatusId($theStatusRow['ID']);
            $newRecentTicket['statusColor'] = $theStatusRow['TextColor'];
            array_push($recentTicketsWithStatuses, $newRecentTicket);
        }
        $recentTickets = $recentTicketsWithStatuses;
    }
}

/* Print admin navigation */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>
    <aside class="main-sidebar">
        <section class="sidebar" style="height: auto">
            <ul class="sidebar-menu">
                <li class="header text-uppercase"><?php echo $hesklang['information']; ?></li>
                <li>
                    <div class="ticket-info">
                        <span><?php echo $hesklang['trackID']; ?></span>
                        <br>
                        <b>
                            <?php

                            $tmp = '';
                            if ($hesk_settings['sequential']) {
                                $tmp = '<br> (' . $hesklang['seqid'] . ': ' . $ticket['id'] . ')';
                            }

                            echo $trackingID . $tmp; ?>
                        </b>
                    </div>
                </li>
                <?php if ($ticket['language'] !== NULL): ?>
                <li>
                    <div class="ticket-info">
                        <span><?php echo $hesklang['lgs']; ?></span>
                        <br><b><?php echo $ticket['language']; ?></b>
                    </div>
                </li>
                <?php endif; ?>
                <li>
                    <div class="ticket-info">
                        <span><?php echo $hesklang['replies']; ?></span>
                        <br><b><?php echo $ticket['replies']; ?></b>
                    </div>
                </li>
                <li>
                    <div class="ticket-info">
                        <span><?php echo $hesklang['created_on']; ?></span>
                        <br><b><?php echo hesk_date($ticket['dt'], true); ?></b>
                    </div>
                </li>
                <li>
                    <div class="ticket-info">
                        <span><?php echo $hesklang['last_update']; ?></span>
                        <br><b><?php echo hesk_date($ticket['lastchange'], true); ?></b>
                    </div>
                </li>
                <li>
                    <div class="ticket-info">
                        <span><?php echo $hesklang['due_date']; ?></span>
                        <br>
                        <div id="readonly-due-date">
                            <b>
                                <span id="due-date">
                                    <?php
                                    $due_date = $hesklang['none'];
                                    if ($ticket['due_date'] != null) {
                                        $due_date = hesk_date($ticket['due_date'], false, true, false);
                                        $due_date = date('Y-m-d', $due_date);
                                    }
                                    echo $due_date;
                                    ?>
                                </span>
                            </b><br>
                            <button class="btn btn-default btn-sm" id="change-button">
                                <?php echo $hesklang['chg']; ?>
                            </button>
                        </div>
                        <div id="editable-due-date" style="display: none">
                            <span class="form-group">
                                <input title="due-date" type="text" class="form-control datepicker" name="due-date" value="<?php echo $due_date == $hesklang['none'] ? '' : $due_date; ?>">
                                <?php echo $hesklang['clear_for_no_due_date']; ?>
                            </span>
                            <span class="btn-group">
                                <button id="submit" class="btn btn-primary"><?php echo $hesklang['save']; ?></button>
                                <button id="cancel" class="btn btn-default"><?php echo $hesklang['cancel']; ?></button>
                            </span>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="ticket-info">
                        <span><?php echo $hesklang['last_replier']; ?></span>
                        <br><b><?php echo $ticket['repliername']; ?></b>
                    </div>
                </li>
                <li>
                    <div class="ticket-info">
                        <span><?php echo $hesklang['ts']; ?></span>
                        <br>
                        <b>
                            <span>
                                <?php echo $ticket['time_worked']; ?>
                            </span>
                        </b><br>
                        <?php if ($can_reply || $can_edit): ?>
                            <button class="btn btn-default btn-sm" data-toggle="modal" data-target="#timeworkedform">
                                <?php echo $hesklang['chg']; ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </li>
                <li>
                    <div class="ticket-info">
                        <span><?php echo $hesklang['linked_tickets']; ?></span>
                        <br>
                        <b>
                            <?php
                            if ($ticket['parent'] != null) {
                                //-- Get the tracking ID of the parent
                                $parentRs = hesk_dbQuery('SELECT `trackid` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'tickets`
                                WHERE `ID` = ' . intval($ticket['parent']));
                                $parent = hesk_dbFetchAssoc($parentRs);
                                echo '<a href="admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999) . '&deleteParent=true">
                                <i class="fa fa-times-circle" data-toggle="tooltip" data-placement="top" title="' . $hesklang['delete_relationship'] . '"></i></a>';
                                echo '&nbsp;<a href="admin_ticket.php?track=' . $parent['trackid'] . '&Refresh=' . mt_rand(10000, 99999) . '">' . $parent['trackid'] . '</a>';
                            }
                            //-- Check if any tickets have a parent set to this tracking ID
                            $hasRows = false;
                            $childrenRS = hesk_dbQuery('SELECT * FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'tickets`
                        WHERE `parent` = ' . intval($ticket['id']));
                            while ($row = hesk_dbFetchAssoc($childrenRS)) {
                                $hasRows = true;
                                echo '<a href="admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999) . '&deleteChild=' . $row['id'] . '">
                            <i class="fa fa-times-circle font-icon red" data-toggle="tooltip" data-placement="top" title="' . $hesklang['unlink'] . '"></i></a>';
                                echo '&nbsp;<a href="admin_ticket.php?track=' . $row['trackid'] . '&Refresh=' . mt_rand(10000, 99999) . '">' . $row['trackid'] . '</a>';
                                echo '<br>';
                            }
                            if (!$hasRows && $ticket['parent'] == null) {
                                echo $hesklang['none'];
                            }
                            ?>
                        </b>
                        <?php
                        if ($can_reply || $can_edit) {
                            ?>
                            <div id="addChildText">
                                <?php echo '<button class="btn btn-default btn-sm" onclick="toggleChildrenForm(true)"><i class="fa fa-plus-circle"></i> ' . $hesklang['add_ticket'] . '</a>'; ?>
                            </div>
                            <div id="childrenForm" style="display: none">
                                <form action="admin_ticket.php" method="post" data-toggle="validator">
                                    <div class="form-group">
                                        <label for="childTrackingId" class="control-label">
                                            <?php echo $hesklang['trackID']; ?>
                                        </label>
                                        <input type="text" name="childTrackingId" class="form-control input-sm"
                                               placeholder="<?php echo htmlspecialchars($hesklang['trackID']); ?>"
                                               data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                               required>
                                        <div class="help-block with-errors"></div>
                                    </div>
                                    <div class="btn-group">
                                        <input type="submit" class="btn btn-primary btn-sm"
                                               value="<?php echo $hesklang['save']; ?>">
                                        <button class="btn btn-default btn-sm" onclick="toggleChildrenForm(false); return false;">
                                            <?php echo $hesklang['cancel']; ?>
                                        </button>
                                    </div>
                                    <input type="hidden" name="track" value="<?php echo $trackingID; ?>"/>
                                    <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>"/>
                                </form>
                            </div>
                        <?php } ?>
                    </div>
                </li>
                <li>
                    <div class="ticket-info">
                        <span><?php echo $hesklang['recent_tickets']; ?></span>
                        <br>
                        <?php if ($recentTickets === NULL): ?>
                            <p style="margin: 0"><b><?php echo $hesklang['none']; ?></b></p>
                            <?php
                        else:
                            foreach ($recentTickets as $recentTicket):
                                ?>
                                <p style="margin: 0"><b>
                                    <i class="fa fa-circle" data-toggle="tooltip" data-placement="top"
                                       style="color: <?php echo $recentTicket['statusColor']; ?>"
                                       title="<?php echo sprintf($hesklang['current_status_colon'], $recentTicket['statusText']); ?>"></i>
                                    <?php echo '<a href="admin_ticket.php?track=' . $recentTicket['trackid'] . '&amp;Refresh=' . mt_rand(10000, 99999) . '">' . $recentTicket['trackid'] . '</a>'; ?>
                                </b></p>
                                <?php
                            endforeach;
                        endif;
                        ?>
                    </div>
                </li>
            </ul>
        </section>
    </aside>
    <div class="modal fade" tabindex="-1" role="dialog" id="timeworkedform">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo $hesklang['ts']; ?></h4>
                </div>
                <div class="modal-body">
                    <?php $t = hesk_getHHMMSS($ticket['time_worked']); ?>
                    <form data-toggle="validator" class="form-horizontal" method="post" action="admin_ticket.php" style="margin:0px; padding:0px;">
                        <div class="form-group">
                            <label for="h" class="col-sm-4 control-label"><?php echo $hesklang['hh']; ?></label>
                            <div class="col-sm-8">
                                <input type="text" name="h" value="<?php echo $t[0]; ?>" size="3"
                                       data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                       placeholder="<?php echo htmlspecialchars($hesklang['hh']); ?>"
                                       class="form-control input-sm" required>
                            </div>
                            <div class="col-sm-12 text-right">
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>
                        <div class="form-group input-group-sm">
                            <label for="m" class="col-sm-4 control-label"><?php echo $hesklang['mm']; ?></label>
                            <div class="col-sm-8">
                                <input type="text" name="m" value="<?php echo $t[1]; ?>" size="3"
                                       data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                       placeholder="<?php echo htmlspecialchars($hesklang['mm']); ?>"
                                       class="form-control input-sm" required>
                            </div>
                            <div class="col-sm-12 text-right">
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>
                        <div class="form-group input-group-sm">
                            <label for="s" class="col-sm-4 control-label"><?php echo $hesklang['ss']; ?></label>
                            <div class="col-sm-8">
                                <input type="text" name="s" value="<?php echo $t[2]; ?>" size="3"
                                       data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                       placeholder="<?php echo htmlspecialchars($hesklang['ss']); ?>"
                                       class="form-control input-sm" required>
                            </div>
                            <div class="col-sm-12 text-right">
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="btn-group btn-group-sm text-right">
                                <input class="btn btn-primary" type="submit" value="<?php echo $hesklang['save']; ?>" />
                                <a class="btn btn-default" data-dismiss="modal"><?php echo $hesklang['cancel']; ?></a>
                            </div>
                        </div>
                        <input type="hidden" name="track" value="<?php echo $trackingID; ?>" />
                        <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>" />
                    </form>
                </div>
            </div>
        </div>
    </div>
<div class="content-wrapper">
    <section class="content">
    <?php
    /* This will handle error, success and notice messages */
    hesk_handle_messages();

    // Prepare special custom fields
    foreach ($hesk_settings['custom_fields'] as $k=>$v) {
        if ($v['use'] && hesk_is_custom_field_in_category($k, $ticket['category']) ) {
            switch ($v['type']) {
                case 'date':
                    $ticket[$k] = hesk_custom_date_display_format($ticket[$k], $v['value']['date_format']);
                    break;
            }
        }
    }
    ?>
    <h1><?php echo $hesklang['ticket_details']; ?></h1>
    <div class="box">
        <div class="box-header">
            <h1 class="box-title">
                <?php
                echo $ticket['subject'];
                if ($ticket['archive']) {
                    echo ' <span class="label label-primary"><i class="fa fa-tag"></i> ' . $hesklang['archived'] . '</span>';
                }
                if ($ticket['locked']) {
                    echo ' <span class="label label-primary"><i class="fa fa-lock"></i> ' . $hesklang['loc'] . '</span>';
                }
                ?>
            </h1>
            <div class="pull-right">
                <?php echo hesk_getAdminButtons($category['id']);


                // Only output JavaScript if we have coordinates
                if (strpos($ticket['latitude'], 'E') === false):
                    ?>
                    <script>
                        var latitude = '';
                        latitude = <?php echo $ticket['latitude'] != '' ? $ticket['latitude'] : -1; ?>;
                        var longitude = '';
                        longitude = <?php echo $ticket['longitude'] != '' ? $ticket['longitude'] : -1; ?>;
                        $('#more-modal').on('shown.bs.modal', function() {
                            initializeMapForStaff(latitude, longitude, "<?php echo $hesklang['users_location']; ?>");
                        });
                    </script>
                    <?php
                endif;
                ?>
            </div>
        </div>
        <div class="table-bordered status-row">
                <div class="row no-margins med-low-priority">
                    <?php

                    $priorityLanguages = array(
                        0 => $hesklang['critical'],
                        1 => $hesklang['high'],
                        2 => $hesklang['medium'],
                        3 => $hesklang['low']
                    );
                    $options = array();
                    for ($i = 0; $i < 4; $i++) {
                        if ($ticket['priority'] == $i) {
                            if ($i === 0) {
                                $cssClass = 'critical-priority';
                            } elseif ($i === 1) {
                                $cssClass = 'high-priority';
                            } elseif ($i === 2) {
                                $cssClass = 'medium-priority';
                            } else {
                                $cssClass = 'low-priority';
                            }
                        }

                        $selected = $ticket['priority'] == $i ? 'selected' : '';
                        $content = "<i class='fa fa-fw fa-%s %s' style='font-size: 1em'></i> {$priorityLanguages[$i]}";

                        if ($i === 0) {
                            $content = sprintf($content, 'long-arrow-up', 'critical');
                        } elseif ($i === 1) {
                            $content = sprintf($content, 'angle-double-up', 'orange');
                        } elseif ($i === 2) {
                            $content = sprintf($content, 'angle-double-down', 'green');
                        } else {
                            $content = sprintf($content, 'long-arrow-down', 'blue');
                        }

                        array_push($options, '<option data-content="' . $content . '" value="' . $i . '" ' . $selected . '>' . $priorityLanguages[$i] . '</option>');
                    }

                    echo '<div class="ticket-cell-admin col-md-3 col-sm-12 ' . $cssClass . '">';

                    echo '<p class="ticket-property-title">' . $hesklang['priority'] . '</p>';

                    echo '<form style="margin-bottom:0;" id="changePriorityForm" action="priority.php" method="post">

                    <span style="white-space:nowrap;">
                    <select class="selectpicker form-control" name="priority" onchange="document.getElementById(\'changePriorityForm\').submit();">';
                    echo implode('', $options);
                    echo '
                    </select>

                    <input type="submit" style="display: none" value="' . $hesklang['go'] . '" /><input type="hidden" name="track" value="' . $trackingID . '" />
                    <input type="hidden" name="token" value="' . hesk_token_echo(0) . '" />';
                    if ($isManager) {
                        echo '<input type="hidden" name="isManager" value="1">';
                    }
                    echo '</span>

                    </form>

                   </div>';

                    echo '<div class="col-md-3 col-sm-12 ticket-cell-admin"><p class="ticket-property-title">' . $hesklang['status'] . '</p>';
                    $status_options = array();
                    $results = mfh_getAllStatuses();
                    foreach ($results as $row) {
                        $selected = $ticket['status'] == $row['ID'] ? 'selected' : '';
                        $status_options[$row['ID']] = '<option style="color: ' . $row['TextColor'] . '" value="' . $row['ID'] . '" ' . $selected . '>' . mfh_getDisplayTextForStatusId($row['ID']) . '</option>';
                    }

                    echo '
                    <form role="form" id="changeStatusForm" style="margin-bottom:0;" action="change_status.php" method="post">
                        <span style="white-space:nowrap;">
                            <select class="selectpicker form-control" onchange="document.getElementById(\'changeStatusForm\').submit();" name="s">
                                ' . implode('', $status_options) . '
                            </select>

                            <input type="submit" style="display:none;" value="' . $hesklang['go'] . '" class="btn btn-default" /><input type="hidden" name="track" value="' . $trackingID . '" />
                            <input type="hidden" name="token" value="' . hesk_token_echo(0) . '" />';
                    if ($isManager) {
                        echo '<input type="hidden" name="isManager" value="1">';
                    }
                    echo '</span>
                    </form>
                    </div>';
                    echo '<div class="col-md-3 col-sm-12 ticket-cell-admin"><p class="ticket-property-title">' . $hesklang['owner'] . '</p>';

                    if (hesk_checkPermission('can_assign_others', 0) || $isManager) {
                        echo '
                            <form style="margin-bottom:0;" id="changeOwnerForm" action="assign_owner.php" method="post">
                            <span style="white-space:nowrap;">
                            <select class="selectpicker form-control"  name="owner" onchange="document.getElementById(\'changeOwnerForm\').submit();">';
                        $selectedForUnassign = 'selected';
                        foreach ($admins as $k => $v) {
                            $selected = '';
                            if ($k == $ticket['owner']) {
                                $selectedForUnassign = '';
                                $selected = 'selected';
                            }
                            echo '<option value="' . $k . '" ' . $selected . '>' . $v . '</option>';
                        }
                        echo '<option value="-1" ' . $selectedForUnassign . '> &gt; ' . $hesklang['unas'] . ' &lt; </option>';
                        echo '</select>
                            <input type="submit" style="display: none" value="' . $hesklang['go'] . '">
                            <input type="hidden" name="track" value="' . $trackingID . '">
                            <input type="hidden" name="token" value="' . hesk_token_echo(0) . '">
                            </span>';
                        if ( ! $ticket['owner'])
                        {
                            echo '<input type="hidden" name="unassigned" value="1">';
                        }
                        echo '</form>';
                    } else {
                        echo '<p class="ticket-property-text">';
                        echo isset($admins[$ticket['owner']]) ? $admins[$ticket['owner']] :
                            ($can_assign_self ? $hesklang['unas'] . ' [<a href="assign_owner.php?track=' . $trackingID . '&amp;owner=' . $_SESSION['id'] . '&amp;token=' . hesk_token_echo(0) . '&amp;unassigned=1">' . $hesklang['asss'] . '</a>]' : $hesklang['unas']);
                        echo '</p>';
                    }
                    echo '</div>';
                    echo '<div class="col-md-3 col-sm-12 ticket-cell-admin"><p class="ticket-property-title">' . $hesklang['category'] . '</p>';
                    if (strlen($categories_options) && ($can_change_cat || $can_change_own_cat)) {
                        echo '

                        <form style="margin-bottom:0;" id="changeCategory" action="move_category.php" method="post">

                            <span style="white-space:nowrap;">
                            <select name="category" class="selectpicker form-control" onchange="document.getElementById(\'changeCategory\').submit();">
                            ' . $categories_options . '
                            </select>

                            <input type="submit" style="display: none" value="' . $hesklang['go'] . '">
                            <input type="hidden" name="track" value="' . $trackingID . '">
                            <input type="hidden" name="token" value="' . hesk_token_echo(0) . '">
                            </span>

                        </form>';
                    } else {
                        echo '<p class="ticket-property-text">' . $category['name'] . '</p>';
                    }
                    echo '</div>';
                    ?>
                </div>
        </div>
    </div>
    <div class="box box-warning">
        <div class="box-header with-border">
            <h1 class="box-title">
                <?php echo $hesklang['notes']; ?>
            </h1>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <?php
            $res = hesk_dbQuery("SELECT t1.*, t2.`name` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` AS t1 LEFT JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` AS t2 ON t1.`who` = t2.`id` WHERE `ticket`='" . intval($ticket['id']) . "' ORDER BY t1.`id` " . ($hesk_settings['new_top'] ? 'DESC' : 'ASC'));
            if (hesk_dbNumRows($res) > 0):
                $first = true;
                while ($note = hesk_dbFetchAssoc($res)):
                    if (!$first) {
                        echo '<hr>';
                    } else {
                        $first = false;
                    }
            ?>
            <div class="row">
                <div class="col-md-8">
                    <p><i><?php echo $hesklang['noteby']; ?>
                            <b><?php echo($note['name'] ? $note['name'] : $hesklang['e_udel']); ?></b></i>
                        - <?php echo hesk_date($note['dt'], true); ?></p>
                    <?php
                    // Message
                    echo $note['message'];

                    // Attachments
                    if ($hesk_settings['attachments']['use'] && strlen($note['attachments'])) {
                        echo strlen($note['message']) ? '<br><br>' : '';

                        $att = explode(',', substr($note['attachments'], 0, -1));
                        $num = count($att);
                        foreach ($att as $myatt) {
                            list($att_id, $att_name) = explode('#', $myatt);

                            // Can edit and delete note (attachments)?
                            if ($can_del_notes || $note['who'] == $_SESSION['id']) {
                                // If this is the last attachment and no message, show "delete ticket" link
                                if ($num == 1 && strlen($note['message']) == 0) {
                                    echo '<a href="admin_ticket.php?delnote=' . $note['id'] . '&amp;track=' . $trackingID . '&amp;Refresh=' . mt_rand(10000, 99999) . '&amp;token=' . hesk_token_echo(0) . '" onclick="return hesk_confirmExecute(\'' . hesk_makeJsString($hesklang['pda']) . '\');">
                                                <i class="fa fa-times" style="font-size:16px;color:red;" data-toggle="tooltip" data-placement="top" data-original-title="' . $hesklang['dela'] . '"></i>
                                            </a> ';
                                } // Show "delete attachment" link
                                else {
                                    echo '<a href="admin_ticket.php?delatt=' . $att_id . '&amp;note=' . $note['id'] . '&amp;track=' . $trackingID . '&amp;Refresh=' . mt_rand(10000, 99999) . '&amp;token=' . hesk_token_echo(0) . '" onclick="return hesk_confirmExecute(\'' . hesk_makeJsString($hesklang['pda']) . '\');">
                                                <i class="fa fa-times" style="font-size:16px;color:red;" data-toggle="tooltip" data-placement="top" data-original-title="' . $hesklang['dela'] . '"></i>
                                            </a> ';
                                }
                            }

                            echo '
                                    <a href="../download_attachment.php?att_id=' . $att_id . '&amp;track=' . $trackingID . '">
                                        <i class="fa fa-paperclip" style="font-size:16px;" data-toggle="tooltip" data-placement="top" data-original-title="' . $hesklang['dnl'] . ' ' . $att_name . '"></i>
                                    </a>
                                    <a href="../download_attachment.php?att_id=' . $att_id . '&amp;track=' . $trackingID . '">' . $att_name . '</a><br />
                                ';
                        }
                    }
                    ?>
                </div>
                <div class="col-md-4 text-right">
                    <?php if ($can_del_notes || $note['who'] == $_SESSION['id']) { ?>
                        <a href="edit_note.php?track=<?php echo $trackingID; ?>&amp;Refresh=<?php echo mt_rand(10000, 99999); ?>&amp;note=<?php echo $note['id']; ?>&amp;token=<?php hesk_token_echo(); ?>">
                            <i class="fa fa-pencil icon-link orange" data-toggle="tooltip" title="<?php echo $hesklang['ednote']; ?>"></i>
                        </a>&nbsp;
                        <a href="admin_ticket.php?track=<?php echo $trackingID; ?>&amp;Refresh=<?php echo mt_rand(10000, 99999); ?>&amp;delnote=<?php echo $note['id']; ?>&amp;token=<?php hesk_token_echo(); ?>">
                            <i class="fa fa-times icon-link red" data-toggle="tooltip" title="<?php echo $hesklang['delnote']; ?>"></i>
                        </a>
                    <?php } ?>
                </div>
            </div>
            <?php
                endwhile;
            else:
                ?>
                <?php echo $hesklang['no_notes_for_this_ticket']; ?>
            <?php endif; ?>
            <div id="noteform" style="display: none">
                <h3><?php echo $hesklang['addnote']; ?></h3>
                <form class="form-horizontal" data-toggle="validator" method="post" action="admin_ticket.php" style="margin:0px; padding:0px;"
                      enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="note-message" class="control-label col-sm-2"><?php echo $hesklang['message']; ?></label>
                        <div class="col-sm-10">
                                <textarea id="note-message" style="min-height: 150px" data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']) ?>" class="form-control" name="notemsg" rows="6"
                                          cols="60" required><?php echo isset($_SESSION['note_message']) ? stripslashes(hesk_input($_SESSION['note_message'])) : ''; ?></textarea>
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="note-attachments" class="control-label col-sm-2">
                            <?php echo $hesklang['attachments']; ?>
                        </label>
                        <div class="col-sm-10">
                            <?php build_dropzone_markup(true, 'notesFiledrop'); ?>
                        </div>
                    </div>
                    <?php display_dropzone_field(HESK_PATH . 'internal-api/ticket/upload-attachment.php', 'notesFiledrop'); ?>
                    <div class="text-right">
                        <i><?php echo $hesklang['nhid']; ?></i>&nbsp;
                        <div class="btn-group">
                            <button type="button" class="btn btn-danger" data-show="note-footer" data-hide="noteform"><?php echo $hesklang['cancel']; ?></button>
                            <input type="submit" class="btn btn-success" value="<?php echo $hesklang['s']; ?>">
                        </div>
                        <input type="hidden" name="track" value="<?php echo $trackingID; ?>">
                        <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>">
                    </div>
                </form>
            </div>
        </div>
        <?php if ($can_reply): ?>
        <div class="box-footer" id="note-footer">
            <button class="btn btn-default pull-right" data-show="noteform" data-hide="note-footer">
                <i class="fa fa-plus-circle"></i> <?php echo $hesklang['addnote']; ?>
            </button>
        </div>
        <?php endif; ?>
    </div>
    <?php
    /* Do we need or have any canned responses? */
    $can_options = hesk_printCanned();

    /* Reply form on top? */
    if ($can_reply && $hesk_settings['reply_top'] == 1) {
        hesk_printReplyForm();
    }

    hesk_printTicketReplies();

    echo '<br>';

    /* Reply form on bottom? */
    if ($can_reply && !$hesk_settings['reply_top']) {
        hesk_printReplyForm();
    }

    /* Display ticket history */
    if (strlen($ticket['history'])) {
        ?>
        <div class="box">
            <div class="box-header with-border">
                <h1 class="box-title">
                    <?php echo $hesklang['thist']; ?>
                </h1>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <ul>
                    <?php echo $ticket['history']; ?>
                </ul>
            </div>
        </div>
    <?php }
    ?>
</section>
</div>
<div style="display: none">
    <p id="lang_ticket_due_date_updated"><?php echo $hesklang['ticket_due_date_updated']; ?></p>
    <p id="lang_none"><?php echo $hesklang['none']; ?></p>
    <p id="lang_error_updating_ticket_due_date"><?php echo $hesklang['error_updating_ticket_due_date']; ?></p>
</div>
<?php


/* Clear unneeded session variables */
hesk_cleanSessionVars('ticket_message');
hesk_cleanSessionVars('time_worked');
hesk_cleanSessionVars('note_message');

require_once(HESK_PATH . 'inc/footer.inc.php');


/*** START FUNCTIONS ***/

function hesk_getAdminButtons($category_id)
{
    global $hesk_settings, $hesklang, $modsForHesk_settings, $ticket, $reply, $trackingID, $can_edit, $can_archive, $can_delete, $can_resolve, $isManager;

    $options = '';

    /* Edit post */
    if ($can_edit) {
        $tmp = $reply ? '&amp;reply=' . $reply['id'] : '';
        $mgr = $isManager ? '&amp;isManager=true' : '';
        $options .= '<a class="btn btn-default" href="edit_post.php?track=' . $trackingID . $tmp . $mgr . '"><i class="fa fa-pencil orange"></i> ' . $hesklang['edit'] . '</a> ';
    }


    /* Print ticket button */
    $options .= '<a class="btn btn-default" href="../print.php?track=' . $trackingID . '"><i class="fa fa-print"></i> ' . $hesklang['printer_friendly'] . '</a> ';

    /* Copy ticket button */
    $strippedName = strip_tags($ticket['name']);
    $strippedEmail = strip_tags($ticket['email']);
    $linkText = 'new_ticket.php?name=' . $strippedName . '&email=' . $strippedEmail . '&catid=' . $category_id . '&priority=' . $ticket['priority'];
    foreach ($hesk_settings['custom_fields'] as $k => $v) {
        if ($v['use'] == 1) {

            if ($v['type'] == 'checkbox') {
                $value = str_replace('<br />', '-CHECKBOX-', $ticket[$k]);
            } else {
                $value = $ticket[$k];
            }
            $strippedCustomField = strip_tags($value);
            $linkText .= '&c_' . $k . '=' . $strippedCustomField;
        }
    }
    $options .= '<a class="btn btn-default" href="' . $linkText . '"><i class="fa fa-copy fa-fw"></i> ' . $hesklang['copy_ticket'] . '</a> ';

    /* Close/Reopen ticket link */
    $random = rand(10000, 99999);

    $statusSql = 'SELECT `ID`, `IsStaffClosedOption`, `IsStaffReopenedStatus` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'statuses` WHERE `IsStaffClosedOption` = 1 OR `IsStaffReopenedStatus` = 1';
    $statusRs = hesk_dbQuery($statusSql);
    $staffClosedOptionStatus = array();
    $staffReopenedStatus = array();
    while ($statusRow = hesk_dbFetchAssoc($statusRs)) {
        if ($statusRow['IsStaffReopenedStatus'] == 1) {
            $staffReopenedStatus['ID'] = $statusRow['ID'];
        } else {
            $staffClosedOptionStatus['ID'] = $statusRow['ID'];
        }
    }

    $isTicketClosedSql = 'SELECT `IsClosed`, `Closable` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'statuses` WHERE `ID` = ' . intval($ticket['status']);
    $isTicketClosedRs = hesk_dbQuery($isTicketClosedSql);
    $isTicketClosedRow = hesk_dbFetchAssoc($isTicketClosedRs);
    $isTicketClosed = $isTicketClosedRow['IsClosed'];
    $isClosable = $isTicketClosedRow['Closable'] == 'yes' || $isTicketClosedRow['Closable'] == 'sonly';

    $mgr = $isManager ? '&amp;isManager=1' : '';
    if ($isTicketClosed == 0 && $isClosable && $can_resolve) // Ticket is still open
    {
        $options .= '<a class="btn btn-default" href="change_status.php?track=' . $trackingID . $mgr . '&amp;s=' . $staffClosedOptionStatus['ID'] . '&amp;Refresh=' . $random . '&amp;token=' . hesk_token_echo(0) . '">
                    <i class="fa fa-check-circle green"></i> ' . $hesklang['close_action'] . '</a> ';
    } elseif ($isTicketClosed == 1) {
        $options .= '<a class="btn btn-default" href="change_status.php?track=' . $trackingID . $mgr . '&amp;s=' . $staffReopenedStatus['ID'] . '&amp;Refresh=' . $random . '&amp;token=' . hesk_token_echo(0) . '">
                    <i class="fa fa-folder-open-o green"></i> ' . $hesklang['open_action'] . '</a> ';
    }

    $dropdown = '
    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#more-modal">
        <i class="fa fa-ellipsis-h"></i> ' . hesk_htmlspecialchars($hesklang['more']) . ' 
    </button>
    <div class="modal fade" id="more-modal" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title">
                        <button type="button" class="close cancel-callback" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4>' . hesk_htmlspecialchars($hesklang['more']) . '</h4>
                    </div>
                </div>
                <div class="modal-body">
            ';

    // Location and UA
    if ($modsForHesk_settings['display_user_agent_information']
        && $ticket['user_agent'] !== NULL
        && $ticket['screen_resolution_height'] !== NULL
        && $ticket['screen_resolution_height'] != 0
        && $ticket['screen_resolution_width'] !== NULL
        && $ticket['screen_resolution_width'] != 0
    ) {
        $dropdown .=
            '<div class="panel panel-default">
                <div class="panel-heading">
                    <h4 style="text-transform: capitalize"><i class="fa fa-desktop fa-fw"></i> '.$hesklang['device_information'].'</h4>
                </div>
                '.buildUserAgentBody($ticket['user_agent'], $ticket['screen_resolution_width'], $ticket['screen_resolution_height']).'
            </div>';
    } else if ($modsForHesk_settings['display_user_agent_information']) {
        $dropdown .=
            '<div class="panel panel-default">
                <div class="panel-heading">
                    <h4 style="text-transform: capitalize"><i class="fa fa-desktop fa-fw"></i> '.$hesklang['device_information'].'</h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-12">
                            '.$hesklang['no_device_information'].'
                        </div>
                    </div>
                </div>
            </div>';
    }
        ?>
        <?php
    if ($modsForHesk_settings['request_location']) {
        $hasLocation = true;
        if (strpos($ticket['latitude'], 'E') === false) {
            $locationText = $hesklang['click_for_map'];
        } else {
            $hasLocation = false;
            $locationText = $hesklang['users_location'];
        }
        $dropdown .= '<div class="panel panel-default">
                <div class="panel-heading">
                    <h4><i class="fa fa-map-marker fa-fw"></i> ' . $locationText . '</h4>
                </div>';
        if ($hasLocation):
            $dropdown .= '<div id="map" style="height: 500px"></div><br><div class="panel-body">
            <address id="friendly-location" style="font-size: 13px"></address>
            <p id="save-for-address"
               style="font-size: 13px;display:none">' . $hesklang['save_to_see_updated_address'] . '</p>
            <script>
                getFriendlyLocation(' . $ticket['latitude'] . ',
                    ' . $ticket['longitude'] . ');
            </script>
            <div class="row">
                <form action="admin_ticket.php" method="post" role="form">
                    <input type="hidden" name="track"
                           value="'. $trackingID . '">
                    <input type="hidden" name="token"
                           value="'. hesk_token_echo(0) . '">
                    <input type="hidden" name="latitude" id="latitude"
                           value="'. $ticket['latitude'] . '">
                    <input type="hidden" name="longitude" id="longitude"
                           value="'. $ticket['longitude'] . '">

                    <div class="col-sm-12">
                        <div class="btn-group" style="display: none" id="save-group">
                            <input type="submit" class="btn btn-success"
                                   value="'.  $hesklang['save_location'] . '">
                            <button type="button" class="btn btn-default" onclick="closeAndReset('. $ticket['latitude'] . ', '. $ticket['longitude'] . ')">' . $hesklang['reset'] . '</button>
                        </div>
                    </div>
                </form>
            </div>';
        else:
            $dropdown .= '<div class="panel-body">';
            $errorCode = explode('-', $ticket['latitude']);
            $key = 'location_unavailable_' . $errorCode[1];
            $dropdown .= '<h5>' . $hesklang[$key] . '</h5>';
        endif;

        $dropdown .= '</div></div>';
    }

    $dropdown .= '<div class="row">';

    /* Lock ticket button */
    if ($can_resolve) {
        $template =
            '<div class="col-md-6 col-sm-12"><a class="button-link" href="lock.php?track=' . $trackingID . '&amp;locked=%s&amp;Refresh=' . mt_rand(10000, 99999) . '&amp;token=' . hesk_token_echo(0) . '">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h4>
                            <i class="fa fa-%s fa-fw"></i> %s
                        </h4>
                    </div>
                </div>
            </a></div>';
        $dropdown .= $ticket['locked']
            ? sprintf($template, 0, 'unlock', $hesklang['tul'])
            : sprintf($template, 1, 'lock', $hesklang['tlo']);
    }

    /* Tag ticket button */
    if ($can_archive) {
        $template =
            '<div class="col-md-6 col-sm-12"><a class="button-link" href="archive.php?track=' . $trackingID . '&amp;archived=%s&amp;Refresh=' . mt_rand(10000, 99999) . '&amp;token=' . hesk_token_echo(0) . '">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h4>
                            <i class="fa fa-tag fa-fw"></i> %s
                        </h4>
                    </div>
                </div>
            </a></div>';

        $dropdown .= $ticket['archive']
            ? sprintf($template, 0, $hesklang['remove_archive'])
            : sprintf($template, 1, $hesklang['add_archive']);
    }

    /* Import to knowledgebase button */
    if ($hesk_settings['kb_enable'] && hesk_checkPermission('can_man_kb', 0)) {
        $dropdown .=
            '<div class="col-md-6 col-sm-12"><a href="manage_knowledgebase.php?a=import_article&amp;track=' . $trackingID . '" class="button-link">
                <div class="panel panel-default">
                        <div class="panel-body">
                            <h4>
                                <i class="fa fa-lightbulb-o fa-fw"></i> ' . $hesklang['import_kb'] . '
                            </h4>
                        </div>
                    </div>
                </a></div>';
    }

    /* Delete ticket */
    if ($can_delete) {
        if ($reply) {
            $url = 'admin_ticket.php';
            $tmp = 'delete_post=' . $reply['id'];
            $txt = $hesklang['delt'];
        } else {
            $url = 'delete_tickets.php';
            $tmp = 'delete_ticket=1';
            $txt = $hesklang['dele'];
        }
        $dropdown .=
            '<div class="col-md-6 col-sm-12"><a class="button-link" href="' . $url . '?track=' . $trackingID . '&amp;' . $tmp . '&amp;Refresh=' . mt_rand(10000, 99999) . '&amp;token=' . hesk_token_echo(0) . '" onclick="return hesk_confirmExecute(\'' . hesk_makeJsString($txt) . '?\');">
                <div class="panel panel-default">
                        <div class="panel-body danger">
                            <h4>
                                <i class="fa fa-fw fa-times"></i> ' . $txt . '
                            </h4>
                        </div>
                    </div>
                </a></div>';
    }
    $dropdown .= '</div></div></div></div></div> ';
    $options .= $dropdown;

    /* Return generated HTML */
    return $options;

} // END hesk_getAdminButtons()

function hesk_getAdminButtonsInTicket($reply = 0, $white = 1)
{
    global $hesk_settings, $hesklang, $ticket, $reply, $trackingID, $can_edit, $can_archive, $can_delete, $isManager;

    $options = $reply ? '' : '<div class="pull-right">';

    // Resend email notification
    $replyDataAttribute = '';
    if ($reply) {
        $replyDataAttribute = 'data-reply-id="' . $reply['id'] . '"';
    }

    if ($ticket['email'] !== '') {
        $options .= '
        <button class="btn btn-default" data-action="resend-email-notification" ' . $replyDataAttribute . ' data-ticket-id="' . $ticket['id'] . '">
            <i class="fa fa-envelope navy-blue"></i> ' . $hesklang['resend_email_notification'] . '
        </button>
        <span id="lang_email_notification_sent" style="display: none">' . $hesklang['email_notification_sent'] . '</span>
        <span id="lang_email_notification_resend_failed" style="display: none">' . $hesklang['email_notification_resend_failed'] . '</span>
        ';
    }

    /* Edit post */
    if ($can_edit) {
        $tmp = $reply ? '&amp;reply=' . $reply['id'] : '';
        $mgr = $isManager ? '&amp;isManager=true' : '';
        $options .= '<a class="btn btn-default" href="edit_post.php?track=' . $trackingID . $tmp . $mgr . '"><i class="fa fa-pencil orange"></i> ' . $hesklang['edtt'] . '</a> ';
    }


    /* Delete ticket */
    if ($can_delete) {
        if ($reply) {
            $url = 'admin_ticket.php';
            $tmp = 'delete_post=' . $reply['id'];
            $txt = $hesklang['delt'];
        } else {
            $url = 'delete_tickets.php';
            $tmp = 'delete_ticket=1';
            $txt = $hesklang['dele'];
        }
        $options .= '<a class="btn btn-default" href="' . $url . '?track=' . $trackingID . '&amp;' . $tmp . '&amp;Refresh=' . mt_rand(10000, 99999) . '&amp;token=' . hesk_token_echo(0) . '" onclick="return hesk_confirmExecute(\'' . $txt . '?\');"><i class="fa fa-times red"></i> ' . $txt . '</a> ';
    }

    /* Return generated HTML */
    $options .= $reply ? '' : '</div>';
    return $options;

} // END hesk_getAdminButtonsInTicket()


function print_form()
{
    global $hesk_settings, $hesklang;
    global $trackingID;

    /* Print header */
    require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

    /* Print admin navigation */
    require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');

    /* This will handle error, success and notice messages */
    hesk_handle_messages();
    ?>
    <div class="row">
        <div class="col-sm-10 col-sm-offset-1">
            <h3 align="left"><?php echo $hesklang['view_existing']; ?></a></h3>

            <form data-toggle="validator" action="admin_ticket.php" method="get" class="form-horizontal">
                <div class="form-group">
                    <label for="track" class="control-label col-sm-3"><?php echo $hesklang['ticket_trackID']; ?></label>
                    <div class="col-sm-9">
                        <input type="text" name="track" maxlength="20" size="35" value="<?php echo $trackingID; ?>"
                               data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                               placeholder="<?php echo $hesklang['ticket_trackID']; ?>" class="form-control" required>
                        <div class="help-block with-errors"></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-3">
                        <input type="submit" value="<?php echo $hesklang['view_ticket']; ?>" class="btn btn-default">
                        <input type="hidden" name="Refresh" value="<?php echo rand(10000, 99999); ?>">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
    require_once(HESK_PATH . 'inc/footer.inc.php');
    exit();
} // End print_form()

function mfh_print_message() {
    global $ticket, $hesklang, $hesk_settings, $can_ban_emails, $can_ban_ips, $trackingID, $modsForHesk_settings;
    ?>
    <li><i class="fa fa-comment bg-red" data-toggle="tooltip" title="<?php echo $hesklang['original_message']; ?>"></i>
        <div class="timeline-item">
            <span class="time"><i class="fa fa-clock-o"></i> <?php echo hesk_date($ticket['dt'], true); ?></span>
            <h3 class="timeline-header">
                <i class="fa fa-fw fa-user" data-toggle="tooltip" title="<?php echo $hesklang['customer']; ?>"></i>
                <?php echo $ticket['name']; ?>
                <?php if ($ticket['email'] !== ''): ?>
                    <br>
                    <i class="fa fa-fw fa-envelope" data-toggle="tooltip" title="<?php echo $hesklang['email']; ?>"></i>
                    <a href="mailto:<?php echo $ticket['email']; ?>"><?php echo $ticket['email']; ?></a>
                    <?php
                    if ($can_ban_emails && !empty($ticket['email'])) {
                        if ($email_id = hesk_isBannedEmail($ticket['email'])) {
                            if ($can_unban_emails) {
                                echo '<a href="banned_emails.php?a=unban&amp;track=' . $trackingID . '&amp;id=' . intval($email_id) . '&amp;token=' . hesk_token_echo(0) . '">
                                <i class="fa fa-ban icon-link red gray-on-hover" data-toggle="tooltip" data-placement="top" data-original-title="' . $hesklang['eisban'] . ' ' . $hesklang['click_unban'] . '"></i>
                            </a> ';
                            } else {
                                echo '<i class="fa fa-ban icon-link red" data-toggle="tooltip" data-placement="top" data-original-title="' . $hesklang['eisban'] . '"></i>';
                            }
                        } else {
                            echo '<a href="banned_emails.php?a=ban&amp;track=' . $trackingID . '&amp;email=' . urlencode($ticket['email']) . '&amp;token=' . hesk_token_echo(0) . '">
                            <i class="fa fa-ban icon-link gray red-on-hover" data-toggle="tooltip" data-placement="top" data-original-title="' . $hesklang['savebanemail'] . '"></i>
                        </a> ';
                        }
                    }
                endif; ?>
                <br> <i class="fa fa-fw fa-globe" data-toggle="tooltip" title="<?php echo $hesklang['ip']; ?>"></i>
                <?php
                // Format IP for lookup
                if ($ticket['ip'] == '' || $ticket['ip'] == 'Unknown' || $ticket['ip'] == $hesklang['unknown']) {
                    echo $hesklang['unknown'];
                } else {
                    echo '<a href="../ip_whois.php?ip=' . urlencode($ticket['ip']) . '">' . $ticket['ip'] . '</a>';

                    if ($can_ban_ips) {
                        if ($ip_id = hesk_isBannedIP($ticket['ip'])) {
                            if ($can_unban_ips) {
                                echo '<a href="banned_ips.php?a=unban&amp;track=' . $trackingID . '&amp;id=' . intval($ip_id) . '&amp;token=' . hesk_token_echo(0) . '">
                                        <i class="fa fa-ban red icon-link gray-on-hover" data-toggle="tooltip" data-placement="top" data-original-title="' . $hesklang['ipisban'] . ' ' . $hesklang['click_unban'] . '"></i>
                                    </a> ';
                            } else {
                                echo '<i class="fa fa-ban icon-link red" data-toggle="tooltip" data-placement="top" data-original-title="' . $hesklang['ipisban'] . '"></i>';
                            }
                        } else {
                            echo '<a href="banned_ips.php?a=ban&amp;track=' . $trackingID . '&amp;ip=' . urlencode($ticket['ip']) . '&amp;token=' . hesk_token_echo(0) . '">
                                    <i class="fa fa-ban gray icon-link red-on-hover" data-toggle="tooltip" data-placement="top" data-original-title="' . $hesklang['savebanip'] . '"></i>
                                </a> ';
                        }
                    }
                }
                ?>
            </h3>
            <div class="timeline-header header-info">
                <?php
                foreach ($hesk_settings['custom_fields'] as $k => $v) {
                    if ($v['use'] && $v['place'] == 0 && hesk_is_custom_field_in_category($k, $ticket['category'])) {
                        echo '<div class="row">';
                        echo '<div class="col-md-3 text-right"><strong>' . $v['name'] . ':</strong></div>';
                        if ($v['type'] == 'email') {
                            echo '<div class="col-md-9"><a href="mailto:'.$ticket[$k].'">'.$ticket[$k].'</a></div>';
                        } else {
                            echo '<div class="col-md-9">' . $ticket[$k] . '</div>';
                        }
                        echo '</div>';
                    }
                }
                ?>
            </div>
            <div class="timeline-body">
                <div class="row">
                    <div class="col-md-3 text-right">
                        <strong><?php echo $hesklang['message_colon']; ?></strong>
                    </div>
                    <div class="col-md-9">
                        <?php
                        if ($ticket['message'] != '') {
                            if ($ticket['html']) {
                                echo hesk_html_entity_decode($ticket['message']);
                            } else {
                                echo $ticket['message'];
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php
            $first = true;
            foreach ($hesk_settings['custom_fields'] as $k => $v) {
                if ($v['use'] && $v['place'] && hesk_is_custom_field_in_category($k, $ticket['category'])) {
                    if ($first) {
                        echo '<div class="timeline-footer">';
                        $first = false;
                    }
                    echo '<div class="row">';
                    echo '<div class="col-md-3 text-right"><strong>' . $v['name'] . ':</strong></div>';
                    if ($v['type'] == 'email') {
                        echo '<div class="col-md-9"><a href="mailto:'.$ticket[$k].'">'.$ticket[$k].'</a></div>';
                    } else {
                        echo '<div class="col-md-9">' . $ticket[$k] . '</div>';
                    }
                    echo '</div>';
                }
            }
            if (!$first) {
                echo '</div>';
            }
            ?>
            <?php if (($hesk_settings['attachments']['use'] && strlen($ticket['attachments']))
                || ($hesk_settings['kb_enable'] && $hesk_settings['kb_recommendanswers'] && strlen($ticket['articles']))): ?>
                <div class="timeline-footer">
                    <?php
                    /* Attachments */
                    mfh_listAttachments($ticket['attachments'], 0, true);

                    // Show suggested KB articles
                    if ($hesk_settings['kb_enable'] && $hesk_settings['kb_recommendanswers'] && strlen($ticket['articles'])) {
                        $suggested = array();
                        $suggested_list = '';

                        // Get article info from the database
                        $articles = hesk_dbQuery("SELECT `id`,`subject` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_articles` WHERE `id` IN (" . preg_replace('/[^0-9\,]/', '', $ticket['articles']) . ")");
                        while ($article = hesk_dbFetchAssoc($articles)) {
                            $suggested[$article['id']] = '<a href="../knowledgebase.php?article=' . $article['id'] . '">' . $article['subject'] . '</a><br />';
                        }

                        // Loop through the IDs to preserve the order they were suggested in
                        $articles = explode(',', $ticket['articles']);
                        foreach ($articles as $article) {
                            if (isset($suggested[$article])) {
                                $suggested_list .= $suggested[$article];
                            }
                        }

                        // Finally print suggested articles
                        if (strlen($suggested_list)) {
                            $suggested_list = '<hr /><i>' . $hesklang['taws'] . '</i><br />' . $suggested_list . '&nbsp;';
                            echo $_SESSION['show_suggested'] ? $suggested_list : '<a href="Javascript:void(0)" onclick="Javascript:hesk_toggleLayerDisplay(\'suggested_articles\')">' . $hesklang['sska'] . '</a><span id="suggested_articles" style="display:none">' . $suggested_list . '</span>';
                        }
                    }
                    ?>
                </div>
            <?php endif; ?>
            <div class="timeline-footer ticket-message-bottom">
                <div class="row">
                    <div class="col-md-12 text-right">
                        <?php echo hesk_getAdminButtonsInTicket(0); ?>
                    </div>
                </div>
            </div>
        </div>
    </li>
<?php
}


function hesk_printTicketReplies()
{
    global $hesklang, $hesk_settings, $result, $reply;

    echo '<ul class="timeline">';
    if (!$hesk_settings['new_top']) {
        mfh_print_message();
    } else {
        echo '<li class="today-top"><i class="fa fa-clock-o bg-gray" data-toggle="tooltip" title="' . $hesklang['timeline_today'] . '"></i></li>';
    }

    while ($reply = hesk_dbFetchAssoc($result)) {
        $reply['dt'] = hesk_date($reply['dt'], true);
        ?>
        <li>
            <?php if ($reply['staffid']): ?>
                <i class="fa fa-reply bg-orange" data-toggle="tooltip" title="<?php echo $hesklang['reply_by_staff']; ?>"></i>
            <?php else: ?>
                <i class="fa fa-share bg-blue" data-toggle="tooltip" title="<?php echo $hesklang['reply_by_customer']; ?>"></i>
            <?php endif; ?>
            <div class="timeline-item">
                <span class="time"><i class="fa fa-clock-o"></i> <?php echo $reply['dt']; ?></span>
                <h3 class="timeline-header"><?php echo $reply['name']; ?></h3>
                <div class="timeline-body">
                    <div class="row">
                        <div class="col-md-3 text-right">
                            <strong><?php echo $hesklang['message_colon']; ?></strong>
                        </div>
                        <div class="col-md-9">
                            <?php
                            if ($reply['html']) {
                                echo hesk_html_entity_decode($reply['message']);
                            } else {
                                echo $reply['message'];
                            } ?>
                        </div>
                    </div>
                </div>
                <?php
                if ($hesk_settings['attachments']['use'] && strlen($reply['attachments'])):
                ?>
                <div class="timeline-footer">
                    <?php mfh_listAttachments($reply['attachments'], $reply['id'], true); ?>
                </div>
                <?php endif; ?>
                <div class="timeline-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <?php
                            /* Staff rating */
                            if ($hesk_settings['rating'] && $reply['staffid']) {
                                if ($reply['rating'] == 1) {
                                    echo '<p class="rate">' . $hesklang['rnh'] . '</p>';
                                } elseif ($reply['rating'] == 5) {
                                    echo '<p class="rate">' . $hesklang['rh'] . '</p>';
                                }
                            }
                            /* Show "unread reply" message? */
                            if ($reply['staffid'] && !$reply['read']) {
                                echo '<p class="rate">' . $hesklang['unread'] . '</p>';
                            }
                            ?>
                        </div>
                        <div class="col-md-6 text-right">
                            <?php echo hesk_getAdminButtonsInTicket(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        <?php
    }

    if ($hesk_settings['new_top']) {
        mfh_print_message();
    } else {
        echo '<li><i class="fa fa-clock-o bg-gray" data-toggle="tooltip" title="' . $hesklang['timeline_today'] . '"></i></li>';
    }
    echo '</ul>';

    return;

} // End hesk_printTicketReplies()


function hesk_printReplyForm()
{
    global $hesklang, $hesk_settings, $ticket, $admins, $can_options, $can_resolve, $options, $can_assign_self, $modsForHesk_settings, $isManager;

    // Force assigning a ticket before allowing to reply?
    if ($hesk_settings['require_owner'] && ! $ticket['owner'])
    {
        hesk_show_notice($hesklang['atbr'].($can_assign_self ? '<br /><br /><a href="assign_owner.php?track='.$ticket['trackid'].'&amp;owner='.$_SESSION['id'].'&amp;token='.hesk_token_echo(0).'&amp;unassigned=1">'.$hesklang['attm'].'</a>' : ''), $hesklang['owneed']);
        return '';
    }
    ?>
    <!-- START REPLY FORM -->
    <?php if ($modsForHesk_settings['rich_text_for_tickets']): ?>
    <script type="text/javascript">
        /* <![CDATA[ */
        tinyMCE.init({
            mode: "textareas",
            editor_selector: "htmlEditor",
            elements: "content",
            theme: "advanced",
            convert_urls: false,
            plugins: "autolink",

            theme_advanced_buttons1: "cut,copy,paste,|,undo,redo,|,formatselect,fontselect,fontsizeselect,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull",
            theme_advanced_buttons2: "sub,sup,|,charmap,|,bullist,numlist,|,outdent,indent,insertdate,inserttime,preview,|,forecolor,backcolor,|,hr,removeformat,visualaid,|,link,unlink,anchor,image,cleanup,code",
            theme_advanced_buttons3: "",

            theme_advanced_toolbar_location: "top",
            theme_advanced_toolbar_align: "left",
            theme_advanced_statusbar_location: "bottom",
            theme_advanced_resizing: true
        });
        /* ]]> */
    </script>
    <?php endif; ?>

<div class="box">
    <div class="box-header with-border">
        <h1 class="box-title">
            <?php echo $hesklang['add_reply']; ?>
        </h1>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                <i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        <?php
        $onsubmit = 'onsubmit="force_stop();"';
        if ($modsForHesk_settings['rich_text_for_tickets']) {
            $onsubmit = 'onsubmit="force_stop();return validateRichText(\'message-help-block\', \'message-group\', \'message\', \''.htmlspecialchars($hesklang['this_field_is_required']).'\')"';
        }
        ?>
        <form id="reply-form" role="form" data-toggle="validator" class="form-horizontal" method="post" action="admin_reply_ticket.php"
              enctype="multipart/form-data" name="form1" <?php echo $onsubmit; ?>>
            <?php

            /* Ticket assigned to someone else? */
            if ($ticket['owner'] && $ticket['owner'] != $_SESSION['id'] && isset($admins[$ticket['owner']])) {
                hesk_show_notice($hesklang['nyt'] . ' ' . $admins[$ticket['owner']]);
            }

            /* Ticket locked? */
            if ($ticket['locked']) {
                hesk_show_notice($hesklang['tislock']);
            }

            // Track time worked?
            if ($hesk_settings['time_worked']) {
                ?>

                <div class="form-group">
                    <label for="time_worked" class="col-sm-3 control-label"><?php echo $hesklang['ts']; ?></label>

                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="time_worked" id="time_worked" size="10"
                               value="<?php echo(isset($_SESSION['time_worked']) ? hesk_getTime($_SESSION['time_worked']) : '00:00:00'); ?>"/>
                    </div>
                    <div class="col-sm-3 text-right">
                        <div class="btn-group">
                            <input type="button" class="btn btn-success" onclick="ss()" id="startb"
                                   value="<?php echo $hesklang['start']; ?>"/>
                            <input type="button" class="btn btn-danger" onclick="r()"
                                   value="<?php echo $hesklang['reset']; ?>"/>
                        </div>
                    </div>
                </div>
                <?php
            }
            /* Do we have any canned responses? */
            if (strlen($can_options)) {
                ?>
                <div class="form-group">
                    <label for="saved_replies" class="col-sm-3 control-label"><?php echo $hesklang['saved_replies']; ?></label>
                    <div class="col-sm-9">
                        <label><input type="radio" name="mode" id="modeadd" value="1"
                                      checked="checked"/> <?php echo $hesklang['madd']; ?></label><br/>
                        <label><input type="radio" name="mode" id="moderep" value="0"/> <?php echo $hesklang['mrep']; ?>
                        </label>
                        <select class="form-control" name="saved_replies" onchange="setMessage(this.value)">
                            <option value="0"> - <?php echo $hesklang['select_empty']; ?> -</option>
                            <?php echo $can_options; ?>
                        </select>
                    </div>
                </div>
                <?php
            }
            ?>
            <div class="form-group" id="message-group">
                <label for="message" class="col-sm-3 control-label"><?php echo $hesklang['message']; ?><span
                        class="important">*</span></label>

                <div class="col-sm-9">
                    <span id="HeskMsg">
                        <textarea class="form-control htmlEditor" name="message" id="message" rows="12"
                                  placeholder="<?php echo htmlspecialchars($hesklang['message']); ?>" cols="72"
                                  data-error="<?php echo htmlspecialchars($hesklang['enter_message']); ?>"
                                  required><?php

                            // Do we have any message stored in session?
                            if (isset($_SESSION['ticket_message'])) {
                                echo stripslashes(hesk_input($_SESSION['ticket_message']));
                            } // Perhaps a message stored in reply drafts?
                            else {
                                $res = hesk_dbQuery("SELECT `message` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "reply_drafts` WHERE `owner`=" . intval($_SESSION['id']) . " AND `ticket`=" . intval($ticket['id']) . " LIMIT 1");
                                if (hesk_dbNumRows($res) == 1) {
                                    echo hesk_dbResult($res);
                                }
                            }

                            ?></textarea>
                        <div class="help-block with-errors" id="message-help-block"></div></span>
                </div>
            </div>
            <?php
            /* attachments */
            if ($hesk_settings['attachments']['use']) {
                ?>
                <div class="form-group">
                    <label for="attachments" class="col-sm-3 control-label"><?php echo $hesklang['attachments']; ?>:</label>

                    <div class="col-sm-9">
                        <?php build_dropzone_markup(true); ?>
                    </div>
                </div>
                <?php
                display_dropzone_field(HESK_PATH . 'internal-api/ticket/upload-attachment.php');
            }
            ?>
            <div class="form-group">
                <label for="options" class="col-sm-3 control-label"><?php echo $hesklang['addop']; ?>:</label>

                <div class="col-sm-9">
                    <?php
                    if ($ticket['owner'] != $_SESSION['id'] && $can_assign_self) {
                        if (empty($ticket['owner'])) {
                            echo '<label><input type="checkbox" name="assign_self" value="1" checked="checked" /> <b>' . $hesklang['asss2'] . '</b></label><br />';
                        } else {
                            echo '<label><input type="checkbox" name="assign_self" value="1" /> ' . $hesklang['asss2'] . '</label><br />';
                        }
                    }

                    ?>
                    <div class="form-inline">
                        <label>
                            <input type="checkbox" name="set_priority"
                                   value="1"/> <?php echo $hesklang['change_priority']; ?>
                        </label>
                        <select class="form-control" name="priority">
                            <?php echo implode('', $options); ?>
                        </select>
                    </div>
                    <br/>
                    <label>
                        <input type="checkbox" name="signature" value="1"
                               checked="checked"/> <?php echo $hesklang['attach_sign']; ?>
                    </label>
                    (<a href="profile.php"><?php echo $hesklang['profile_settings']; ?></a>)
                    <br/>
                    <label>
                        <input type="checkbox" name="no_notify"
                               value="1" <?php echo ($_SESSION['notify_customer_reply'] && !empty($ticket['email'])) ? '' : 'checked="checked" '; ?> <?php if (empty($ticket['email'])) {
                            echo 'disabled';
                        } ?>> <?php echo $hesklang['dsen']; ?>
                    </label><br/><br/>
                    <?php if (empty($ticket['email'])) {
                        echo '<input type="hidden" name="no_notify" value="1">';
                    } ?>
                    <input type="hidden" name="orig_id" value="<?php echo $ticket['id']; ?>"/>
                    <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>"/>

                    <div class="btn-group dropup">
                        <input class="btn btn-primary" type="submit" value="<?php echo $hesklang['submit_reply']; ?>">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
                                aria-expanded="false">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li><a>
                                    <button class="dropdown-submit" type="submit" name="submit_as_customer">
                                        <?php echo $hesklang['sasc']; ?>
                                    </button>
                                </a></li>
                            <li class="divider"></li>
                            <?php
                            $statuses = mfh_getAllStatuses();
                            foreach ($statuses as $status) {
                                if ($status['IsClosed'] == '1' && !$can_resolve) {
                                    continue;
                                }

                                echo '<li><a>
                                        <button class="dropdown-submit" type="submit" name="submit_as_status" value="' . $status['ID'] . '"">
                                            ' . $hesklang['submit_reply'] . ' ' . $hesklang['and_change_status_to'] . ' <b>
                                            <span style="color:' . $status['TextColor'] . '">' . mfh_getDisplayTextForStatusId($status['ID']) . '</span></b>
                                        </button>
                                    </a></li>';
                            }
                            ?>
                        </ul>
                    </div>
                    <input class="btn btn-default" type="submit" name="save_reply" value="<?php echo $hesklang['sacl']; ?>">
                    <?php if ($isManager): ?>
                        <input type="hidden" name="isManager" value="1">
                    <?php endif; ?>
                </div>
            </div>
        </form>
        <script>$('form#reply-form').dirtyForms();</script>
    </div>
</div>
    <!-- END REPLY FORM -->
    <?php
} // End hesk_printReplyForm()


function hesk_printCanned()
{
    global $hesklang, $hesk_settings, $can_reply, $ticket, $modsForHesk_settings;

    /* Can user reply to tickets? */
    if (!$can_reply) {
        return '';
    }

    /* Get canned replies from the database */
    $res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "std_replies` ORDER BY `reply_order` ASC");

    /* If no canned replies return empty */
    if (!hesk_dbNumRows($res)) {
        return '';
    }

    /* We do have some replies, print the required Javascript and select field options */
    $can_options = '';
    ?>
    <script language="javascript" type="text/javascript"><!--
        // -->
        var myMsgTxt = new Array();
        myMsgTxt[0] = '';

        <?php
        while ($mysaved = hesk_dbFetchRow($res))
        {
            $can_options .= '<option value="' . $mysaved[0] . '">' . $mysaved[1]. "</option>\n";
            if ($modsForHesk_settings['rich_text_for_tickets']) {
                $theMessage = hesk_html_entity_decode($mysaved[2]);
                $theMessage = addslashes($theMessage);
                echo 'myMsgTxt['.$mysaved[0].']=\''.str_replace("\r\n","\\r\\n' + \r\n'", $theMessage)."';\n";
            } else {
                echo 'myMsgTxt['.$mysaved[0].']=\''.str_replace("\r\n","\\r\\n' + \r\n'", addslashes($mysaved[2]))."';\n";
            }
        }

        ?>

        function setMessage(msgid) {
            var isHtml = <?php echo hesk_jsString($modsForHesk_settings['rich_text_for_tickets']); ?>;

            var myMsg = myMsgTxt[msgid];

            if (myMsg == '') {
                if (document.form1.mode[1].checked) {
                    document.getElementById('message').value = '';
                }
                return true;
            }

            myMsg = myMsg.replace(/%%HESK_ID%%/g, '<?php echo hesk_jsString($ticket['id']); ?>');
            myMsg = myMsg.replace(/%%HESK_TRACKID%%/g, '<?php echo hesk_jsString($ticket['trackid']); ?>');
            myMsg = myMsg.replace(/%%HESK_TRACK_ID%%/g, '<?php echo hesk_jsString($ticket['trackid']); ?>');
            myMsg = myMsg.replace(/%%HESK_NAME%%/g, '<?php echo hesk_jsString($ticket['name']); ?>');
            myMsg = myMsg.replace(/%%HESK_EMAIL%%/g, '<?php echo hesk_jsString($ticket['email']); ?>');
            myMsg = myMsg.replace(/%%HESK_OWNER%%/g, '<?php echo hesk_jsString( isset($admins[$ticket['owner']]) ? $admins[$ticket['owner']] : ''); ?>');

            <?php
            for ($i=1; $i<=50; $i++) {
                echo 'myMsg = myMsg.replace(/%%HESK_custom'.$i.'%%/g, \''.hesk_jsString($ticket['custom'.$i]).'\');';
            }
            ?>

            if (document.getElementById) {
                if (document.getElementById('moderep').checked) {
                    if (isHtml) {
                        tinymce.get("message").setContent('');
                        tinymce.get("message").execCommand('mceInsertRawHTML', false, myMsg);
                    } else {
                        myMsg = $('<textarea />').html(myMsg).text();
                        $('#message').val(myMsg).trigger('input');
                    }
                }
                else {
                    if (isHtml) {
                        var oldMsg = tinymce.get("message").getContent();
                        tinymce.get("message").setContent('');
                        tinymce.get("message").execCommand('mceInsertRawHTML', false, oldMsg + myMsg);
                    } else {
                        var oldMsg = $('#message').val();
                        var newMsg = $('<textarea />').html(oldMsg + '\n' + myMsg).text();
                        $('#message').val(newMsg).trigger('input');
                    }
                }
            }
            else {
                if (document.form1.mode[0].checked) {
                    document.form1.message.value = myMsg;
                }
                else {
                    var oldMsg = document.form1.message.value;
                    document.form1.message.value = oldMsg + myMsg;
                }
            }

        }
        //-->
    </script>
    <?php

    /* Return options for select box */
    return $can_options;

} // End hesk_printCanned()

function buildUserAgentBody($user_agent, $width, $height) {
    global $hesklang;

    return '
    <script>
        var userAgent = platform.parse(\'' . addslashes($user_agent) . '\');
        console.log(userAgent);
        var screenResWidth = ' . intval($width) . ';
        var screenResHeight = ' . intval($height) . ';
    </script>
    <table class="table table-striped">
        <tbody>
        <tr>
            <td><strong>' . $hesklang['operating_system'] . '</strong>
            </td>
            <td id="operating-system">&nbsp;</td>
            <script>$(\'#operating-system\').html(userAgent.os.toString());</script>
        </tr>
        <tr>
            <td><strong>' . $hesklang['browser'] . '</strong></td>
            <td id="browser">&nbsp;</td>
            <script>$(\'#browser\').html(userAgent.name + \' \' + userAgent.version);</script>
        </tr>
        <tr>
            <td><strong>' . $hesklang['screen_resolution'] . '</strong>
            </td>
            <td id="screen-resolution">&nbsp;</td>
            <script>$(\'#screen-resolution\').html(screenResWidth + \' x \' + screenResHeight);</script>
        </tr>
        </tbody>
    </table>';
}
?>
