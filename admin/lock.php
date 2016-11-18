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

/* Check permissions for this feature */
hesk_checkPermission('can_view_tickets');
hesk_checkPermission('can_reply_tickets');
hesk_checkPermission('can_edit_tickets');
hesk_checkPermission('can_resolve');

/* A security check */
hesk_token_check();

/* Ticket ID */
$trackingID = hesk_cleanID() or die($hesklang['int_error'] . ': ' . $hesklang['no_trackID']);

/* New locked status */
if (empty($_GET['locked'])) {
    $status = 0;
    $tmp = $hesklang['tunlock'];
    $revision = sprintf($hesklang['thist6'], hesk_date(), $_SESSION['name'] . ' (' . $_SESSION['user'] . ')');
    $closedby_sql = ' , `closedat`=NULL, `closedby`=NULL ';
} else {
    $status = 1;
    $tmp = $hesklang['tlock'];
    $revision = sprintf($hesklang['thist5'], hesk_date(), $_SESSION['name'] . ' (' . $_SESSION['user'] . ')');
    $closedby_sql = ' , `closedat`=NOW(), `closedby`=' . intval($_SESSION['id']) . ' ';

    // Notify customer of closed ticket?
    if ($hesk_settings['notify_closed']) {
        // Get ticket info
        $result = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `trackid`='" . hesk_dbEscape($trackingID) . "' LIMIT 1");
        if (hesk_dbNumRows($result) != 1) {
            hesk_error($hesklang['ticket_not_found']);
        }
        $ticket = hesk_dbFetchAssoc($result);

        $closedStatusRS = hesk_dbQuery('SELECT `ID` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'statuses` WHERE `IsClosed` = 1');
        $ticketIsOpen = true;
        while ($row = hesk_dbFetchAssoc($closedStatusRS)) {
            if ($ticket['status'] == $row['ID']) {
                $ticketIsOpen = false;
            }
        }
        // Notify customer, but only if ticket is not already closed
        if ($ticketIsOpen) {
            require(HESK_PATH . 'inc/email_functions.inc.php');

            $ticket['dt'] = hesk_date($ticket['dt'], true);
            $ticket['lastchange'] = hesk_date($ticket['lastchange'], true);
            hesk_notifyCustomer($modsForHesk_settings, 'ticket_closed');
        }
    }
}

/* Update database */
$statusSql = 'SELECT `ID` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'statuses` WHERE `LockedTicketStatus` = 1';
$statusRs = hesk_dbQuery($statusSql);
$statusRow = hesk_dbFetchAssoc($statusRs);
$statusId = $statusRow['ID'];

hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `status`= {$statusId},`locked`='{$status}' $closedby_sql , `history`=CONCAT(`history`,'" . hesk_dbEscape($revision) . "')  WHERE `trackid`='" . hesk_dbEscape($trackingID) . "'");

/* Back to ticket page and show a success message */
hesk_process_messages($tmp, 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . rand(10000, 99999), 'SUCCESS');