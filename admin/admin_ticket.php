<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.5.5 from 5th August 2014
*  Author: Klemen Stirn
*  Website: http://www.hesk.com
********************************************************************************
*  COPYRIGHT AND TRADEMARK NOTICE
*  Copyright 2005-2013 Klemen Stirn. All Rights Reserved.
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
$can_del_notes		 = hesk_checkPermission('can_del_notes',0);
$can_reply			 = hesk_checkPermission('can_reply_tickets',0);
$can_delete			 = hesk_checkPermission('can_del_tickets',0);
$can_edit			 = hesk_checkPermission('can_edit_tickets',0);
$can_archive		 = hesk_checkPermission('can_add_archive',0);
$can_assign_self	 = hesk_checkPermission('can_assign_self',0);
$can_view_unassigned = hesk_checkPermission('can_view_unassigned',0);
$can_change_cat		 = hesk_checkPermission('can_change_cat',0);

// Get ticket ID
$trackingID = hesk_cleanID() or print_form();

$_SERVER['PHP_SELF'] = 'admin_ticket.php?track='.$trackingID.'&Refresh='.mt_rand(10000,99999);

/* We will need timer function */
define('TIMER',1);

/* Get ticket info */
$res = hesk_dbQuery("SELECT `t1`.* , `t2`.name AS `repliername` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` AS `t1` LEFT JOIN `".hesk_dbEscape($hesk_settings['db_pfix'])."users` AS `t2` ON `t1`.`replierid` = `t2`.`id` WHERE `trackid`='".hesk_dbEscape($trackingID)."' LIMIT 1");

/* Ticket found? */
if (hesk_dbNumRows($res) != 1)
{
	/* Ticket not found, perhaps it was merged with another ticket? */
	$res = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `merged` LIKE '%#".hesk_dbEscape($trackingID)."#%' LIMIT 1");

	if (hesk_dbNumRows($res) == 1)
	{
    	/* OK, found in a merged ticket. Get info */
     	$ticket = hesk_dbFetchAssoc($res);
        hesk_process_messages( sprintf($hesklang['tme'], $trackingID, $ticket['trackid']) ,'NOREDIRECT','NOTICE');
        $trackingID = $ticket['trackid'];
	}
    else
    {
    	/* Nothing found, error out */
	    hesk_process_messages($hesklang['ticket_not_found'],'NOREDIRECT');
	    print_form();
    }
}
else
{
	/* We have a match, get ticket info */
	$ticket = hesk_dbFetchAssoc($res);
}

/* Permission to view this ticket? */
if ($ticket['owner'] && $ticket['owner'] != $_SESSION['id'] && ! hesk_checkPermission('can_view_ass_others',0))
{
	hesk_error($hesklang['ycvtao']);
}

if (!$ticket['owner'] && ! $can_view_unassigned)
{
	hesk_error($hesklang['ycovtay']);
}

/* Set last replier name */
if ($ticket['lastreplier'])
{
	if (empty($ticket['repliername']))
	{
		$ticket['repliername'] = $hesklang['staff'];
	}
}
else
{
	$ticket['repliername'] = $ticket['name'];
}

/* Get category name and ID */
$result = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` WHERE `id`='".intval($ticket['category'])."' LIMIT 1");

/* If this category has been deleted use the default category with ID 1 */
if (hesk_dbNumRows($result) != 1)
{
	$result = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` WHERE `id`='1' LIMIT 1");
}

$category = hesk_dbFetchAssoc($result);

/* Is this user allowed to view tickets inside this category? */
hesk_okCategory($category['id']);

/* Delete post action */
if (isset($_GET['delete_post']) && $can_delete && hesk_token_check())
{
	$n = intval( hesk_GET('delete_post') );
    if ($n)
    {
		/* Get last reply ID, we'll need it later */
		$res = hesk_dbQuery("SELECT `id` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."replies` WHERE `replyto`='".intval($ticket['id'])."' ORDER BY `id` DESC LIMIT 1");
        $last_reply_id = hesk_dbResult($res,0,0);

		/* Does this post have any attachments? */
		$res = hesk_dbQuery("SELECT `attachments` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."replies` WHERE `id`='".intval($n)."' AND `replyto`='".intval($ticket['id'])."' LIMIT 1");
		$attachments = hesk_dbResult($res,0,0);

		/* Delete any attachments to this post */
		if ( strlen($attachments) )
		{
        	$hesk_settings['server_path'] = dirname(dirname(__FILE__));

			/* List of attachments */
			$att=explode(',',substr($attachments, 0, -1));
			foreach ($att as $myatt)
			{
				list($att_id, $att_name) = explode('#', $myatt);

				/* Delete attachment files */
				$res = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` WHERE `att_id`='".intval($att_id)."' LIMIT 1");
				if (hesk_dbNumRows($res) && $file = hesk_dbFetchAssoc($res))
				{
					hesk_unlink($hesk_settings['server_path'].'/'.$hesk_settings['attach_dir'].'/'.$file['saved_name']);
				}

				/* Delete attachments info from the database */
				hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` WHERE `att_id`='".intval($att_id)."' LIMIT 1");
			}
		}

		/* Delete this reply */
		hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."replies` WHERE `id`='".intval($n)."' AND `replyto`='".intval($ticket['id'])."' LIMIT 1");

        /* Reply wasn't deleted */
        if (hesk_dbAffectedRows() != 1)
        {
			hesk_process_messages($hesklang['repl1'],$_SERVER['PHP_SELF']);
        }
        else
        {
			/* Reply deleted. Need to update status and last replier? */
			$res = hesk_dbQuery("SELECT `staffid` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."replies` WHERE `replyto`='".intval($ticket['id'])."' ORDER BY `id` DESC LIMIT 1");
			if (hesk_dbNumRows($res))
			{
				$replier_id = hesk_dbResult($res,0,0);
                $last_replier = $replier_id ? 1 : 0;

				/* Change status? */
                $status_sql = '';
				if ($last_reply_id == $n)
				{
					$status = $ticket['locked'] ? 3 : ($last_replier ? 2 : 1);
                    $status_sql = " , `status`='".intval($status)."' ";
				}

				hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `lastchange`=NOW(), `lastreplier`='{$last_replier}', `replierid`='".intval($replier_id)."' $status_sql WHERE `id`='".intval($ticket['id'])."' LIMIT 1");
			}
			else
			{
            	$status = $ticket['locked'] ? 3 : 0;
				hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `lastchange`=NOW(), `lastreplier`='0', `status`='$status' WHERE `id`='".intval($ticket['id'])."' LIMIT 1");
			}

			hesk_process_messages($hesklang['repl'],$_SERVER['PHP_SELF'],'SUCCESS');
        }
    }
    else
    {
    	hesk_process_messages($hesklang['repl0'],$_SERVER['PHP_SELF']);
    }
}

/* Delete notes action */
if (isset($_GET['delnote']) && hesk_token_check())
{
	$n = intval( hesk_GET('delnote') );
    if ($n)
    {
    	if ($can_del_notes)
        {
			hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."notes` WHERE `id`='".intval($n)."' LIMIT 1");
        }
        else
        {
        	hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."notes` WHERE `id`='".intval($n)."' AND `who`='".intval($_SESSION['id'])."' LIMIT 1");
        }
    }
    header('Location: admin_ticket.php?track='.$trackingID.'&Refresh='.mt_rand(10000,99999));
    exit();
}

/* Add a note action */
if (isset($_POST['notemsg']) && hesk_token_check('POST'))
{
	$msg = hesk_input( hesk_POST('notemsg') );

    if ($msg)
    {
    	/* Add note to database */
    	$msg = nl2br(hesk_makeURL($msg));
		hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."notes` (`ticket`,`who`,`dt`,`message`) VALUES ('".intval($ticket['id'])."','".intval($_SESSION['id'])."',NOW(),'".hesk_dbEscape($msg)."')");

        /* Notify assigned staff that a note has been added if needed */
        if ($ticket['owner'] && $ticket['owner'] != $_SESSION['id'])
        {
			$res = hesk_dbQuery("SELECT `email`, `notify_note` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `id`='".intval($ticket['owner'])."' LIMIT 1");

			if (hesk_dbNumRows($res) == 1)
			{
				$owner = hesk_dbFetchAssoc($res);

				// 1. Generate the array with ticket info that can be used in emails
				$info = array(
				'email'			=> $ticket['email'],
				'category'		=> $ticket['category'],
				'priority'		=> $ticket['priority'],
				'owner'			=> $ticket['owner'],
				'trackid'		=> $ticket['trackid'],
				'status'		=> $ticket['status'],
				'name'			=> $_SESSION['name'],
				'lastreplier'	=> $ticket['lastreplier'],
				'subject'		=> $ticket['subject'],
				'message'		=> stripslashes($msg),
                'dt'            => hesk_date($ticket['dt'], true),
                'lastchange'    => hesk_date($ticket['lastchange'], true),
				);

				// 2. Add custom fields to the array
				foreach ($hesk_settings['custom_fields'] as $k => $v)
				{
					$info[$k] = $v['use'] ? $ticket[$k] : '';
				}

				// 3. Make sure all values are properly formatted for email
				$ticket = hesk_ticketToPlain($info, 1, 0);

				/* Get email functions */
				require(HESK_PATH . 'inc/email_functions.inc.php');

				/* Format email subject and message for staff */
				$subject = hesk_getEmailSubject('new_note',$ticket);
				$message = hesk_getEmailMessage('new_note',$ticket,1);

				/* Send email to staff */
				hesk_mail($owner['email'], $subject, $message);
			}
        }

    }
    header('Location: admin_ticket.php?track='.$trackingID.'&Refresh='.mt_rand(10000,99999));
    exit();
}

/* Update time worked */
if ( ($can_reply || $can_edit) && isset($_POST['h']) && isset($_POST['m']) && isset($_POST['s']) && hesk_token_check('POST'))
{
	$h = intval( hesk_POST('h') );
	$m = intval( hesk_POST('m') );
	$s = intval( hesk_POST('s') );

	/* Get time worked in proper format */
    $time_worked = hesk_getTime($h . ':' . $m . ':' . $s);

	/* Update database */
    $revision = sprintf($hesklang['thist14'],hesk_date(),$time_worked,$_SESSION['name'].' ('.$_SESSION['user'].')');
	hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `time_worked`='" . hesk_dbEscape($time_worked) . "', `history`=CONCAT(`history`,'" . hesk_dbEscape($revision) . "') WHERE `trackid`='" . hesk_dbEscape($trackingID) . "' LIMIT 1");

	/* Show ticket */
	hesk_process_messages($hesklang['twu'],'admin_ticket.php?track='.$trackingID.'&Refresh='.mt_rand(10000,99999),'SUCCESS');
}

/* Delete attachment action */
if (isset($_GET['delatt']) && hesk_token_check())
{
	if ( ! $can_delete || ! $can_edit)
    {
		hesk_process_messages($hesklang['no_permission'],'admin_ticket.php?track='.$trackingID.'&Refresh='.mt_rand(10000,99999));
    }

	$att_id = intval( hesk_GET('delatt') ) or hesk_error($hesklang['inv_att_id']);

	$reply = intval( hesk_GET('reply', 0) );
	if ($reply < 1)
	{
		$reply = 0;
	}

	/* Get attachment info */
	$res = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` WHERE `att_id`='".intval($att_id)."' LIMIT 1");
	if (hesk_dbNumRows($res) != 1)
	{
		hesk_process_messages($hesklang['id_not_valid'].' (att_id)','admin_ticket.php?track='.$trackingID.'&Refresh='.mt_rand(10000,99999));
	}
	$att = hesk_dbFetchAssoc($res);

	/* Is ticket ID valid for this attachment? */
	if ($att['ticket_id'] != $trackingID)
	{
		hesk_process_messages($hesklang['trackID_not_found'],'admin_ticket.php?track='.$trackingID.'&Refresh='.mt_rand(10000,99999));
	}

	/* Delete file from server */
	hesk_unlink(HESK_PATH.$hesk_settings['attach_dir'].'/'.$att['saved_name']);

	/* Delete attachment from database */
	hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` WHERE `att_id`='".intval($att_id)."'");

	/* Update ticket or reply in the database */
    $revision = sprintf($hesklang['thist12'],hesk_date(),$att['real_name'],$_SESSION['name'].' ('.$_SESSION['user'].')');
	if ($reply)
	{
		hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."replies` SET `attachments`=REPLACE(`attachments`,'".hesk_dbEscape($att_id.'#'.$att['real_name']).",','') WHERE `id`='".intval($reply)."' LIMIT 1");
		hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `history`=CONCAT(`history`,'".hesk_dbEscape($revision)."') WHERE `id`='".intval($ticket['id'])."' LIMIT 1");
	}
	else
	{
		hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `attachments`=REPLACE(`attachments`,'".hesk_dbEscape($att_id.'#'.$att['real_name']).",',''), `history`=CONCAT(`history`,'".hesk_dbEscape($revision)."') WHERE `id`='".intval($ticket['id'])."' LIMIT 1");
	}

	hesk_process_messages($hesklang['kb_att_rem'],'admin_ticket.php?track='.$trackingID.'&Refresh='.mt_rand(10000,99999),'SUCCESS');
}

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* List of categories */
$result = hesk_dbQuery("SELECT `id`,`name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` ORDER BY `cat_order` ASC");
$categories_options='';
while ($row=hesk_dbFetchAssoc($result))
{
    if ($row['id'] == $ticket['category']) {continue;}
    $categories_options.='<option value="'.$row['id'].'">'.$row['name'].'</option>';
}

/* List of users */
$admins = array();
$result = hesk_dbQuery("SELECT `id`,`name`,`isadmin`,`categories`,`heskprivileges` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` ORDER BY `id` ASC");
while ($row=hesk_dbFetchAssoc($result))
{
	/* Is this an administrator? */
	if ($row['isadmin'])
    {
	    $admins[$row['id']]=$row['name'];
	    continue;
    }

	/* Not admin, is user allowed to view tickets? */
	if (strpos($row['heskprivileges'], 'can_view_tickets') !== false)
	{
		/* Is user allowed to access this category? */
		$cat=substr($row['categories'], 0);
		$row['categories']=explode(',',$cat);
		if (in_array($ticket['category'],$row['categories']))
		{
			$admins[$row['id']]=$row['name'];
			continue;
		}
	}
}

/* Get replies */
$reply = '';
$result  = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."replies` WHERE `replyto`='".intval($ticket['id'])."' ORDER BY `id` " . ($hesk_settings['new_top'] ? 'DESC' : 'ASC') );
$replies = hesk_dbNumRows($result);

// Demo mode
if ( defined('HESK_DEMO') )
{
	$ticket['email'] = 'hidden@demo.com';
	$ticket['ip']	 = '127.0.0.1';
}

/* Print admin navigation */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>
<div class="row" style="padding: 20px">
    <div class="col-md-2">
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo $hesklang['information']; ?></div>
            <ul class="list-group">
                <li class="list-group-item">
                    <strong><?php echo $hesklang['trackID']; ?></strong><br/>
                    <?php

	                $tmp = '';
	                if ($hesk_settings['sequential'])
	                {
    	                $tmp = ' ('.$hesklang['seqid'].': '.$ticket['id'].')';
	                } 
                    
                    echo $trackingID.'<br/>'.$tmp;?>
                </li>
                <li class="list-group-item">
                    <strong><?php echo $hesklang['replies']; ?></strong><br/>
                    <?php echo $replies; ?>
                </li>
                <li class="list-group-item">
                    <strong><?php echo $hesklang['owner']; ?></strong><br/>
                    <?php
                    echo isset($admins[$ticket['owner']]) ? $admins[$ticket['owner']] :
        	             ($can_assign_self ? $hesklang['unas'].' [<a href="assign_owner.php?track='.$trackingID.'&amp;owner='.$_SESSION['id'].'&amp;token='.hesk_token_echo(0).'">'.$hesklang['asss'].'</a>]' : $hesklang['unas']);
                    ?>
                </li>
                <li class="list-group-item">
                    <strong><?php echo $hesklang['created_on']; ?></strong><br/>
                    <?php echo hesk_date($ticket['dt'], true); ?>
                </li>
                <li class="list-group-item">
                    <strong><?php echo $hesklang['last_update']; ?></strong><br/>
                    <?php echo hesk_date($ticket['lastchange'], true); ?>    
                </li>
                <li class="list-group-item">
                    <strong><?php echo $hesklang['last_replier']; ?></strong><br/>
                    <?php echo $ticket['repliername']; ?>
                </li>
                <li class="list-group-item">
                    <strong><?php echo $hesklang['ts']; ?></strong><br/>
                    <?php
                    if ($can_reply || $can_edit)
                    {
                    ?>
	                <a href="Javascript:void(0)" onclick="Javascript:hesk_toggleLayerDisplay('modifytime')"><?php echo $ticket['time_worked']; ?></a>

                        <?php $t = hesk_getHHMMSS($ticket['time_worked']); ?>

		                <div id="modifytime" style="display:none">
			                <br />

			                <form method="post" action="admin_ticket.php" style="margin:0px; padding:0px;">
			                <table class="white">
			                <tr>
				                <td class="admin_gray"><?php echo $hesklang['hh']; ?>:</td>
				                <td class="admin_gray"><input type="text" name="h" value="<?php echo $t[0]; ?>" size="3" /></td>
			                </tr>
			                <tr>
				                <td class="admin_gray"><?php echo $hesklang['mm']; ?>:</td>
				                <td class="admin_gray"><input type="text" name="m" value="<?php echo $t[1]; ?>" size="3" /></td>
			                </tr>
			                <tr>
				                <td class="admin_gray"><?php echo $hesklang['ss']; ?>:</td>
				                <td class="admin_gray"><input type="text" name="s" value="<?php echo $t[2]; ?>" size="3" /></td>
			                </tr>
			                </table>

                            <br />

			                <input class="btn btn-default" type="submit" value="<?php echo $hesklang['save']; ?>" />
                            <a class="btn btn-default" href="Javascript:void(0)" onclick="Javascript:hesk_toggleLayerDisplay('modifytime')"><?php echo $hesklang['cancel']; ?></a>
                            <input type="hidden" name="track" value="<?php echo $trackingID; ?>" />
			                <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>" />
			                </form>
		                </div>

                    </td>
                    <?php
                    }
                    else
                    {
    	                echo $ticket['time_worked'];
                    }
                    ?>
                </li>
            </ul>
        </div>
    </div>
    <div class="col-md-10">
        <?php
        /* This will handle error, success and notice messages */
        hesk_handle_messages();

        /* Do we need or have any canned responses? */
        $can_options = hesk_printCanned();
        
        echo hesk_getAdminButtons();
        ?>   
        <div class="blankSpace"></div>
        <!-- BEGIN TICKT HEAD -->
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <td colspan="20" style="border-bottom: 0px">
                        <h3>
                        <?php
                        if ($ticket['archive'])
                        {
	                        echo '<span class="fa fa-tag"></span> &nbsp;';
                        }
                        if ($ticket['locked'])
                        {
                            echo '<span class="fa fa-lock"></span>&nbsp;';
                        }
                        echo $ticket['subject'];
                        ?></h3>
                    </td>
                </tr>
                <tr>
                    <td colspan="10" style="border-width: 0px"><?php echo $hesklang['created_on'].': '.hesk_date($ticket['dt']); ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $hesklang['last_update'].': '.hesk_date($ticket['lastchange']); ?></td>
                    <td colspan="10" style="border-width: 0px; text-align: right">
                        <?php
                            $random=rand(10000,99999);

                            $statusSql = 'SELECT `ID`, `ShortNameContentKey`, `IsStaffClosedOption`, `IsStaffReopenedStatus` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'statuses` WHERE `IsStaffClosedOption` = 1 OR `IsStaffReopenedStatus` = 1';
                            $statusRs = hesk_dbQuery($statusSql);
                            $staffClosedOptionStatus = array();
                            $staffReopenedStatus = array();
                            while ($statusRow = $statusRs->fetch_assoc())
                            {
                                if ($statusRow['IsStaffReopenedStatus'] == 1)
                                {
                                    $staffReopenedStatus['ID'] = $statusRow['ID'];
                                } else
                                {
                                    $staffClosedOptionStatus['ID'] = $statusRow['ID'];
                                }
                            }

                            $isTicketClosedSql = 'SELECT `IsClosed` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'statuses` WHERE `ID` = '.$ticket['status'];
                            $isTicketClosedRow = hesk_dbQuery($isTicketClosedSql)->fetch_assoc();
                            $isTicketClosed = $isTicketClosedRow['IsClosed'];

                            if ($isTicketClosed == 0) // Ticket is still open
                            {
                                echo '<a
		                        href="change_status.php?track='.$trackingID.'&amp;s='.$staffClosedOptionStatus['ID'].'&amp;Refresh='.$random.'&amp;token='.hesk_token_echo(0).'">'.$hesklang['close_action'].'</a>';
                            }
                            else
                            {
                                echo '<a
		                        href="change_status.php?track='.$trackingID.'&amp;s='.$staffReopenedStatus['ID'].'&amp;Refresh='.$random.'&amp;token='.hesk_token_echo(0).'">'.$hesklang['open_action'].'</a>';
                            }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="20" style="border-width: 0px">
                        <b><i><?php echo $hesklang['notes']; ?>: </i></b>
                        <?php
                        if ($can_reply)
                        {
                        ?>
                        &nbsp; <a href="Javascript:void(0)" onclick="Javascript:hesk_toggleLayerDisplay('notesform')"><?php echo $hesklang['addnote']; ?></a>
                        <?php
                        }
                        ?>

                        <div id="notesform" style="display:none">
	                    <form method="post" action="admin_ticket.php" style="margin:0px; padding:0px;">
	                        <textarea class="form-control" name="notemsg" rows="6" cols="60"></textarea><br />
	                        <input class="btn btn-default" type="submit" value="<?php echo $hesklang['s']; ?>"  /><input type="hidden" name="track" value="<?php echo $trackingID; ?>" />
                            <i><?php echo $hesklang['nhid']; ?></i>
                            <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>" />
                        </form>
	                    </div>   
                    </td>
                </tr>
                <?php
	            $res = hesk_dbQuery("SELECT t1.*, t2.`name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."notes` AS t1 LEFT JOIN `".hesk_dbEscape($hesk_settings['db_pfix'])."users` AS t2 ON t1.`who` = t2.`id` WHERE `ticket`='".intval($ticket['id'])."' ORDER BY t1.`id` " . ($hesk_settings['new_top'] ? 'DESC' : 'ASC') );
	            while ($note = hesk_dbFetchAssoc($res))
	            {
	            ?>
                <tr>
                <td colspan="20" class="notes" style="padding: 10px; border: #ffe6b8 1px solid">
                    <?php if ($can_del_notes || $note['who'] == $_SESSION['id']) { ?><p><a href="admin_ticket.php?track=<?php echo $trackingID; ?>&amp;Refresh=<?php echo mt_rand(10000,99999); ?>&amp;delnote=<?php echo $note['id']; ?>&amp;token=<?php hesk_token_echo(); ?>" onclick="return hesk_confirmExecute('<?php echo hesk_makeJsString($hesklang['delnote']).'?'; ?>');"><i class="fa fa-times"></i> Delete Note</a></p><?php }?>
                    <p><i><?php echo $hesklang['noteby']; ?> <b><?php echo ($note['name'] ? $note['name'] : $hesklang['e_udel']); ?></b></i> - <?php echo hesk_date($note['dt']); ?></p>
                    <p><?php echo $note['message']; ?></p>
                </td>
               
                </tr>
                <?php
	            }
                ?>
                <tr class="medLowPriority">
                    <?php
                          $hesk_settings['ticketColumnWidth'] = 5;

                           $options = array(
        	                    0 => '<option value="0">'.$hesklang['critical'].'</option>',
        	                    1 => '<option value="1">'.$hesklang['high'].'</option>',
                                2 => '<option value="2">'.$hesklang['medium'].'</option>',
                                3 => '<option value="3">'.$hesklang['low'].'</option>'
                            );

                           echo '<td colspan="'.$hesk_settings['ticketColumnWidth'].'" ';
                           if ($ticket['priority'] == 0) {echo 'class="criticalPriority">';}
                           elseif ($ticket['priority'] == 1) {echo 'class="highPriority">';}
                           else {echo 'class="medLowPriority">';}

                           echo '<p class="ticketPropertyTitle">'.$hesklang['priority'].'</p>';

                           if ($ticket['priority']==0) {echo '<p class="ticketPropertyText">'.$hesklang['critical'].'</p>';}
                            elseif ($ticket['priority']==1) {echo '<p class="ticketPropertyText">'.$hesklang['high'].'</p>';}
			                elseif ($ticket['priority']==2) {echo '<p class="ticketPropertyText">'.$hesklang['medium'].'</p>';}
			                else {echo '<p class="ticketPropertyText">'.$hesklang['low'].'</p>';}
                           echo '<br/>
                           <form style="margin-bottom:0;" id="changePriorityForm" action="priority.php" method="post">

                            <span style="white-space:nowrap;">
                            <select class="form-control" name="priority" onchange="document.getElementById(\'changePriorityForm\').submit();">
                            <option value="-1" selected="selected">'.$hesklang['priorityChange'].'</option>
                            ';
                            echo implode('',$options);
                            echo '
                            </select>

                            <input type="submit" style="display: none" value="'.$hesklang['go'].'" /><input type="hidden" name="track" value="'.$trackingID.'" />
                            <input type="hidden" name="token" value="'.hesk_token_echo(0).'" />
                            </span>

                            </form>
                           
                           </td>';

                        echo '<td colspan="'.$hesk_settings['ticketColumnWidth'].'"><p class="ticketPropertyTitle">'.$hesklang['status'].'</p>'; 
                            $status_options = array();
                            $results = hesk_dbQuery("SELECT `ID`, `ShortNameContentKey` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses`");
                            while ($row = $results->fetch_assoc())
                            {
                                $status_options[$row['ID']] = '<option value="'.$row['ID'].'">'.$hesklang[$row['ShortNameContentKey']].'</option>';
                            }

                            $ticketStatus = hesk_dbFetchAssoc(hesk_dbQuery("SELECT `TicketViewContentKey` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` WHERE ID = " .$ticket['status']));
                            echo '<p class="ticketPropertyText">'.$hesklang[$ticketStatus['TicketViewContentKey']].'</p>';
                            echo '<br/>
                            
                            <form role="form" id="changeStatusForm" style="margin-bottom:0;" action="change_status.php" method="post">
                                <span style="white-space:nowrap;">
                                    <select class="form-control" onchange="document.getElementById(\'changeStatusForm\').submit();" name="s">
	                                    <option value="-1" selected="selected">'.$hesklang['statusChange'].'</option>
                                        ' . implode('', $status_options) . '
                                    </select>

	                                <input type="submit" style="display:none;" value="'.$hesklang['go'].'" class="btn btn-default" /><input type="hidden" name="track" value="'.$trackingID.'" />
                                    <input type="hidden" name="token" value="'.hesk_token_echo(0).'" />
                                </span>
                            </form>
                            </td>';
                        echo '<td colspan="'.$hesk_settings['ticketColumnWidth'].'"><p class="ticketPropertyTitle">'.$hesklang['owner'].'</p>
                                <p class="ticketPropertyText">';
                                
                                echo isset($admins[$ticket['owner']]) ? $admins[$ticket['owner']] :
        	                    ($can_assign_self ? $hesklang['unas'].' [<a href="assign_owner.php?track='.$trackingID.'&amp;owner='.$_SESSION['id'].'&amp;token='.hesk_token_echo(0).'">'.$hesklang['asss'].'</a>]' : $hesklang['unas']);
                        
                                echo '</p><br/>';
                                
                                if (hesk_checkPermission('can_assign_others',0))
                                {
			                        echo'
                                    <form style="margin-bottom:0;" id="changeOwnerForm" action="assign_owner.php" method="post">
                                    <span style="white-space:nowrap;">
                                    <select class="form-control"  name="owner" onchange="document.getElementById(\'changeOwnerForm\').submit();">
			                        <option value="" selected="selected">'.$hesklang['ownerChange'].'</option>';
                                    if ($ticket['owner'])
                                    {
            	                        echo '<option value="-1"> &gt; '.$hesklang['unas'].' &lt; </option>';
                                    }

			                        foreach ($admins as $k=>$v)
			                        {
				                        if ($k != $ticket['owner'])
				                        {
					                        echo '<option value="'.$k.'">'.$v.'</option>';
				                        }
			                        }
			                        echo '</select>
			                        <input type="submit" style="display: none" value="'.$hesklang['go'].'" class="orangebutton" onmouseover="hesk_btn(this,\'orangebuttonover\');" onmouseout="hesk_btn(this,\'orangebutton\');" />
			                        <input type="hidden" name="track" value="'.$trackingID.'" />
			                        <input type="hidden" name="token" value="'.hesk_token_echo(0).'" />
                                    </span>';
                                }
                                echo '</form></td>';
                        echo '<td colspan="'.$hesk_settings['ticketColumnWidth'].'"><p class="ticketPropertyTitle">'.$hesklang['category'].'</p>
                                <p class="ticketPropertyText">'.$category['name'].'</p>';
                                
                                if ($can_change_cat)
                                {
                                echo '
                                
                                <br/>
                                <form style="margin-bottom:0;" id="changeCategory" action="move_category.php" method="post">

                                    <span style="white-space:nowrap;">
                                    <select name="category" class="form-control" onchange="document.getElementById(\'changeCategory\').submit();">
	                                <option value="-1" selected="selected">'.$hesklang['categoryChange'].'</option>
                                    '.$categories_options.'
                                    </select>

	                                <input type="submit" style="display: none" value="'.$hesklang['go'].'" /><input type="hidden" name="track" value="'.$trackingID.'" />
                                    <input type="hidden" name="token" value="'.hesk_token_echo(0).'" />
                                    </span>

                                </form>'; }
                                
                                echo '</td>';
                    ?>
                </tr>
            </tbody>
        </table>
        <!-- END TICKET HEAD -->

        <?php
        /* Reply form on top? */
        if ($can_reply && $hesk_settings['reply_top'] == 1)
        {
	        hesk_printReplyForm();
            echo '<br />';
        }
        ?>

        <!-- START TICKET REPLIES -->

        <?php
		    if ($hesk_settings['new_top'])
            {
        	    $i = hesk_printTicketReplies() ? 0 : 1;
            }
            else
            {
        	    $i = 1;
            }

            /* Make sure original message is in correct color if newest are on top */
            $color = 'class="ticketMessageContainer"';
		?>
        <div class="row ticketMessageContainer">
            <div class="col-md-3 col-xs-12">
                <div class="ticketName"><?php echo $ticket['name']; ?></div>
                <div class="ticketEmail"><?php echo $ticket['email']; ?></div>
                <div class="ticketEmail"><?php echo $hesklang['ip']; ?>: <?php echo $ticket['ip']; ?></div>
            </div>
            <div class="col-md-9 col-xs-12 pushMarginLeft">
                <div class="ticketMessageTop withBorder">
                    <!-- Action Buttons -->
                    <?php echo hesk_getAdminButtonsInTicket(0, $i); ?>
                    
                    <!-- Date -->
                    <p><br/><?php echo $hesklang['date']; ?>: <?php echo hesk_date($ticket['dt'], true); ?>
                    
                    <!-- Custom Fields Before Message -->
                        <?php
		                    foreach ($hesk_settings['custom_fields'] as $k=>$v)
		                    {
			                    if ($v['use'] && $v['place']==0)
		                        {
		                            echo '
				                    <p>'.$v['name'].': '.$ticket[$k].'</p>';
		                        }
		                    }
		                ?>
                    </div>
                <div class="ticketMessageBottom">
                     <!-- Message -->
                    <p><b><?php echo $hesklang['message']; ?>:</b></p>
		            <p class="message"><?php echo $ticket['message']; ?><br />&nbsp;</p>
                </div>
                <div class="ticketMessageTop">
                         <!-- Custom Fields after Message -->
                         <?php
		                    foreach ($hesk_settings['custom_fields'] as $k=>$v)
		                    {
			                    if ($v['use'] && $v['place'])
		                        {
		                            echo '
				                    <p>'.$v['name'].': '.$ticket[$k].'</p>';
		                        }
		                    }
	    	                /* Attachments */
    		                hesk_listAttachments($ticket['attachments'], $i);
		                ?>
                 </div>
            </div>
        </div>
        <?php
		if ( ! $hesk_settings['new_top'])
        {
        	hesk_printTicketReplies();
        }
		?>

        <?php
        /* Reply form on bottom? */
        if ($can_reply && ! $hesk_settings['reply_top'])
        {
	        hesk_printReplyForm();
        } 
        
        /* Display ticket history */
        if (strlen($ticket['history']))
        {
        ?>
        <h3><?php echo $hesklang['thist']; ?></h3>
        <div class="footerWithBorder blankSpace"></div>
        <ul><?php echo $ticket['history']; ?></ul>
        <?php }
        ?>
    </div>     
</div>
<?php


/* Clear unneeded session variables */
hesk_cleanSessionVars('ticket_message');
hesk_cleanSessionVars('time_worked');

require_once(HESK_PATH . 'inc/footer.inc.php');


/*** START FUNCTIONS ***/


function hesk_listAttachments($attachments='', $reply=0, $white=1)
{
	global $hesk_settings, $hesklang, $trackingID, $can_edit, $can_delete;

	/* Attachments disabled or not available */
	if ( ! $hesk_settings['attachments']['use'] || ! strlen($attachments) )
    {
    	return false;
    }

    /* Style and mousover/mousout */
    $tmp = $white ? 'White' : 'Blue';
    $style = 'class="option'.$tmp.'OFF" onmouseover="this.className=\'option'.$tmp.'ON\'" onmouseout="this.className=\'option'.$tmp.'OFF\'"';

	/* List attachments */
	echo '<p><b>'.$hesklang['attachments'].':</b><br />';
	$att=explode(',',substr($attachments, 0, -1));
	foreach ($att as $myatt)
	{
		list($att_id, $att_name) = explode('#', $myatt);

        /* Can edit and delete tickets? */
        if ($can_edit && $can_delete)
        {
        	echo '<a href="admin_ticket.php?delatt='.$att_id.'&amp;reply='.$reply.'&amp;track='.$trackingID.'&amp;'.$tmp.'&amp;Refresh='.mt_rand(10000,99999).'&amp;token='.hesk_token_echo(0).'" onclick="return hesk_confirmExecute(\''.hesk_makeJsString($hesklang['pda']).'\');"><i class="fa fa-times"></i></a> ';
        }

		echo '
		<a href="../download_attachment.php?att_id='.$att_id.'&amp;track='.$trackingID.'"><i class="fa fa-paperclip"></i></a>
		<a href="../download_attachment.php?att_id='.$att_id.'&amp;track='.$trackingID.'">'.$att_name.'</a><br />
        ';
	}
	echo '</p>';

    return true;
} // End hesk_listAttachments()


function hesk_getAdminButtons($reply=0,$white=1)
{
	global $hesk_settings, $hesklang, $ticket, $reply, $trackingID, $can_edit, $can_archive, $can_delete;

	$options = '<div class="btn-group" style="width: 100%">';

    /* Style and mousover/mousout */
    $tmp = $white ? 'White' : 'Blue';
    $style = 'class="option'.$tmp.'OFF" onmouseover="this.className=\'option'.$tmp.'ON\'" onmouseout="this.className=\'option'.$tmp.'OFF\'"';

    /* Lock ticket button */
	if ( /* ! $reply && */ $can_edit)
	{
		if ($ticket['locked'])
		{
			$des = $hesklang['tul'] . ' - ' . $hesklang['isloc'];
            $options .= '<a class="btn btn-default" href="lock.php?track='.$trackingID.'&amp;locked=0&amp;Refresh='.mt_rand(10000,99999).'&amp;token='.hesk_token_echo(0).'"><i class="fa fa-unlock"></i> '.$hesklang['tul'].'</a> ';
		}
		else
		{
			$des = $hesklang['tlo'] . ' - ' . $hesklang['isloc'];
            $options .= '<a class="btn btn-default" href="lock.php?track='.$trackingID.'&amp;locked=1&amp;Refresh='.mt_rand(10000,99999).'&amp;token='.hesk_token_echo(0).'"><i class="fa fa-lock"></i> '.$hesklang['tlo'].'</a> ';
		}
	}

	/* Tag ticket button */
	if ( /* ! $reply && */ $can_archive)
	{
		if ($ticket['archive'])
		{
        	$options .= '<a class="btn btn-default" href="archive.php?track='.$trackingID.'&amp;archived=0&amp;Refresh='.mt_rand(10000,99999).'&amp;token='.hesk_token_echo(0).'"><i class="fa fa-tag"></i>'.$hesklang['remove_archive'].'</a> ';
		}
		else
		{
        	$options .= '<a class="btn btn-default" href="archive.php?track='.$trackingID.'&amp;archived=1&amp;Refresh='.mt_rand(10000,99999).'&amp;token='.hesk_token_echo(0).'"><i class="fa fa-tag"></i> '.$hesklang['add_archive'].'</a> ';
		}
	}

	/* Import to knowledgebase button */
	if ($hesk_settings['kb_enable'] && hesk_checkPermission('can_man_kb',0))
	{
		$options .= '<a class="btn btn-default" href="manage_knowledgebase.php?a=import_article&amp;track='.$trackingID.'"><i class="fa fa-lightbulb-o"></i> '.$hesklang['import_kb'].'</a> ';
	}

	/* Print ticket button */
    $options .= '<a class="btn btn-default" href="../print.php?track='.$trackingID.'"><i class="fa fa-print"></i> '.$hesklang['printer_friendly'].'</a> ';

	/* Edit post */
	if ($can_edit)
	{
    	$tmp = $reply ? '&amp;reply='.$reply['id'] : '';
		$options .= '<a class="btn btn-default" href="edit_post.php?track='.$trackingID.$tmp.'"><i class="fa fa-pencil"></i> '.$hesklang['edtt'].'</a> ';
	}


	/* Delete ticket */
	if ($can_delete)
	{
		if ($reply)
		{
			$url = 'admin_ticket.php';
			$tmp = 'delete_post='.$reply['id'];
			$img = 'delete.png';
			$txt = $hesklang['delt'];
		}
		else
		{
			$url = 'delete_tickets.php';
			$tmp = 'delete_ticket=1';
			$img = 'delete_ticket.png';
			$txt = $hesklang['dele'];
		}
		$options .= '<a class="btn btn-default" href="'.$url.'?track='.$trackingID.'&amp;'.$tmp.'&amp;Refresh='.mt_rand(10000,99999).'&amp;token='.hesk_token_echo(0).'" onclick="return hesk_confirmExecute(\''.hesk_makeJsString($txt).'?\');"><i class="fa fa-ban"></i> '.$txt.'</a> ';
	}

    /* Return generated HTML */
    $options .= '</div>';
    return $options;

} // END hesk_getAdminButtons()

function hesk_getAdminButtonsInTicket($reply=0,$white=1)
{
	global $hesk_settings, $hesklang, $ticket, $reply, $trackingID, $can_edit, $can_archive, $can_delete;

	$options = '<div class="btn-group" style="width: 70%; text-align: right; margin-left: auto; margin-right: auto">';

    /* Style and mousover/mousout */
    $tmp = $white ? 'White' : 'Blue';
    $style = 'class="option'.$tmp.'OFF" onmouseover="this.className=\'option'.$tmp.'ON\'" onmouseout="this.className=\'option'.$tmp.'OFF\'"';

	/* Edit post */
	if ($can_edit)
	{
    	$tmp = $reply ? '&amp;reply='.$reply['id'] : '';
		$options .= '<a class="btn btn-default" href="edit_post.php?track='.$trackingID.$tmp.'"><i class="fa fa-pencil"></i> '.$hesklang['edtt'].'</a> ';
	}


	/* Delete ticket */
	if ($can_delete)
	{
		if ($reply)
		{
			$url = 'admin_ticket.php';
			$tmp = 'delete_post='.$reply['id'];
			$img = 'delete.png';
			$txt = $hesklang['delt'];
		}
		else
		{
			$url = 'delete_tickets.php';
			$tmp = 'delete_ticket=1';
			$img = 'delete_ticket.png';
			$txt = $hesklang['dele'];
		}
		$options .= '<a class="btn btn-default" href="'.$url.'?track='.$trackingID.'&amp;'.$tmp.'&amp;Refresh='.mt_rand(10000,99999).'&amp;token='.hesk_token_echo(0).'" onclick="return hesk_confirmExecute(\''.$txt.'?\');"><i class="fa fa-ban"></i> '.$txt.'</a> ';
	}

    /* Return generated HTML */
    $options .= '</div>';
    return $options;

} // END hesk_getAdminButtonsInTicket()


function print_form()
{
	global $hesk_settings, $hesklang;
    global $trackingID;

	/* Print header */
	require_once(HESK_PATH . 'inc/header.inc.php');

	/* Print admin navigation */
	require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
	?>

	</td>
	</tr>
	<tr>
	<td>

    &nbsp;<br />

	<?php
	/* This will handle error, success and notice messages */
	hesk_handle_messages();
	?>

	<div align="center">
	<table border="0" cellspacing="0" cellpadding="0" width="50%">
	<tr>
		<td width="7" height="7"><img src="../img/roundcornerslt.jpg" width="7" height="7" alt="" /></td>
		<td class="roundcornerstop"></td>
		<td><img src="../img/roundcornersrt.jpg" width="7" height="7" alt="" /></td>
	</tr>
	<tr>
		<td class="roundcornersleft">&nbsp;</td>
		<td>

	        <form action="admin_ticket.php" method="get">

	        <table width="100%" border="0" cellspacing="0" cellpadding="0">
	        <tr>
	                <td width="1"><img src="../img/existingticket.png" alt="" width="60" height="60" /></td>
	                <td>
	                <p><b><?php echo $hesklang['view_existing']; ?></a></b></p>
	                </td>
	        </tr>
	        <tr>
	                <td width="1">&nbsp;</td>
	                <td>&nbsp;</td>
	        </tr>
	        <tr>
	                <td width="1">&nbsp;</td>
	                <td>
	                <?php echo $hesklang['ticket_trackID']; ?>: <br /><input type="text" name="track" maxlength="20" size="35" value="<?php echo $trackingID; ?>" /><br />&nbsp;
	                </td>
	        </tr>
	        <tr>
	                <td width="1">&nbsp;</td>
	                <td><input type="submit" value="<?php echo $hesklang['view_ticket']; ?>" class="orangebutton" onmouseover="hesk_btn(this,'orangebuttonover');" onmouseout="hesk_btn(this,'orangebutton');" /><input type="hidden" name="Refresh" value="<?php echo rand(10000,99999); ?>"></td>
	        </tr>
	        </table>

	        </form>

		</td>
		<td class="roundcornersright">&nbsp;</td>
	</tr>
	<tr>
		<td><img src="../img/roundcornerslb.jpg" width="7" height="7" alt="" /></td>
		<td class="roundcornersbottom"></td>
		<td width="7" height="7"><img src="../img/roundcornersrb.jpg" width="7" height="7" alt="" /></td>
	</tr>
	</table>
	</div>

	<p>&nbsp;</p>
	<?php
	require_once(HESK_PATH . 'inc/footer.inc.php');
	exit();
} // End print_form()


function hesk_printTicketReplies() {
	global $hesklang, $hesk_settings, $result, $reply;

	$i = $hesk_settings['new_top'] ? 0 : 1;

	while ($reply = hesk_dbFetchAssoc($result))
	{
		$color = 'class="ticketMessageContainer"';

		$reply['dt'] = hesk_date($reply['dt'], true);
		?>
        <div class="row ticketMessageContainer">
            <div class="col-md-3 col-xs-12">
                <div class="ticketName"><?php echo $reply['name']; ?></div>
            </div>
            <div class="col-md-9 col-xs-12 pushMarginLeft">
                <div class="ticketMessageTop withBorder">
                    <?php echo hesk_getAdminButtonsInTicket(); ?>
                    <div class="blankSpace"></div>
                    <p><?php echo $hesklang['date']; ?>: <?php echo $reply['dt']; ?></p> 
                </div>
                <div class="ticketMessageBottom">
                    <p><b><?php echo $hesklang['message']; ?>:</b></p>
			        <p><?php echo $reply['message']; ?></p> 
                </div>
                <div class="ticketMessageTop pushMargin">
                     <?php hesk_listAttachments($reply['attachments'],$i);
                        /* Staff rating */
			            if ($hesk_settings['rating'] && $reply['staffid'])
			            {
				            if ($reply['rating']==1)
				            {
					            echo '<p class="rate">'.$hesklang['rnh'].'</p>';
				            }
				            elseif ($reply['rating']==5)
				            {
					            echo '<p class="rate">'.$hesklang['rh'].'</p>';
				            }
			            }

                        /* Show "unread reply" message? */
			            if ($reply['staffid'] && ! $reply['read'])
                        {
            	            echo '<p class="rate">'.$hesklang['unread'].'</p>';
                        }
                    ?>
                </div>
            </div>
        </div>
        <?php
	}

    return $i;

} // End hesk_printTicketReplies()


function hesk_printReplyForm() {
	global $hesklang, $hesk_settings, $ticket, $admins, $can_options, $options, $can_assign_self;
?>
<!-- START REPLY FORM -->

        <h3 style="text-align: left"><?php echo $hesklang['add_reply']; ?></h3>
        <div class="footerWithBorder"></div>
        <div class="blankSpace"></div>
        
        <form role="form" class="form-horizontal" method="post" action="admin_reply_ticket.php" enctype="multipart/form-data" name="form1" onsubmit="javascript:force_stop();return true;">
            <?php

            /* Ticket assigned to someone else? */
            if ($ticket['owner'] && $ticket['owner'] != $_SESSION['id'] && isset($admins[$ticket['owner']]) )
            {
    	        hesk_show_notice($hesklang['nyt'] . ' ' . $admins[$ticket['owner']]);
            }

            /* Ticket locked? */
            if ($ticket['locked'])
            {
    	        hesk_show_notice($hesklang['tislock']);
            }

            ?>

            <div class="form-group">
                <label for="time_worked" class="col-sm-3 control-label"><?php echo $hesklang['ts']; ?>:</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="time_worked" id="time_worked" size="10" value="<?php echo ( isset($_SESSION['time_worked']) ? hesk_getTime($_SESSION['time_worked']) : '00:00:00'); ?>" />
                </div>
                <div class="col-sm-3" style="text-align:  right">
                    <input type="button" class="btn btn-success" onclick="ss()" id="startb" value="<?php echo $hesklang['start']; ?>" />
		            <input type="button" class="btn btn-danger" onclick="r()" value="<?php echo $hesklang['reset']; ?>" />
                </div>
            </div>
            <?php

            /* Do we have any canned responses? */
            if (strlen($can_options))
            {
            ?>
            <div class="form-group">
                <label for="saved_replies" class="col-sm-3 control-label"><?php echo $hesklang['saved_replies']; ?>:</label>
                <div class="col-sm-9">
                    <label><input type="radio" name="mode" id="modeadd" value="1" checked="checked" /> <?php echo $hesklang['madd']; ?></label><br />
                    <label><input type="radio" name="mode" id="moderep" value="0" /> <?php echo $hesklang['mrep']; ?></label>
                   <select class="form-control" name="saved_replies" onchange="setMessage(this.value)">
		                <option value="0"> - <?php echo $hesklang['select_empty']; ?> - </option>
		                <?php echo $can_options; ?>
		            </select>
                </div>     
            </div>
            <?php
            }
            ?>
            <div class="form-group">
                <label for="message" class="col-sm-3 control-label"><?php echo $hesklang['message']; ?>: <font class="important">*</font></label>
                <div class="col-sm-9">
                    <span id="HeskMsg"><textarea class="form-control" name="message" id="message" rows="12" placeholder="<?php echo $hesklang['message']; ?>" cols="72"><?php if (isset($_SESSION['ticket_message'])) {echo stripslashes(hesk_input($_SESSION['ticket_message']));} ?></textarea></span>     
                </div>
            </div>
            <?php
	        /* attachments */
	        if ($hesk_settings['attachments']['use'])
            {
	        ?>
            <div class="form-group">
                <label for="attachments" class="col-sm-3 control-label"><?php echo $hesklang['attachments']; ?>:</label>
                <div class="col-sm-9">
                    <?php for ($i=1;$i<=$hesk_settings['attachments']['max_number'];$i++)
		            {
			            echo '<input type="file" name="attachment['.$i.']" size="50" /><br />';
		            }

                        echo '<a href="Javascript:void(0)" onclick="Javascript:hesk_window(\'../file_limits.php\',250,500);return false;">' . $hesklang['ful'] . '</a>';
		            ?>
                </div>     
            </div>
            <?php
	        }
	        ?>
            <div class="form-group">
                <label for="options" class="col-sm-3 control-label"><?php echo $hesklang['addop']; ?>:</label>
                <div class="col-sm-9">
                   <?php
                    if ($ticket['owner'] != $_SESSION['id'] && $can_assign_self)
                    {
		                if (empty($ticket['owner']))
		                {
			                echo '<label><input type="checkbox" name="assign_self" value="1" checked="checked" /> <b>'.$hesklang['asss2'].'</b></label><br />';
		                }
		                else
		                {
			                echo '<label><input type="checkbox" name="assign_self" value="1" /> '.$hesklang['asss2'].'</label><br />';
		                }
                    }

                   $statusSql = 'SELECT `ID` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'statuses` WHERE `IsStaffClosedOption` = 1';
                   $statusRow = hesk_dbQuery($statusSql)->fetch_assoc();
                   $staffClosedOptionStatus = array();
                   $staffClosedOptionStatus['ID'] = $statusRow['ID'];

	                if ($ticket['status'] != $staffClosedOptionStatus['ID'])
	                {
		                echo '<label><input type="checkbox" name="close" value="1" /> '.$hesklang['close_this_ticket'].'</label><br />';
	                }
	                ?>
	                <div class="form-inline"><label><input type="checkbox" name="set_priority" value="1" /> <?php echo $hesklang['change_priority']; ?> </label>
	                <select class="form-control" name="priority">
	                <?php echo implode('',$options); ?>
	                </select></div><br />
	                <label><input type="checkbox" name="signature" value="1" checked="checked" /> <?php echo $hesklang['attach_sign']; ?></label>
	                (<a href="profile.php"><?php echo $hesklang['profile_settings']; ?></a>)<br />
                    <label><input type="checkbox" name="no_notify" value="1" /> <?php echo $hesklang['dsen']; ?></label><br/><br/>
                    
                    <input type="hidden" name="orig_id" value="<?php echo $ticket['id']; ?>" />
                    <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>" />
                    <input class="btn btn-default" type="submit" value="<?php echo $hesklang['submit_reply']; ?>" />
   
                </div>
            </div>
        </form>

<!-- END REPLY FORM -->
<?php
} // End hesk_printReplyForm()


function hesk_printCanned()
{
	global $hesklang, $hesk_settings, $can_reply, $ticket;

	/* Can user reply to tickets? */
	if ( ! $can_reply)
    {
    	return '';
    }

	/* Get canned replies from the database */
	$res = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."std_replies` ORDER BY `reply_order` ASC");

	/* If no canned replies return empty */
    if ( ! hesk_dbNumRows($res) )
    {
    	return '';
    }

	/* We do have some replies, print the required Javascript and select field options */
	$can_options = '';
	?>
	<script language="javascript" type="text/javascript"><!--
    // -->
    var myMsgTxt = new Array();
	myMsgTxt[0]='';

	<?php
	while ($mysaved = hesk_dbFetchRow($res))
	{
	    $can_options .= '<option value="' . $mysaved[0] . '">' . $mysaved[1]. "</option>\n";
	    echo 'myMsgTxt['.$mysaved[0].']=\''.str_replace("\r\n","\\r\\n' + \r\n'", addslashes($mysaved[2]))."';\n";
	}

	?>

	function setMessage(msgid)
    {
		var myMsg=myMsgTxt[msgid];

        if (myMsg == '')
        {
        	if (document.form1.mode[1].checked)
            {
				document.getElementById('message').value = '';
            }
            return true;
        }

		myMsg = myMsg.replace(/%%HESK_NAME%%/g, '<?php echo hesk_jsString($ticket['name']); ?>');
		myMsg = myMsg.replace(/%%HESK_EMAIL%%/g, '<?php echo hesk_jsString($ticket['email']); ?>');
		myMsg = myMsg.replace(/%%HESK_custom1%%/g, '<?php echo hesk_jsString($ticket['custom1']); ?>');
		myMsg = myMsg.replace(/%%HESK_custom2%%/g, '<?php echo hesk_jsString($ticket['custom2']); ?>');
		myMsg = myMsg.replace(/%%HESK_custom3%%/g, '<?php echo hesk_jsString($ticket['custom3']); ?>');
		myMsg = myMsg.replace(/%%HESK_custom4%%/g, '<?php echo hesk_jsString($ticket['custom4']); ?>');
		myMsg = myMsg.replace(/%%HESK_custom5%%/g, '<?php echo hesk_jsString($ticket['custom5']); ?>');
		myMsg = myMsg.replace(/%%HESK_custom6%%/g, '<?php echo hesk_jsString($ticket['custom6']); ?>');
		myMsg = myMsg.replace(/%%HESK_custom7%%/g, '<?php echo hesk_jsString($ticket['custom7']); ?>');
		myMsg = myMsg.replace(/%%HESK_custom8%%/g, '<?php echo hesk_jsString($ticket['custom8']); ?>');
		myMsg = myMsg.replace(/%%HESK_custom9%%/g, '<?php echo hesk_jsString($ticket['custom9']); ?>');
		myMsg = myMsg.replace(/%%HESK_custom10%%/g, '<?php echo hesk_jsString($ticket['custom10']); ?>');
		myMsg = myMsg.replace(/%%HESK_custom11%%/g, '<?php echo hesk_jsString($ticket['custom11']); ?>');
		myMsg = myMsg.replace(/%%HESK_custom12%%/g, '<?php echo hesk_jsString($ticket['custom12']); ?>');
		myMsg = myMsg.replace(/%%HESK_custom13%%/g, '<?php echo hesk_jsString($ticket['custom13']); ?>');
		myMsg = myMsg.replace(/%%HESK_custom14%%/g, '<?php echo hesk_jsString($ticket['custom14']); ?>');
		myMsg = myMsg.replace(/%%HESK_custom15%%/g, '<?php echo hesk_jsString($ticket['custom15']); ?>');
		myMsg = myMsg.replace(/%%HESK_custom16%%/g, '<?php echo hesk_jsString($ticket['custom16']); ?>');
		myMsg = myMsg.replace(/%%HESK_custom17%%/g, '<?php echo hesk_jsString($ticket['custom17']); ?>');
		myMsg = myMsg.replace(/%%HESK_custom18%%/g, '<?php echo hesk_jsString($ticket['custom18']); ?>');
		myMsg = myMsg.replace(/%%HESK_custom19%%/g, '<?php echo hesk_jsString($ticket['custom19']); ?>');
		myMsg = myMsg.replace(/%%HESK_custom20%%/g, '<?php echo hesk_jsString($ticket['custom20']); ?>');

	    if (document.getElementById)
        {
			if (document.getElementById('moderep').checked)
            {
				document.getElementById('HeskMsg').innerHTML='<textarea class="form-control" name="message" id="message" rows="12" cols="72">'+myMsg+'</textarea>';
            }
            else
            {
            	var oldMsg = document.getElementById('message').value;
		        document.getElementById('HeskMsg').innerHTML='<textarea class="form-control" name="message" id="message" rows="12" cols="72">'+oldMsg+myMsg+'</textarea>';
            }
	    }
        else
        {
			if (document.form1.mode[0].checked)
            {
				document.form1.message.value=myMsg;
            }
            else
            {
            	var oldMsg = document.form1.message.value;
		        document.form1.message.value=oldMsg+myMsg;
            }
	    }

	}
	//-->
	</script>
    <?php

    /* Return options for select box */
    return $can_options;

} // End hesk_printCanned()
?>
