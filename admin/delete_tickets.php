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
    'critical' => array('value' => 0, 'text' => $hesklang['critical'], 'formatted' => '<font class="critical">' . $hesklang['critical'] . '</font>'),
    'high' => array('value' => 1, 'text' => $hesklang['high'], 'formatted' => '<font class="important">' . $hesklang['high'] . '</font>'),
    'medium' => array('value' => 2, 'text' => $hesklang['medium'], 'formatted' => '<font class="medium">' . $hesklang['medium'] . '</font>'),
    'low' => array('value' => 3, 'text' => $hesklang['low'], 'formatted' => $hesklang['low']),
);

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

        $revision = sprintf($hesklang['thist8'], hesk_date(), $priority['formatted'], $_SESSION['name'] . ' (' . $_SESSION['user'] . ')');
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `priority`='{$priority['value']}', `history`=CONCAT(`history`,'" . hesk_dbEscape($revision) . "') WHERE `id`={$this_id}");

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

    $revision = sprintf($hesklang['thist3'], hesk_date(), $_SESSION['name'] . ' (' . $_SESSION['user'] . ')');

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
} /* JUST CLOSE */
else {
    /* Check permissions for this feature */
    hesk_checkPermission('can_view_tickets');
    hesk_checkPermission('can_reply_tickets');
    hesk_checkPermission('can_resolve');

    /* A security check */
    hesk_token_check('POST');
    require(HESK_PATH . 'inc/email_functions.inc.php');

    $revision = sprintf($hesklang['thist3'], hesk_date(), $_SESSION['name'] . ' (' . $_SESSION['user'] . ')');

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

        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `status`='" . $closedStatus['ID'] . "', `closedat`=NOW(), `closedby`=" . intval($_SESSION['id']) . ", `history`=CONCAT(`history`,'" . hesk_dbEscape($revision) . "') WHERE `id`='" . intval($this_id) . "'");
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

    /* Delete ticket reply drafts */
    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "reply_drafts` WHERE `ticket`=" . intval($ticket['id']));

    return true;
}

?>
