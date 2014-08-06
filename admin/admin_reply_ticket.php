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
require(HESK_PATH . 'inc/email_functions.inc.php');
require(HESK_PATH . 'inc/posting_functions.inc.php');

// We only allow POST requests from the HESK form to this file
if ( $_SERVER['REQUEST_METHOD'] != 'POST' )
{
	header('Location: admin_main.php');
	exit();
}

// Check for POST requests larger than what the server can handle
if ( empty($_POST) && ! empty($_SERVER['CONTENT_LENGTH']) )
{
	hesk_error($hesklang['maxpost']);
}

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

/* Check permissions for this feature */
hesk_checkPermission('can_reply_tickets');

/* A security check */
# hesk_token_check('POST');

/* Original ticket ID */
$replyto = intval( hesk_POST('orig_id', 0) ) or die($hesklang['int_error']);

/* Get details about the original ticket */
$result = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `id`='{$replyto}' LIMIT 1");
if (hesk_dbNumRows($result) != 1)
{
	hesk_error($hesklang['ticket_not_found']);
}
$ticket = hesk_dbFetchAssoc($result);
$trackingID = $ticket['trackid'];

$hesk_error_buffer = array();

// Get the message
$message = hesk_input(hesk_POST('message'));

if (strlen($message))
{
	// Attach signature to the message?
	if ( ! empty($_POST['signature']))
	{
	    $message .= "\n\n" . addslashes($_SESSION['signature']) . "\n";
	}

    // Make links clickable
	$message = hesk_makeURL($message);

    // Turn newlines into <br /> tags
	$message = nl2br($message);
}
else
{
    $hesk_error_buffer[] = $hesklang['enter_message'];
}

/* Attachments */
if ($hesk_settings['attachments']['use'])
{
    require(HESK_PATH . 'inc/attachments.inc.php');
    $attachments = array();
    for ($i=1;$i<=$hesk_settings['attachments']['max_number'];$i++)
    {
        $att = hesk_uploadFile($i);
        if ($att !== false && !empty($att))
        {
            $attachments[$i] = $att;
        }
    }
}
$myattachments='';

/* Time spent working on ticket */
$time_worked = hesk_getTime(hesk_POST('time_worked'));

/* Any errors? */
if (count($hesk_error_buffer)!=0)
{
    $_SESSION['ticket_message'] = hesk_POST('message');
    $_SESSION['time_worked'] = $time_worked;

	// Remove any successfully uploaded attachments
	if ($hesk_settings['attachments']['use'])
	{
		hesk_removeAttachments($attachments);
	}

    $tmp = '';
    foreach ($hesk_error_buffer as $error)
    {
        $tmp .= "<li>$error</li>\n";
    }
    $hesk_error_buffer = $tmp;

    $hesk_error_buffer = $hesklang['pcer'].'<br /><br /><ul>'.$hesk_error_buffer.'</ul>';
    hesk_process_messages($hesk_error_buffer,'admin_ticket.php?track='.$ticket['trackid'].'&Refresh='.rand(10000,99999));
}

if ($hesk_settings['attachments']['use'] && !empty($attachments))
{
    foreach ($attachments as $myatt)
    {
        hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` (`ticket_id`,`saved_name`,`real_name`,`size`) VALUES ('".hesk_dbEscape($trackingID)."','".hesk_dbEscape($myatt['saved_name'])."','".hesk_dbEscape($myatt['real_name'])."','".intval($myatt['size'])."')");
        $myattachments .= hesk_dbInsertID() . '#' . $myatt['real_name'] .',';
    }
}

/* Add reply */
$result = hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."replies` (`replyto`,`name`,`message`,`dt`,`attachments`,`staffid`) VALUES ('".intval($replyto)."','".hesk_dbEscape(addslashes($_SESSION['name']))."','".hesk_dbEscape($message)."',NOW(),'".hesk_dbEscape($myattachments)."','".intval($_SESSION['id'])."')");

/* Track ticket status changes for history */
$revision = '';

/* Change the status of priority? */
if ( ! empty($_POST['set_priority']) )
{
    $priority = intval( hesk_POST('priority') );
    if ($priority < 0 || $priority > 3)
    {
    	hesk_error($hesklang['select_priority']);
    }

	$options = array(
		0 => '<font class="critical">'.$hesklang['critical'].'</font>',
		1 => '<font class="important">'.$hesklang['high'].'</font>',
		2 => '<font class="medium">'.$hesklang['medium'].'</font>',
		3 => $hesklang['low']
	);

    $revision = sprintf($hesklang['thist8'],hesk_date(),$options[$priority],$_SESSION['name'].' ('.$_SESSION['user'].')');

    $priority_sql = ",`priority`='$priority', `history`=CONCAT(`history`,'".hesk_dbEscape($revision)."') ";
}
else
{
    $priority_sql = "";
}

/* Update the original ticket */
$defaultStatusReplyStatus = hesk_dbFetchAssoc(hesk_dbQuery("SELECT `ID`, `IsClosed` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` WHERE `IsDefaultStaffReplyStatus` = 1 LIMIT 1"));
$staffClosedCheckboxStatus = hesk_dbFetchAssoc(hesk_dbQuery("SELECT `ID`, `IsClosed` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` WHERE `IsStaffClosedOption` = 1 LIMIT 1"));
$lockedTicketStatus = hesk_dbFetchAssoc(hesk_dbQuery("SELECT `ID` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` WHERE `LockedTicketStatus` = 1 LIMIT 1"));

$new_status = empty($_POST['close']) ? $defaultStatusReplyStatus['ID'] : $staffClosedCheckboxStatus['ID'];

/* --> If a ticket is locked keep it closed */
if ($ticket['locked'])
{
	$new_status = $lockedTicketStatus['ID'];
}

$sql = "UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `status`='{$new_status}', `lastreplier`='1', `replierid`='".intval($_SESSION['id'])."' ";

/* Update time_worked or force update lastchange */
if ($time_worked == '00:00:00')
{
	$sql .= ", `lastchange` = NOW() ";
}
else
{
	$sql .= ",`time_worked` = ADDTIME(`time_worked`,'" . hesk_dbEscape($time_worked) . "') ";
}

if ( ! empty($_POST['assign_self']) && hesk_checkPermission('can_assign_self',0))
{
	$revision = sprintf($hesklang['thist2'],hesk_date(),$_SESSION['name'].' ('.$_SESSION['user'].')',$_SESSION['name'].' ('.$_SESSION['user'].')');
    $sql .= " , `owner`=".intval($_SESSION['id']).", `history`=CONCAT(`history`,'".hesk_dbEscape($revision)."') ";
}

$sql .= " $priority_sql ";


$isNewStatusClosed = empty($_POST['close']) ? $defaultStatusReplyStatus['IsClosed'] : $staffClosedCheckboxStatus['IsClosed'];
if ($isNewStatusClosed)
{
    $revision = sprintf($hesklang['thist3'],hesk_date(),$_SESSION['name'].' ('.$_SESSION['user'].')');
    $sql .= " , `history`=CONCAT(`history`,'".hesk_dbEscape($revision)."') ";

    if ($hesk_settings['custopen'] != 1)
    {
		$sql .= " , `locked`='1' ";
    }
}
$sql .= " WHERE `id`='{$replyto}' LIMIT 1";
hesk_dbQuery($sql);
unset($sql);

/* Update number of replies in the users table */
hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` SET `replies`=`replies`+1 WHERE `id`='".intval($_SESSION['id'])."' LIMIT 1");

// --> Prepare reply message

// 1. Generate the array with ticket info that can be used in emails
$info = array(
'email'			=> $ticket['email'],
'category'		=> $ticket['category'],
'priority'		=> $ticket['priority'],
'owner'			=> $ticket['owner'],
'trackid'		=> $ticket['trackid'],
'status'		=> $new_status,
'name'			=> $ticket['name'],
'lastreplier'	=> $_SESSION['name'],
'subject'		=> $ticket['subject'],
'message'		=> stripslashes($message),
'attachments'	=> $myattachments,
'dt'			=> hesk_date($ticket['dt'], true),
'lastchange'	=> hesk_date($ticket['lastchange'], true),
);

// 2. Add custom fields to the array
foreach ($hesk_settings['custom_fields'] as $k => $v)
{
	$info[$k] = $v['use'] ? $ticket[$k] : '';
}

// 3. Make sure all values are properly formatted for email
$ticket = hesk_ticketToPlain($info, 1, 0);

// Notify the customer
if ( ! isset($_POST['no_notify']) || intval( hesk_POST('no_notify') ) != 1)
{
	hesk_notifyCustomer('new_reply_by_staff');
}

/* Set reply submitted message */
$_SESSION['HESK_SUCCESS'] = TRUE;
$_SESSION['HESK_MESSAGE'] = $hesklang['reply_submitted'];
if (!empty($_POST['close']))
{
    $_SESSION['HESK_MESSAGE'] .= '<br /><br />'.$hesklang['ticket_marked'].' <span class="resolved">'.$hesklang['closed'].'</span>';
}

/* What to do after reply? */
if ($_SESSION['afterreply'] == 1)
{
	header('Location: admin_main.php');
}
elseif ($_SESSION['afterreply'] == 2)
{
	/* Get the next open ticket that needs a reply */
    $res  = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `owner` IN ('0','".intval($_SESSION['id'])."') AND " . hesk_myCategories() . " AND `status` IN ('0','1') ORDER BY `owner` DESC, `priority` ASC LIMIT 1");

    if (hesk_dbNumRows($res) == 1)
    {
    	$row = hesk_dbFetchAssoc($res);
        $_SESSION['HESK_MESSAGE'] .= '<br /><br />'.$hesklang['rssn'];
        header('Location: admin_ticket.php?track='.$row['trackid'].'&Refresh='.rand(10000,99999));
    }
    else
    {
		header('Location: admin_main.php');
    }
}
else
{
	header('Location: admin_ticket.php?track='.$ticket['trackid'].'&Refresh='.rand(10000,99999));
}
exit();
?>
