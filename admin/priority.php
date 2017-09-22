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
hesk_token_check('POST');

/* Ticket ID */
$trackingID = hesk_cleanID() or die($hesklang['int_error'].': '.$hesklang['no_trackID']);

$priority   = intval( hesk_POST('priority') );
if ($priority < 0 || $priority > 3)
{
	hesk_process_messages($hesklang['inpr'],'admin_ticket.php?track='.$trackingID.'&Refresh='.mt_rand(10000,99999),'NOTICE');
}

$options = array(
	0 => '<font class="critical">'.$hesklang['critical'].'</font>',
	1 => '<font class="important">'.$hesklang['high'].'</font>',
	2 => '<font class="medium">'.$hesklang['medium'].'</font>',
	3 => $hesklang['low']
);

$revision = sprintf($hesklang['thist8'],hesk_date(),$options[$priority],$_SESSION['name'].' ('.$_SESSION['user'].')');

hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `priority`='{$priority}', `history`=CONCAT(`history`,'".hesk_dbEscape($revision)."') WHERE `trackid`='".hesk_dbEscape($trackingID)."'");
if (hesk_dbAffectedRows() != 1)
{
	hesk_process_messages($hesklang['inpr'],'admin_ticket.php?track='.$trackingID.'&Refresh='.mt_rand(10000,99999),'NOTICE');
}

hesk_process_messages(sprintf($hesklang['chpri2'],$options[$priority]),'admin_ticket.php?track='.$trackingID.'&Refresh='.mt_rand(10000,99999),'SUCCESS');
?>
