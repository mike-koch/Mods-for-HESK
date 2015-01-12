<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.6.0 beta 1 from 30th December 2014
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

/* Check if this is a valid include */
if (!defined('IN_SCRIPT')) {die('Invalid attempt');}


/* Get includes for SMTP */
if ($hesk_settings['smtp'])
{
	require(HESK_PATH . 'inc/mail/smtp.php');
    if (strlen($hesk_settings['smtp_user']) || strlen($hesk_settings['smtp_password']))
	{
		require_once(HESK_PATH . 'inc/mail/sasl/sasl.php');
	}
}

function hesk_notifyCustomerForVerifyEmail($email_template = 'verify_email', $activationKey)
{
    global $hesk_settings, $ticket;

    if (defined('HESK_DEMO'))
    {
        return true;
    }

    // Format email subject and message
    $subject = hesk_getEmailSubject($email_template, $ticket);
    $message = hesk_getEmailMessage($email_template, $ticket);
    $activationUrl = $hesk_settings['hesk_url'] . '/verifyemail.php?key=%%ACTIVATIONKEY%%';
    $message = str_replace('%%VERIFYURL%%', $activationUrl, $message);
    $message = str_replace('%%ACTIVATIONKEY%%', $activationKey, $message);

    hesk_mail($ticket['email'], $subject, $message);
}


function hesk_notifyCustomer($email_template = 'new_ticket')
{
	global $hesk_settings, $hesklang, $ticket;

	// Demo mode
	if ( defined('HESK_DEMO') )
	{
		return true;
	}

	// Format email subject and message
	$subject = hesk_getEmailSubject($email_template,$ticket);
	$message = hesk_getEmailMessage($email_template,$ticket);

	// Send e-mail
	hesk_mail($ticket['email'], $subject, $message);

    return true;

} // END hesk_notifyCustomer()


function hesk_notifyAssignedStaff($autoassign_owner, $email_template, $type = 'notify_assigned')
{
	global $hesk_settings, $hesklang, $ticket;

	// Demo mode
	if ( defined('HESK_DEMO') )
	{
		return true;
	}

	$ticket['owner'] = intval($ticket['owner']);

    /* Need to lookup owner info from the database? */
    if ($autoassign_owner === false)
    {
		$res = hesk_dbQuery("SELECT `name`, `email`,`language`,`notify_assigned`,`notify_reply_my` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `id`='" . $ticket['owner'] . "' LIMIT 1");

        $autoassign_owner = hesk_dbFetchAssoc($res);
		$hesk_settings['user_data'][$ticket['owner']] = $autoassign_owner;

		/* If owner selected not to be notified or invalid stop here */
		if ( empty($autoassign_owner[$type]) )
        {
        	return false;
        }
    }

	/* Set new language if required */
    hesk_setLanguage($autoassign_owner['language']);

	/* Format email subject and message for staff */
    $subject = hesk_getEmailSubject($email_template,$ticket);
	$message = hesk_getEmailMessage($email_template,$ticket,1);

	/* Send email to staff */
	hesk_mail($autoassign_owner['email'], $subject, $message);

    /* Reset language to original one */
    hesk_resetLanguage();

    return true;

} // END hesk_notifyAssignedStaff()


function hesk_notifyStaff($email_template,$sql_where,$is_ticket=1)
{
	global $hesk_settings, $hesklang, $ticket;

	// Demo mode
	if ( defined('HESK_DEMO') )
	{
		return true;
	}

	$admins = array();

	$res = hesk_dbQuery("SELECT `email`,`language`,`isadmin`,`categories` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE $sql_where ORDER BY `language`");
	while ($myuser = hesk_dbFetchAssoc($res))
	{
		/* Is this an administrator? */
		if ($myuser['isadmin'])
		{
			$admins[] = array('email' => $myuser['email'], 'language' => $myuser['language']);
			continue;
		}

		/* Not admin, is he/she allowed this category? */
		$myuser['categories']=explode(',',$myuser['categories']);
		if (in_array($ticket['category'],$myuser['categories']))
		{
			$admins[] = array('email' => $myuser['email'], 'language' => $myuser['language']);
			continue;
        }
	}

	if (count($admins) > 0)
	{
    	/* Make sure each user gets email in his/her preferred language */
        $current_language = 'NONE';
        $recipients = array();

		/* Loop through staff */
        foreach ($admins as $admin)
        {
        	/* If admin language is NULL force default HESK language */
        	if ( ! $admin['language'] || ! isset($hesk_settings['languages'][$admin['language']]) )
            {
            	$admin['language'] = HESK_DEFAULT_LANGUAGE;
            }

            /* Generate message or add email to the list of recepients */
        	if ($admin['language'] == $current_language)
            {
            	/* We already have the message, just add email to the recipients list */
                $recipients[] = $admin['email'];
            }
            else
            {
            	/* Send email messages in previous languages (if required) */
                if ($current_language != 'NONE')
                {
					/* Send e-mail to staff */
					hesk_mail(implode(',',$recipients), $subject, $message );

                    /* Reset list of email addresses */
                    $recipients = array();
                }

				/* Set new language */
				hesk_setLanguage($admin['language']);

				/* Format staff email subject and message for this language */
                $subject = hesk_getEmailSubject($email_template,$ticket);
				$message = hesk_getEmailMessage($email_template,$ticket,$is_ticket);

				/* Add email to the recipients list */
				$recipients[] = $admin['email'];

				/* Remember the last processed language */
				$current_language = $admin['language'];
            }
        }

        /* Send email messages to the remaining staff */
		hesk_mail(implode(',',$recipients), $subject, $message );

		/* Reset language to original one */
		hesk_resetLanguage();
	}

    return true;

} // END hesk_notifyStaff()


function hesk_validEmails()
{
	global $hesklang;

	return  array(

		/*** Emails sent to CLIENT ***/

		// --> Send reminder about existing tickets
		'forgot_ticket_id' => $hesklang['forgot_ticket_id'],

		// --> Staff replied to a ticket
		'new_reply_by_staff' => $hesklang['new_reply_by_staff'],

		// --> New ticket submitted
		'new_ticket' => $hesklang['ticket_received'],

        // --> Verify email
        'verify_email' => $hesklang['verify_email'],

        // --> Ticket closed
        'ticket_closed' => $hesklang['ticket_closed'],


		/*** Emails sent to STAFF ***/

		// --> Ticket moved to a new category
		'category_moved' => $hesklang['category_moved'],

		// --> Client replied to a ticket
		'new_reply_by_customer' => $hesklang['new_reply_by_customer'],

		// --> New ticket submitted
		'new_ticket_staff' => $hesklang['new_ticket_staff'],

		// --> New ticket assigned to staff
		'ticket_assigned_to_you'=> $hesklang['ticket_assigned_to_you'],

		// --> New private message
		'new_pm' => $hesklang['new_pm'],

		// --> New note by someone to a ticket assigned to you
		'new_note' => $hesklang['new_note'],

        // --> Staff password reset email
        'reset_password' => $hesklang['reset_password'],

    );
} // END hesk_validEmails()


function hesk_mail($to,$subject,$message)
{
	global $hesk_settings, $hesklang;

	// Demo mode
	if ( defined('HESK_DEMO') )
	{
		return true;
	}

    // Encode subject to UTF-8
    $subject = "=?UTF-8?B?" . base64_encode( hesk_html_entity_decode($subject) ) . "?=";

    // Setup "name <email>" for headers
    if ($hesk_settings['noreply_name'])
    {
    	$hesk_settings['from_header'] = "=?UTF-8?B?" . base64_encode( hesk_html_entity_decode($hesk_settings['noreply_name']) ) . "?= <" . $hesk_settings['noreply_mail'] . ">";
    }
    else
    {
    	$hesk_settings['from_header'] = $hesk_settings['noreply_mail'];
    }

	// Uncomment for debugging
	# echo "<p>TO: $to<br >SUBJECT: $subject<br >MSG: $message</p>";
	# return true;

    // Use PHP's mail function
	if ( ! $hesk_settings['smtp'])
    {
    	// Set additional headers
		$headers = "From: $hesk_settings[from_header]\n";
		$headers.= "Reply-To: $hesk_settings[from_header]\n";
		$headers.= "Return-Path: $hesk_settings[webmaster_mail]\n";
		$headers.= "Date: " . date(DATE_RFC2822) . "\n";
		$headers.= "Content-Type: text/plain; charset=" . $hesklang['ENCODING'];

		// Send using PHP mail() function
        ob_start();
		mail($to,$subject,$message,$headers);
        $tmp = trim(ob_get_contents());
        ob_end_clean();

        return (strlen($tmp)) ? $tmp : true;
    }

    // Use a SMTP server directly instead
	$smtp = new smtp_class;
	$smtp->host_name	= $hesk_settings['smtp_host_name'];
	$smtp->host_port	= $hesk_settings['smtp_host_port'];
	$smtp->timeout		= $hesk_settings['smtp_timeout'];
    $smtp->ssl			= $hesk_settings['smtp_ssl'];
    $smtp->start_tls	= $hesk_settings['smtp_tls'];
	$smtp->user			= $hesk_settings['smtp_user'];
	$smtp->password		= hesk_htmlspecialchars_decode($hesk_settings['smtp_password']);
    $smtp->debug		= 1;

    // Start output buffering so that any errors don't break headers
    ob_start();

    // Send the e-mail using SMTP
    $to_arr = explode(',',$to);
	if ( ! $smtp->SendMessage($hesk_settings['noreply_mail'], $to_arr, array(
				"From: $hesk_settings[from_header]",
				"To: $to",
                "Reply-To: $hesk_settings[from_header]",
                "Return-Path: $hesk_settings[webmaster_mail]",
				"Subject: " . $subject,
				"Date: " . date(DATE_RFC2822),
                "Content-Type: text/plain; charset=" . $hesklang['ENCODING']
			), $message))
    {
		// Suppress errors unless we are in debug mode
		if ($hesk_settings['debug_mode'])
		{
			$error = $hesklang['cnsm'] . ' ' . $to . '<br /><br />' .
					 $hesklang['error'] . ': ' . htmlspecialchars($smtp->error). '<br /><br />' .
					 '<textarea name="smtp_log" rows="10" cols="60">' . ob_get_contents() . '</textarea>';
			ob_end_clean();
			hesk_error($error);
		}
        else
        {
			$_SESSION['HESK_2ND_NOTICE']  = true;
            $_SESSION['HESK_2ND_MESSAGE'] = $hesklang['esf'] . ' ' . $hesklang['contact_webmsater'] . ' <a href="mailto:' . $hesk_settings['webmaster_mail'] . '">' . $hesk_settings['webmaster_mail'] . '</a>';
        }
    }

    ob_end_clean();

	return true;

} // END hesk_mail()


function hesk_getEmailSubject($eml_file, $ticket='', $is_ticket=1, $strip=0)
{
	global $hesk_settings, $hesklang;

	// Demo mode
	if ( defined('HESK_DEMO') )
	{
		return '';
	}

	/* Get list of valid emails */
    $valid_emails = hesk_validEmails();

	/* Verify this is a valid email include */
    if ( ! isset($valid_emails[$eml_file]))
    {
    	hesk_error($hesklang['inve']);
    }
    else
    {
    	$msg = $valid_emails[$eml_file];
    }

    /* If not a ticket-related email return subject as is */
    if ( ! $ticket )
    {
    	return $msg;
    }

	/* Strip slashes from the subject only if it's a new ticket */
	if ($strip)
	{
		$ticket['subject'] = stripslashes($ticket['subject']);
	}

    /* Not a ticket, but has some info in the $ticket array */
    if ( ! $is_ticket)
    {
    	return str_replace('%%SUBJECT%%', $ticket['subject'], $msg);
    }

 	/* Set category title */
	$ticket['category'] = hesk_msgToPlain(hesk_getCategoryName($ticket['category']), 1);

	/* Get priority */
	switch ($ticket['priority'])
	{
		case 0:
			$ticket['priority'] = $hesklang['critical'];
			break;
		case 1:
			$ticket['priority'] = $hesklang['high'];
			break;
		case 2:
			$ticket['priority'] = $hesklang['medium'];
			break;
		default:
			$ticket['priority'] = $hesklang['low'];
	}

    /* Set status */
	switch ($ticket['status'])
	{
		case 1:
			$ticket['status'] = $hesklang['wait_reply'];
			break;
		case 2:
			$ticket['status'] = $hesklang['replied'];
			break;
		case 3:
			$ticket['status'] = $hesklang['closed'];
			break;
		case 4:
			$ticket['status'] = $hesklang['in_progress'];
			break;
		case 5:
			$ticket['status'] = $hesklang['on_hold'];
			break;
		default:
			$ticket['status'] = $hesklang['open'];
	}

	/* Replace all special tags */
	$msg = str_replace('%%SUBJECT%%',	$ticket['subject'],		$msg);
	$msg = str_replace('%%TRACK_ID%%',	$ticket['trackid'],		$msg);
	$msg = str_replace('%%CATEGORY%%',	$ticket['category'],	$msg);
	$msg = str_replace('%%PRIORITY%%',	$ticket['priority'],	$msg);
    $msg = str_replace('%%STATUS%%',	$ticket['status'],		$msg);

	return $msg;

} // hesk_getEmailSubject()


function hesk_getEmailMessage($eml_file, $ticket, $is_admin=0, $is_ticket=1, $just_message=0)
{
	global $hesk_settings, $hesklang;

	// Demo mode
	if ( defined('HESK_DEMO') )
	{
		return '';
	}

	/* Get list of valid emails */
    $valid_emails = hesk_validEmails();

	/* Verify this is a valid email include */
    if ( ! isset($valid_emails[$eml_file]))
    {
    	hesk_error($hesklang['inve']);
    }

	/* Get email template */
	$eml_file = 'language/' . $hesk_settings['languages'][$hesk_settings['language']]['folder'] . '/emails/' . $eml_file . '.txt';

    if (file_exists(HESK_PATH . $eml_file))
    {
		$msg = file_get_contents(HESK_PATH . $eml_file);
    }
    else
    {
    	hesk_error($hesklang['emfm'].': '.$eml_file);
    }

    /* Return just the message without any processing? */
    if ($just_message)
    {
    	return $msg;
    }

	// Convert any entities in site title to plain text
	$hesk_settings['site_title'] = hesk_msgToPlain($hesk_settings['site_title'], 1);

    /* If it's not a ticket-related mail (like "a new PM") just process quickly */
    if ( ! $is_ticket)
    {
		$trackingURL = $hesk_settings['hesk_url'] . '/' . $hesk_settings['admin_dir'] . '/mail.php?a=read&id=' . intval($ticket['id']);

		$msg = str_replace('%%NAME%%',		$ticket['name']					,$msg);
		$msg = str_replace('%%SUBJECT%%',	$ticket['subject']				,$msg);
		$msg = str_replace('%%TRACK_URL%%',	$trackingURL					,$msg);
		$msg = str_replace('%%SITE_TITLE%%',$hesk_settings['site_title']	,$msg);
		$msg = str_replace('%%SITE_URL%%',	$hesk_settings['site_url']		,$msg);

		if ( isset($ticket['message']) )
		{
			return str_replace('%%MESSAGE%%', $ticket['message'], $msg);
		}
		else
		{
			return $msg;
		}
    }

    // Is email required to view ticket (for customers only)?
	$hesk_settings['e_param'] = $hesk_settings['email_view_ticket'] ? '&e=' . rawurlencode($ticket['email']) : '';

    /* Generate the ticket URLs */
    $trackingURL = $hesk_settings['hesk_url'];
	$trackingURL.= $is_admin ? '/' . $hesk_settings['admin_dir'] . '/admin_ticket.php' : '/ticket.php';
    $trackingURL.= '?track='.$ticket['trackid'].($is_admin ? '' : $hesk_settings['e_param']).'&Refresh='.rand(10000,99999);

 	/* Set category title */
	$ticket['category'] = hesk_msgToPlain(hesk_getCategoryName($ticket['category']), 1);

	/* Set priority title */
	switch ($ticket['priority'])
	{
		case 0:
			$ticket['priority'] = $hesklang['critical'];
			break;
		case 1:
			$ticket['priority'] = $hesklang['high'];
			break;
		case 2:
			$ticket['priority'] = $hesklang['medium'];
			break;
		default:
			$ticket['priority'] = $hesklang['low'];
	}

    /* Get owner name */
    $ticket['owner'] = hesk_msgToPlain( hesk_getOwnerName($ticket['owner']), 1);

    /* Set status */
	switch ($ticket['status'])
	{
		case 1:
			$ticket['status'] = $hesklang['wait_reply'];
			break;
		case 2:
			$ticket['status'] = $hesklang['replied'];
			break;
		case 3:
			$ticket['status'] = $hesklang['closed'];
			break;
		case 4:
			$ticket['status'] = $hesklang['in_progress'];
			break;
		case 5:
			$ticket['status'] = $hesklang['on_hold'];
			break;
		default:
			$ticket['status'] = $hesklang['open'];
	}

	/* Replace all special tags */
	$msg = str_replace('%%NAME%%',		$ticket['name']				,$msg);
	$msg = str_replace('%%SUBJECT%%',	$ticket['subject']			,$msg);
	$msg = str_replace('%%TRACK_ID%%',	$ticket['trackid']			,$msg);
	$msg = str_replace('%%TRACK_URL%%',	$trackingURL				,$msg);
	$msg = str_replace('%%SITE_TITLE%%',$hesk_settings['site_title'],$msg);
	$msg = str_replace('%%SITE_URL%%',	$hesk_settings['site_url']	,$msg);
	$msg = str_replace('%%CATEGORY%%',	$ticket['category']			,$msg);
	$msg = str_replace('%%PRIORITY%%',	$ticket['priority']			,$msg);
    $msg = str_replace('%%OWNER%%',		$ticket['owner']			,$msg);
    $msg = str_replace('%%STATUS%%',	$ticket['status']			,$msg);
    $msg = str_replace('%%EMAIL%%',		$ticket['email']			,$msg);
    $msg = str_replace('%%CREATED%%',	$ticket['dt']				,$msg);
    $msg = str_replace('%%UPDATED%%',	$ticket['lastchange']		,$msg);
    $msg = str_replace('%%ID%%',		$ticket['id']				,$msg);

	/* All custom fields */
	foreach ($hesk_settings['custom_fields'] as $k=>$v)
	{
		if ($v['use'])
		{
        	if ($v['type'] == 'checkbox')
            {
            	$ticket[$k] = str_replace("<br />","\n",$ticket[$k]);
            }

			$msg = str_replace('%%'.strtoupper($k).'%%',stripslashes($ticket[$k]),$msg);
		}
        else
        {
        	$msg = str_replace('%%'.strtoupper($k).'%%','',$msg);
        }
	}

	// Is message tag in email template?
	if (strpos($msg, '%%MESSAGE%%') !== false)
	{
		// Replace message
		$msg = str_replace('%%MESSAGE%%',$ticket['message'],$msg);

		// Add direct links to any attachments at the bottom of the email message
		if ($hesk_settings['attachments']['use'] && isset($ticket['attachments']) && strlen($ticket['attachments']) )
		{
			$msg .= "\n\n\n" . $hesklang['fatt'];

			$att = explode(',', substr($ticket['attachments'], 0, -1));
			foreach ($att as $myatt)
			{
				list($att_id, $att_name) = explode('#', $myatt);
				$msg .= "\n\n" . $att_name . "\n" . $hesk_settings['hesk_url'] . '/download_attachment.php?att_id='.$att_id.'&track='.$ticket['trackid'].$hesk_settings['e_param'];
			}
		}

		// For customer notifications: if we allow email piping/pop 3 fetching and
		// stripping quoted replies add an "reply above this line" tag
		if ( ! $is_admin && ($hesk_settings['email_piping'] || $hesk_settings['pop3']) && $hesk_settings['strip_quoted'])
		{
			$msg = $hesklang['EMAIL_HR'] . "\n\n" . $msg;
		}
	}

    return $msg;

} // END hesk_getEmailMessage
