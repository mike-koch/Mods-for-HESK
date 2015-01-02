<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.5.5 from 5th August 2014
*  Author: Klemen Stirn
*  Website: http://www.hesk.com
********************************************************************************
*  COPYRIGHT AND TRADEMARK NOTICE
*  Copyright 2005-2014 Klemen Stirn. All Rights Reserved.
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

define('IN_SCRIPT',1);
define('HESK_PATH','../');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

/* Check permissions for this feature */
hesk_checkPermission('can_view_tickets');
hesk_checkPermission('can_reply_tickets');

/* A security check */
hesk_token_check();

/* Ticket ID */
$trackingID = hesk_cleanID() or die($hesklang['int_error'].': '.$hesklang['no_trackID']);

/* Valid statuses */
$statusSql = "SELECT `ID`, `ShortNameContentKey` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses`";
$status_options = array();
$results = hesk_dbQuery($statusSql);

while ($row = $results->fetch_assoc())
{
    $status_options[$row['ID']] = $hesklang[$row['ShortNameContentKey']];
}

/* New status */
$status = intval( hesk_REQUEST('s') );
if ( ! isset($status_options[$status]))
{
	hesk_process_messages($hesklang['instat'],'admin_ticket.php?track='.$trackingID.'&Refresh='.mt_rand(10000,99999),'NOTICE');
}

$locked = 0;

// Ticket info
$result = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `trackid`='".hesk_dbEscape($trackingID)."' LIMIT 1");
if (hesk_dbNumRows($result) != 1) {
    hesk_error($hesklang['ticket_not_found']);
}
$ticket = hesk_dbFetchAssoc($result);

$statusRow = hesk_dbFetchAssoc(hesk_dbQuery("SELECT `ID`, `IsClosed` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` WHERE ID = ".$status));
if ($statusRow['IsClosed']) // Closed
{
	$action = $hesklang['ticket_been'] . ' ' . $hesklang['close'];
    $revision = sprintf($hesklang['thist3'],hesk_date(),$_SESSION['name'].' ('.$_SESSION['user'].')');

    if ($hesk_settings['custopen'] != 1)
    {
    	$locked = 1;
    }

    // Notify customer
    require(HESK_PATH . 'inc/email_functions.inc.php');

    if (!empty($ticket['email']))
    {
        hesk_notifyCustomer('ticket_closed');
    }
}
elseif ($statusRow['ID'] != 0) //Ticket is still open, but not new
{
	$action = sprintf($hesklang['tsst'],$status_options[$status]);
    $revision = sprintf($hesklang['thist9'],hesk_date(),$status_options[$status],$_SESSION['name'].' ('.$_SESSION['user'].')');
}
else // Ticket is marked as "NEW"
{
	$action = $hesklang['ticket_been'] . ' ' . $hesklang['opened'];
    $revision = sprintf($hesklang['thist4'],hesk_date(),$_SESSION['name'].' ('.$_SESSION['user'].')');
}

//-- Notify staff after ticket re-open?
$currentStatusRS = hesk_dbQuery('SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'statuses` WHERE `ID` = '.$ticket['status']);
$currentStatus = hesk_dbFetchAssoc($currentStatusRS);

if (intval($currentStatus['IsClosed']) == 1 && $statusRow['IsClosed'] == 0 && $ticket['owner'] != $_SESSION['id']) {
    $ticket['name'] = $_SESSION['name'];
    require(HESK_PATH . 'inc/email_functions.inc.php');
    hesk_notifyAssignedStaff(false, 'ticket_reopen_assigned');
}

hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `status`='{$status}', `locked`='{$locked}', `history`=CONCAT(`history`,'".hesk_dbEscape($revision)."') WHERE `trackid`='".hesk_dbEscape($trackingID)."' LIMIT 1");

if (hesk_dbAffectedRows() != 1)
{
	hesk_error("$hesklang[int_error]: $hesklang[trackID_not_found].");
}

hesk_process_messages($action,'admin_ticket.php?track='.$trackingID.'&Refresh='.rand(10000,99999),'SUCCESS');
?>
