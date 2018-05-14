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

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();
$modsForHesk_settings = mfh_getSettings();

/* Set correct return URL */
if (isset($_SERVER['HTTP_REFERER'])) {
    $url = hesk_input($_SERVER['HTTP_REFERER']);
    $url = str_replace('&amp;', '&', $url);
    if ($tmp = strstr($url, 'show_tickets.php')) {
        $referer = $tmp;
    } elseif ($tmp = strstr($url, 'find_tickets.php')) {
        $referer = $tmp;
    } elseif ($tmp = strstr($url, 'admin_main.php')) {
        $referer = $tmp;
    } else {
        $referer = 'admin_main.php';
    }
} else {
    $referer = 'admin_main.php';
}

/* Is this a delete ticket request from within a ticket ("delete" icon)? */
if (isset($_GET['delete_ticket'])) {
    /* Check permissions for this feature */
    hesk_checkPermission('can_del_tickets');

    /* A security check */
    hesk_token_check();

    // Tracking ID
    $trackingID = hesk_cleanID() or die($hesklang['int_error'] . ': ' . $hesklang['no_trackID']);

    /* Get ticket info */
    $result = hesk_dbQuery("SELECT `id`,`trackid`,`category` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `trackid`='" . hesk_dbEscape($trackingID) . "' LIMIT 1");
    if (hesk_dbNumRows($result) != 1) {
        hesk_error($hesklang['ticket_not_found']);
    }
    $ticket = hesk_dbFetchAssoc($result);

    /* Is this user allowed to delete tickets inside this category? */
    hesk_okCategory($ticket['category']);

    hesk_fullyDeleteTicket();

    hesk_process_messages(sprintf($hesklang['num_tickets_deleted'], 1), $referer, 'SUCCESS');
}


/* This is a request from ticket list. Must be POST and id must be an array */
if (!isset($_POST['id']) || !is_array($_POST['id'])) {
    hesk_process_messages($hesklang['no_selected'], $referer, 'NOTICE');
} /* If not, then needs an action (a) POST variable set */
elseif (!isset($_POST['a'])) {
    hesk_process_messages($hesklang['invalid_action'], $referer);
}

$i = 0;

// Possible priorities
$priorities = array(
    'critical' => array('value' => 0, 'lang' => 'critical', 'text' => $hesklang['critical'], 'formatted' => '<font class="critical">' . $hesklang['critical'] . '</font>'),
    'high' => array('value' => 1, 'lang' => 'high', 'text' => $hesklang['high'], 'formatted' => '<font class="important">' . $hesklang['high'] . '</font>'),
    'medium' => array('value' => 2, 'lang' => 'medium', 'text' => $hesklang['medium'], 'formatted' => '<font class="medium">' . $hesklang['medium'] . '</font>'),
    'low' => array('value' => 3, 'lang' => 'low', 'text' => $hesklang['low'], 'formatted' => $hesklang['low']),
);

// Assign tickets to
if ( isset($_POST['assign']) && $_POST['assign'] == $hesklang['assi']) {
    if ( ! isset($_POST['owner']) || $_POST['owner'] == '') {
        hesk_process_messages($hesklang['assign_no'], $referer, 'NOTICE');
    }

    $end_message = array();
    $num_assigned = 0;

    // Permissions
    $can_assign_others = hesk_checkPermission('can_assign_others',0);
    if ($can_assign_others) {
        $can_assign_self = true;
    } else {
        $can_assign_self = hesk_checkPermission('can_assign_self',0);
    }

    $owner = intval( hesk_POST('owner') );

    if ($owner == -1) {
        foreach ($_POST['id'] as $this_id) {
            if (is_array($this_id)) {
                continue;
            }

            $this_id = intval($this_id) or hesk_error($hesklang['id_not_valid']);

            // TODO Should we reset the assignedby?
            $res = hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `owner`=0 WHERE `id`={$this_id} LIMIT 1");
            mfh_insert_audit_trail_record($this_id, 'TICKET', 'audit_assigned', hesk_date(), array(0 => $hesklang['unas'],
                1 => $_SESSION['name'].' ('.$_SESSION['user'].')'));

            $end_message[] = sprintf($hesklang['assign_2'], $this_id);
            $i++;
        }

        hesk_process_messages($hesklang['assign_1'],$referer,'SUCCESS');
    }

	$res = hesk_dbQuery("SELECT `id`,`user`,`name`,`email`,`isadmin`,`categories`,`notify_assigned` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `id`='{$owner}' LIMIT 1");
	$owner_data = hesk_dbFetchAssoc($res);

	if (!$owner_data['isadmin']) {
        $owner_data['categories']=explode(',',$owner_data['categories']);
    }

	require(HESK_PATH . 'inc/email_functions.inc.php');

	foreach ($_POST['id'] as $this_id) {
        if (is_array($this_id)) {
            continue;
        }

        $this_id = intval($this_id) or hesk_error($hesklang['id_not_valid']);

        $result = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `id`={$this_id} LIMIT 1");
        if (hesk_dbNumRows($result) != 1) {
            continue;
        }
		$ticket = hesk_dbFetchAssoc($result);

		if ($ticket['owner'] == $owner) {
            $end_message[] = sprintf($hesklang['assign_3'], $ticket['trackid'], $owner_data['name']);
            $i++;
            continue;
		}
		if ($owner_data['isadmin'] || in_array($ticket['category'],$owner_data['categories'])) {
		    // TODO Should we set the assignedby?
            hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `owner`={$owner} WHERE `id`={$this_id} LIMIT 1");
            mfh_insert_audit_trail_record($this_id, 'TICKET', 'audit_assigned', hesk_date(), array(0 => $owner_data['name'].' ('.$owner_data['user'].')',
                1 => $_SESSION['name'].' ('.$_SESSION['user'].')'));

            $end_message[] = sprintf($hesklang['assign_4'], $ticket['trackid'], $owner_data['name']);
            $num_assigned++;

            $ticket['owner'] = $owner;

            /* --> Prepare message */

            // 1. Generate the array with ticket info that can be used in emails
            $info = array(
                'email'			=> $ticket['email'],
                'category'		=> $ticket['category'],
                'priority'		=> $ticket['priority'],
                'owner'			=> $ticket['owner'],
                'trackid'		=> $ticket['trackid'],
                'status'		=> $ticket['status'],
                'name'			=> $ticket['name'],
                'subject'		=> $ticket['subject'],
                'message'		=> $ticket['message'],
                'attachments'	=> $ticket['attachments'],
                'dt'			=> hesk_date($ticket['dt'], true),
                'lastchange'	=> hesk_date($ticket['lastchange'], true),
                'id'			=> $ticket['id'],
                'time_worked'   => $ticket['time_worked'],
                'last_reply_by' => hesk_getReplierName($ticket),
            );

            // 2. Add custom fields to the array
            foreach ($hesk_settings['custom_fields'] as $k => $v) {
                $info[$k] = $v['use'] ? $ticket[$k] : '';
            }

            // 3. Make sure all values are properly formatted for email
            $ticket = hesk_ticketToPlain($info, 1, 0);

            /* Notify the new owner? */
            if ($ticket['owner'] != intval($_SESSION['id'])) {
                hesk_notifyAssignedStaff(false, 'ticket_assigned_to_you');
            }
		} else {
            $end_message[] = sprintf($hesklang['assign_5'], $ticket['trackid'], $owner_data['name']);
        }

		$i++;
	}

	hesk_process_messages(sprintf($hesklang['assign_log'], $num_assigned, ($i - $num_assigned), implode("\n", $end_message)),$referer,($num_assigned == 0) ? 'ERROR' : ($num_assigned < $i ? 'NOTICE' : 'SUCCESS'));
}


// Change priority
if (array_key_exists($_POST['a'], $priorities)) {
    // A security check
    hesk_token_check('POST');

    // Priority info
    $priority = $priorities[$_POST['a']];

    foreach ($_POST['id'] as $this_id) {
        if (is_array($this_id)) {
            continue;
        }

        $this_id = intval($this_id) or hesk_error($hesklang['id_not_valid']);
        $result = hesk_dbQuery("SELECT `priority`, `category` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `id`={$this_id} LIMIT 1");
        if (hesk_dbNumRows($result) != 1) {
            continue;
        }
        $ticket = hesk_dbFetchAssoc($result);

        if ($ticket['priority'] == $priority['value']) {
            continue;
        }

        hesk_okCategory($ticket['category']);

        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `priority`='{$priority['value']}' WHERE `id`={$this_id}");
        mfh_insert_audit_trail_record($this_id, 'TICKET', 'audit_priority', hesk_date(),
            array(0 => $_SESSION['name'] . ' (' . $_SESSION['user'] . ')',
                1 => $priority['lang']));

        $i++;
    }

    hesk_process_messages($hesklang['pri_set_to'] . ' ' . $priority['formatted'], $referer, 'SUCCESS');
} /* DELETE */
elseif ($_POST['a'] == 'delete') {
    /* Check permissions for this feature */
    hesk_checkPermission('can_del_tickets');

    /* A security check */
    hesk_token_check('POST');

    // Will we need ticket notifications?
    if ($hesk_settings['notify_closed']) {
        require(HESK_PATH . 'inc/email_functions.inc.php');
    }

    foreach ($_POST['id'] as $this_id) {
        if (is_array($this_id)) {
            continue;
        }

        $this_id = intval($this_id) or hesk_error($hesklang['id_not_valid']);
        $result = hesk_dbQuery("SELECT `id`,`trackid`,`category` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `id`='" . intval($this_id) . "' LIMIT 1");
        if (hesk_dbNumRows($result) != 1) {
            continue;
        }
        $ticket = hesk_dbFetchAssoc($result);

        hesk_okCategory($ticket['category']);

        hesk_fullyDeleteTicket();
        $i++;
    }

    hesk_process_messages(sprintf($hesklang['num_tickets_deleted'], $i), $referer, 'SUCCESS');
} /* MERGE TICKETS */
elseif ($_POST['a'] == 'merge') {
    /* Check permissions for this feature */
    hesk_checkPermission('can_merge_tickets');

    /* A security check */
    hesk_token_check('POST');

    /* Sort IDs, tickets will be merged to the lowest ID */
    sort($_POST['id'], SORT_NUMERIC);

    /* Select lowest ID as the target ticket */
    $merge_into = array_shift($_POST['id']);

    /* Merge tickets or throw an error */
    if (hesk_mergeTickets($_POST['id'], $merge_into)) {
        hesk_process_messages($hesklang['merged'], $referer, 'SUCCESS');
    } else {
        $hesklang['merge_err'] .= ' ' . $_SESSION['error'];
        hesk_cleanSessionVars($_SESSION['error']);
        hesk_process_messages($hesklang['merge_err'], $referer);
    }
} /* TAG/UNTAG TICKETS */
elseif ($_POST['a'] == 'tag' || $_POST['a'] == 'untag') {
    /* Check permissions for this feature */
    hesk_checkPermission('can_add_archive');

    /* A security check */
    hesk_token_check('POST');

    if ($_POST['a'] == 'tag') {
        $archived = 1;
        $action = $hesklang['num_tickets_tag'];
    } else {
        $archived = 0;
        $action = $hesklang['num_tickets_untag'];
    }

    foreach ($_POST['id'] as $this_id) {
        if (is_array($this_id)) {
            continue;
        }

        $this_id = intval($this_id) or hesk_error($hesklang['id_not_valid']);
        $result = hesk_dbQuery("SELECT `id`,`trackid`,`category` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `id`='" . intval($this_id) . "' LIMIT 1");
        if (hesk_dbNumRows($result) != 1) {
            continue;
        }
        $ticket = hesk_dbFetchAssoc($result);

        hesk_okCategory($ticket['category']);

        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `archive`='$archived' WHERE `id`='" . intval($this_id) . "'");
        $i++;
    }

    hesk_process_messages(sprintf($action, $i), $referer, 'SUCCESS');
}
/* EXPORT */
elseif ($_POST['a']=='export') {
    /* Check permissions for this feature */
    hesk_checkPermission('can_export');

    /* A security check */
    hesk_token_check('POST');

    $ids_to_export = array();

    foreach ($_POST['id'] as $this_id) {
        if ( is_array($this_id) ) {
            continue;
        }

        $ids_to_export[] = intval($this_id) or hesk_error($hesklang['id_not_valid']);
        $i++;
    }

    if ($i < 1) {
        hesk_process_messages($hesklang['no_selected'], $referer, 'NOTICE');
    }

    // Start SQL statement for selecting tickets
    $sql = "SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `id` IN (".implode(',', $ids_to_export).") ";
    $sql .= " AND " . hesk_myCategories();
    $sql .= " AND " . hesk_myOwnership();

    require_once(HESK_PATH . 'inc/custom_fields.inc.php');
    require_once(HESK_PATH . 'inc/statuses.inc.php');
    require(HESK_PATH . 'inc/export_functions.inc.php');

    list($success_msg, $tickets_exported) = hesk_export_to_XML($sql, true);

    if ($tickets_exported > 0) {
        hesk_process_messages($success_msg,$referer,'SUCCESS');
    } else {
        hesk_process_messages($hesklang['n2ex'],$referer,'NOTICE');
    }
}
/* ANONYMIZE */
elseif ($_POST['a']=='anonymize') {
    /* Check permissions for this feature */
    hesk_checkPermission('can_privacy');

    /* A security check */
    hesk_token_check('POST');

    require(HESK_PATH . 'inc/privacy_functions.inc.php');

    foreach ($_POST['id'] as $this_id) {
        if (is_array($this_id)) {
            continue;
        }

        $this_id = intval($this_id) or hesk_error($hesklang['id_not_valid']);
        $result = hesk_dbQuery("SELECT `id`,`trackid`,`name`,`category` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `id`='".intval($this_id)."' AND ".hesk_myOwnership()." LIMIT 1");
        if (hesk_dbNumRows($result) != 1) {
            continue;
        }
        $ticket = hesk_dbFetchAssoc($result);

        hesk_okCategory($ticket['category']);

        hesk_anonymizeTicket(null, null, true);
        $i++;
    }

    hesk_process_messages(sprintf($hesklang['num_tickets_anon'],$i),$referer,'SUCCESS');
}
/* PRINT */
elseif ($_POST['a']=='print') {
    /* Check permissions for this feature */
	hesk_checkPermission('can_view_tickets');

	/* A security check */
	hesk_token_check('POST');

    // Load custom fields
    require_once(HESK_PATH . 'inc/custom_fields.inc.php');

    // Load statuses
    require_once(HESK_PATH . 'inc/statuses.inc.php');

	// List of staff
	if (!isset($admins)) {
        $admins = array();
        $res2 = hesk_dbQuery("SELECT `id`,`name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` ORDER BY `id` ASC");
        while ($row=hesk_dbFetchAssoc($res2)) {
            $admins[$row['id']]=$row['name'];
        }
    }

	// List of categories
	$hesk_settings['categories'] = array();
	$res2 = hesk_dbQuery('SELECT `id`, `name` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'categories` WHERE ' . hesk_myCategories('id') . ' ORDER BY `cat_order` ASC');
	while ($row=hesk_dbFetchAssoc($res2)) {
        $hesk_settings['categories'][$row['id']] = $row['name'];
    }

    // Print page head
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
    <html>
    <head>
        <title><?php echo $hesk_settings['hesk_title']; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $hesklang['ENCODING']; ?>">
        <style type="text/css">
            body, table, td, p {
                color : black;
                font-family : Verdana, Geneva, Arial, Helvetica, sans-serif;
                font-size : <?php echo $hesk_settings['print_font_size']; ?>px;
            }
            table {
            	border-collapse:collapse;
            }
            hr {
            	border: 0;
            	color: #9e9e9e;
            	background-color: #9e9e9e;
            	height: 1px;
            	width: 100%;
            	text-align: left;
            }
            </style>
    </head>
    <body onload="window.print()">
    <?php

    // Loop through ticket IDs and print them
    foreach ($_POST['id'] as $this_id) {
        if (is_array($this_id)) {
            continue;
        }

        $this_id = intval($this_id) or hesk_error($hesklang['id_not_valid']);
        $result = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `id`='".intval($this_id)."' LIMIT 1");
        if (hesk_dbNumRows($result) != 1) {
            continue;
        }
        $ticket = hesk_dbFetchAssoc($result);

        // Check that we have proper permissions to view this ticket
        hesk_okCategory($ticket['category']);

        $can_view_ass_by     = hesk_checkPermission('can_view_ass_by', 0);
        $can_view_unassigned = hesk_checkPermission('can_view_unassigned',0);

        if ($ticket['owner'] && $ticket['owner'] != $_SESSION['id'] && ! hesk_checkPermission('can_view_ass_others',0)) {
            // Maybe this user is allowed to view tickets he/she assigned?
            if ( ! $can_view_ass_by || $ticket['assignedby'] != $_SESSION['id']) {
                hesk_error($hesklang['ycvtao']);
            }
        }

        if (!$ticket['owner'] && ! $can_view_unassigned) {
            hesk_error($hesklang['ycovtay']);
        }

        // All good, continue...

        $category['name'] = isset($hesk_settings['categories'][$ticket['category']]) ? $hesk_settings['categories'][$ticket['category']] : $hesklang['catd'];

        // Get replies
        $res  = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."replies` WHERE `replyto`='{$ticket['id']}' ORDER BY `id` ASC");
        $replies = hesk_dbNumRows($res);

        // Print ticket
        require(HESK_PATH . 'inc/print_template.inc.php');
		flush();
    }
    ?>
    </body>
    </html>
    <?php
    exit();
}
/* JUST CLOSE */
else {
    /* Check permissions for this feature */
    hesk_checkPermission('can_view_tickets');
    hesk_checkPermission('can_reply_tickets');
    hesk_checkPermission('can_resolve');

    /* A security check */
    hesk_token_check('POST');
    require(HESK_PATH . 'inc/email_functions.inc.php');

    foreach ($_POST['id'] as $this_id) {
        if (is_array($this_id)) {
            continue;
        }

        $this_id = intval($this_id) or hesk_error($hesklang['id_not_valid']);

        $result = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `id`='" . intval($this_id) . "' LIMIT 1");
        $ticket = hesk_dbFetchAssoc($result);

        hesk_okCategory($ticket['category']);

        $closedStatusRS = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` WHERE `IsStaffClosedOption` = 1");
        $closedStatus = hesk_dbFetchAssoc($closedStatusRS);

        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `status`='" . $closedStatus['ID'] . "', `closedat`=NOW(), `closedby`=" . intval($_SESSION['id']) . " WHERE `id`='" . intval($this_id) . "'");

        mfh_insert_audit_trail_record($this_id, 'TICKET', 'audit_closed', hesk_date(),
            array(0 => $_SESSION['name'] . ' (' . $_SESSION['user'] . ')'));

        $i++;

        // Notify customer of closed ticket?
        if ($hesk_settings['notify_closed']) {
            $ticket['dt'] = hesk_date($ticket['dt'], true);
            $ticket['lastchange'] = hesk_date($ticket['lastchange'], true);
            $ticket = hesk_ticketToPlain($ticket, 1, 0);
            hesk_notifyCustomer($modsForHesk_settings, 'ticket_closed');
        }
    }

    hesk_process_messages(sprintf($hesklang['num_tickets_closed'], $i), $referer, 'SUCCESS');
}


/*** START FUNCTIONS ***/


function hesk_fullyDeleteTicket()
{
    global $hesk_settings, $hesklang, $ticket;

    /* Delete attachment files */
    $res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` WHERE `ticket_id`='" . hesk_dbEscape($ticket['trackid']) . "'");
    if (hesk_dbNumRows($res)) {
        $hesk_settings['server_path'] = dirname(dirname(__FILE__));

        while ($file = hesk_dbFetchAssoc($res)) {
            hesk_unlink($hesk_settings['server_path'] . '/' . $hesk_settings['attach_dir'] . '/' . $file['saved_name']);
        }
    }

    /* Delete attachments info from the database */
    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` WHERE `ticket_id`='" . hesk_dbEscape($ticket['trackid']) . "'");

    /* Delete the ticket */
    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `id`='" . intval($ticket['id']) . "'");

    /* Delete replies to the ticket */
    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` WHERE `replyto`='" . intval($ticket['id']) . "'");

    /* Delete ticket notes */
    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` WHERE `ticket`='" . intval($ticket['id']) . "'");

    /* Delete audit trail records */
    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "audit_trail_to_replacement_values` 
        WHERE `audit_trail_id` IN (
            SELECT `id` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "audit_trail`
            WHERE `entity_type` = 'TICKET' AND `entity_id` = " . intval($ticket['id']) . ")");
    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "audit_trail` WHERE `entity_type`='TICKET' 
        AND `entity_id` = " . intval($ticket['id']));

    /* Delete ticket reply drafts */
    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "reply_drafts` WHERE `ticket`=" . intval($ticket['id']));

    return true;
}

?>
