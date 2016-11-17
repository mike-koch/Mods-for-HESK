<?php
/*******************************************************************************
 *  Title: Help Desk Software HESK
 *  Version: 2.6.8 from 10th August 2016
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
if (!isset($_REQUEST['isManager']) || !$_REQUEST['isManager']) {
    hesk_checkPermission('can_view_tickets');
    hesk_checkPermission('can_reply_tickets');
}

/* A security check */
hesk_token_check();

/* Ticket ID */
$trackingID = hesk_cleanID() or die($hesklang['int_error'] . ': ' . $hesklang['no_trackID']);

/* Valid statuses */
$statusSql = "SELECT `ID` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses`";
$status_options = array();
$results = hesk_dbQuery($statusSql);

while ($row = hesk_dbFetchAssoc($results)) {
    $status_options[$row['ID']] = mfh_getDisplayTextForStatusId($row['ID']);
}

/* New status */
$status = intval(hesk_REQUEST('s'));
if (!isset($status_options[$status])) {
    hesk_process_messages($hesklang['instat'], 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999), 'NOTICE');
}

$locked = 0;

$statusRow = hesk_dbFetchAssoc(hesk_dbQuery("SELECT `ID`, `IsClosed` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` WHERE ID = " . $status));
if ($statusRow['IsClosed']) // Closed
{
    if ( ! hesk_checkPermission('can_resolve', 0)) {
        hesk_process_messages($hesklang['noauth_resolve'],'admin_ticket.php?track='.$trackingID.'&Refresh='.mt_rand(10000,99999),'NOTICE');
    }

    $action = $hesklang['ticket_been'] . ' ' . $hesklang['close'];
    $revision = sprintf($hesklang['thist3'], hesk_date(), $_SESSION['name'] . ' (' . $_SESSION['user'] . ')');

    if ($hesk_settings['custopen'] != 1) {
        $locked = 1;
    }

    // Notify customer of closed ticket?
    if ($hesk_settings['notify_closed']) {
        // Get ticket info
        $result = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `trackid`='" . hesk_dbEscape($trackingID) . "' LIMIT 1");
        if (hesk_dbNumRows($result) != 1) {
            hesk_error($hesklang['ticket_not_found']);
        }
        $ticket = hesk_dbFetchAssoc($result);
        $ticket['status'] = $status;
        $ticket['dt'] = hesk_date($ticket['dt'], true);
        $ticket['lastchange'] = hesk_date($ticket['lastchange'], true);
        $ticket = hesk_ticketToPlain($ticket, 1, 0);

        // Notify customer
        require(HESK_PATH . 'inc/email_functions.inc.php');
        hesk_notifyCustomer($modsForHesk_settings, 'ticket_closed');
    }

    // Log who marked the ticket resolved
    $closedby_sql = ' , `closedat`=NOW(), `closedby`=' . intval($_SESSION['id']) . ' ';
} elseif ($statusRow['IsNewTicketStatus'] == '0') //Ticket is still open, but not new
{
    $action = sprintf($hesklang['tsst'], $status_options[$status]);
    $revision = sprintf($hesklang['thist9'], hesk_date(), $status_options[$status], $_SESSION['name'] . ' (' . $_SESSION['user'] . ')');

    // Ticket is not resolved
    $closedby_sql = ' , `closedat`=NULL, `closedby`=NULL ';
} else // Ticket is marked as "NEW"
{
    $action = $hesklang['ticket_been'] . ' ' . $hesklang['opened'];
    $revision = sprintf($hesklang['thist4'], hesk_date(), $_SESSION['name'] . ' (' . $_SESSION['user'] . ')');

    // Ticket is not resolved
    $closedby_sql = ' , `closedat`=NULL, `closedby`=NULL ';
}


hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `status`='{$status}', `locked`='{$locked}' $closedby_sql , `history`=CONCAT(`history`,'" . hesk_dbEscape($revision) . "') WHERE `trackid`='" . hesk_dbEscape($trackingID) . "'");

if (hesk_dbAffectedRows() != 1) {
    hesk_error("$hesklang[int_error]: $hesklang[trackID_not_found].");
}

hesk_process_messages($action, 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . rand(10000, 99999), 'SUCCESS');
