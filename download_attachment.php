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
define('HESK_PATH','./');

// Get all the required files and functions
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
hesk_load_database_functions();

hesk_session_start();

// Are we in maintenance mode? (check customers only)
if ( empty($SESSION['id']) )
{
	hesk_check_maintenance();
}

// Knowledgebase attachments
if ( isset($_GET['kb_att']) )
{
	// Attachment ID
	$att_id = intval( hesk_GET('kb_att') ) or hesk_error($hesklang['id_not_valid']);

	// Connect to database
	hesk_dbConnect();

	// Get attachment info
	$res = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_attachments` WHERE `att_id`='{$att_id}' LIMIT 1");
	if (hesk_dbNumRows($res) != 1)
	{
		hesk_error($hesklang['id_not_valid'].' (att_id)');
	}
	$file = hesk_dbFetchAssoc($res);

    // Is this person allowed access to this attachment?
	$res = hesk_dbQuery("SELECT `t1`.`type` as `cat_type`, `t2`.`type` as `art_type`
						FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_articles` AS `t2`
                        JOIN `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_categories` AS `t1`
                        ON `t2`.`catid` = `t1`.`id`
                        WHERE (`t2`.`attachments` LIKE '{$att_id}#%' OR `t2`.`attachments` LIKE '%,{$att_id}#%' )
                        LIMIT 1");

    // If no attachment found, throw an error
	if (hesk_dbNumRows($res) != 1)
	{
		hesk_error($hesklang['id_not_valid'].' (no_art)');
	}
	$row = hesk_dbFetchAssoc($res);

    // Private or draft article or category?
    if ($row['cat_type'] || $row['art_type'])
    {
		if ( empty($_SESSION['id']) )
		{
			// This is a staff-only attachment
			hesk_error($hesklang['attpri']);
		}
		elseif ($row['art_type'] == 2)
		{
			// Need permission to manage KB to access draft attachments
			require(HESK_PATH . 'inc/admin_functions.inc.php');
			hesk_checkPermission('can_man_kb');
		}
    }
}

// Ticket attachments
else
{
	// Attachmend ID and ticket tracking ID
    $att_id = intval( hesk_GET('att_id', 0) ) or die($hesklang['id_not_valid']);
	$tic_id = hesk_cleanID() or die("$hesklang[int_error]: $hesklang[no_trackID]");

	// Connect to database
	hesk_dbConnect();

	// Get attachment info
	$res = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` WHERE `att_id`='{$att_id}' LIMIT 1");
	if (hesk_dbNumRows($res) != 1)
	{
		hesk_error($hesklang['id_not_valid'].' (att_id)');
	}
	$file = hesk_dbFetchAssoc($res);

	// Is ticket ID valid for this attachment?
	if ($file['ticket_id'] != $tic_id)
	{
	    hesk_error($hesklang['trackID_not_found']);
	}

	// Verify email address match if needed
	if ( empty($_SESSION['id']) )
    {
    	hesk_verifyEmailMatch($tic_id);

		// Only staff may download attachments to notes
		if ($file['type'])
		{
        	hesk_error($hesklang['perm_deny']);
		}
    }
}

// Path of the file on the server
$realpath = $hesk_settings['attach_dir'] . '/' . $file['saved_name'];

// Perhaps the file has been deleted?
if ( ! file_exists($realpath))
{
	hesk_error($hesklang['attdel']);
}

// Send the file as an attachment to prevent malicious code from executing
header("Pragma: "); # To fix a bug in IE when running https
header("Cache-Control: "); # To fix a bug in IE when running https
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Length: ' . $file['size']);
header('Content-Disposition: attachment; filename=' . $file['real_name']);

// For larger files use chunks, smaller ones can be read all at once
$chunksize = 1048576; // = 1024 * 1024 (1 Mb)
if ($file['size'] > $chunksize)
{
	$handle = fopen($realpath, 'rb');
	$buffer = '';
	while ( ! feof($handle))
    {
        set_time_limit(300);
		$buffer = fread($handle, $chunksize);
		echo $buffer;
		flush();
	}
	fclose($handle);
}
else
{
	readfile($realpath);
}

exit();
?>
