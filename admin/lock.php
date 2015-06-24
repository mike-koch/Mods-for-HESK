<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.6.4 from 22nd June 2015
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
hesk_checkPermission('can_edit_tickets');

/* A security check */
hesk_token_check();

/* Ticket ID */
$trackingID = hesk_cleanID() or die($hesklang['int_error'].': '.$hesklang['no_trackID']);

/* New locked status */
if (empty($_GET['locked']))
{
	$status = 0;
	$tmp = $hesklang['tunlock'];                                                                      
    $revision = sprintf($hesklang['thist6'],hesk_date(),$_SESSION['name'].' ('.$_SESSION['user'].')');
    $closedby_sql = ' , `closedat`=NULL, `closedby`=NULL ';
}
else
{
	$status = 1;
	$tmp = $hesklang['tlock'];
    $revision = sprintf($hesklang['thist5'],hesk_date(),$_SESSION['name'].' ('.$_SESSION['user'].')');
    $closedby_sql = ' , `closedat`=NOW(), `closedby`='.intval($_SESSION['id']).' ';

    // Notify customer of closed ticket?
    if ($hesk_settings['notify_closed'])
    {
        // Get ticket info
        $result = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `trackid`='".hesk_dbEscape($trackingID)."' LIMIT 1");
        if (hesk_dbNumRows($result) != 1)
        {
            hesk_error($hesklang['ticket_not_found']);
        }
        $ticket = hesk_dbFetchAssoc($result);

        $closedStatusRS = hesk_dbQuery('SELECT `ID` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'statuses` WHERE `IsClosed` = 1');
        $ticketIsOpen = true;
        while ($row = hesk_dbFetchAssoc($closedStatusRS))
        {
            if ($ticket['status'] == $row['ID'])
            {
                $ticketIsOpen = false;
            }
        }
        // Notify customer, but only if ticket is not already closed
        if ($ticketIsOpen)
        {
            require(HESK_PATH . 'inc/email_functions.inc.php');

            $ticket['dt'] = hesk_date($ticket['dt'], true);
            $ticket['lastchange'] = hesk_date($ticket['lastchange'], true);
            hesk_notifyCustomer('ticket_closed');
        }
    }
}

/* Update database */
$statusSql = 'SELECT `ID` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'statuses` WHERE `LockedTicketStatus` = 1';
$statusRow = hesk_dbQuery($statusSql)->fetch_assoc();
$statusId = $statusRow['ID'];

hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `status`='{$statusId}',`locked`='{$status}' $closedby_sql , `history`=CONCAT(`history`,'".hesk_dbEscape($revision)."')  WHERE `trackid`='".hesk_dbEscape($trackingID)."' LIMIT 1");

/* Back to ticket page and show a success message */
hesk_process_messages($tmp,'admin_ticket.php?track='.$trackingID.'&Refresh='.rand(10000,99999),'SUCCESS');
?>
