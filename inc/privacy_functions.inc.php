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

/* Check if this is a valid include */
if (!defined('IN_SCRIPT')) {die('Invalid attempt');}


/*** FUNCTIONS ***/


function hesk_anonymizeTicket($id, $trackingID = null, $have_ticket = false)
{
	global $hesk_settings, $hesklang;

    // Do we already have ticket info?
    if ($have_ticket)
    {
        global $ticket;
    }
    else
    {
        // Get ticket info by tracking or numerical ID
        if ($trackingID !== null)
        {
            $res = hesk_dbQuery("SELECT `id`, `trackid`, `name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `trackid`='".hesk_dbEscape($trackingID)."' AND ".hesk_myOwnership());
        }
        else
        {
    	    $res = hesk_dbQuery("SELECT `id`, `trackid`, `name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `id`=".intval($id)." AND ".hesk_myOwnership());
        }
        if ( ! hesk_dbNumRows($res))
        {
            return false;
        }
        $ticket = hesk_dbFetchAssoc($res);
    }

    // Delete attachment files
	$res = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` WHERE `ticket_id`='".hesk_dbEscape($ticket['trackid'])."'");
    if (hesk_dbNumRows($res))
    {
    	$hesk_settings['server_path'] = dirname(dirname(__FILE__));

    	while ($file = hesk_dbFetchAssoc($res))
        {
        	hesk_unlink($hesk_settings['server_path'].'/'.$hesk_settings['attach_dir'].'/'.$file['saved_name']);
        }
    }

    // Delete attachments info from the database
	hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` WHERE `ticket_id`='".hesk_dbEscape($ticket['trackid'])."'");

    // Anonymize ticket
    $sql = "UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET
    `name`    = '".hesk_dbEscape($hesklang['anon_name'])."',
    `email`   = '".hesk_dbEscape($hesklang['anon_email'])."',
    `subject` = '".hesk_dbEscape($hesklang['anon_subject'])."',
    `message` = '".hesk_dbEscape($hesklang['anon_message'])."',
    `ip`      = '".hesk_dbEscape($hesklang['anon_IP'])."',
    ";
    for($i=1; $i<=50; $i++)
    {
        $sql .= "`custom{$i}` = '',";
    }
    $sql .= "
    attachments='',
    `history`=REPLACE(`history`, ' ".hesk_dbEscape(addslashes($ticket['name']))."</li>', ' ".hesk_dbEscape($hesklang['anon_name'])."</li>'),
    `history`=CONCAT(`history`,'".hesk_dbEscape(sprintf($hesklang['thist18'],hesk_date(),$_SESSION['name'].' ('.$_SESSION['user'].')'))."')
    WHERE `id`='".intval($ticket['id'])."'";
	hesk_dbQuery($sql);

    // Anonymize replies
	hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."replies` SET `name` = '".hesk_dbEscape($hesklang['anon_name'])."', `message` = '".hesk_dbEscape($hesklang['anon_message'])."', attachments='' WHERE `replyto`='".intval($ticket['id'])."'");

    // Delete ticket notes
	hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."notes` WHERE `ticket`='".intval($ticket['id'])."'");

	// Delete ticket reply drafts
	hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."reply_drafts` WHERE `ticket`=".intval($ticket['id']));

    return true;
} // END hesk_anonymizeTicket()
