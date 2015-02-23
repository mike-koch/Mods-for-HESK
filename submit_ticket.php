<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.6.0 from 22nd February 2015
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

// Try to detect some simple SPAM bots
if ( ! isset($_POST['hx']) || $_POST['hx'] != 3 || ! isset($_POST['hy']) || $_POST['hy'] != '' || isset($_POST['phone']) )
{
    header('HTTP/1.1 403 Forbidden');
    exit();
}

// Get all the required files and functions
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'modsForHesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');

// Are we in maintenance mode?
hesk_check_maintenance();

// Are we in "Knowledgebase only" mode?
hesk_check_kb_only();

hesk_load_database_functions();
require(HESK_PATH . 'inc/email_functions.inc.php');
require(HESK_PATH . 'inc/posting_functions.inc.php');

// We only allow POST requests to this file
if ( $_SERVER['REQUEST_METHOD'] != 'POST' )
{
	header('Location: index.php?a=add');
	exit();
}

// Check for POST requests larger than what the server can handle
if ( empty($_POST) && ! empty($_SERVER['CONTENT_LENGTH']) )
{
	hesk_error($hesklang['maxpost']);
}

// Block obvious spammers trying to inject email headers
if ( preg_match("/\n|\r|\t|%0A|%0D|%08|%09/", hesk_POST('name') . hesk_POST('subject') ) )
{
	header('HTTP/1.1 403 Forbidden');
    exit();
}

hesk_session_start();

// A security check - not needed here, but uncomment if you require it
# hesk_token_check();

// Prevent submitting multiple tickets by reloading submit_ticket.php page
if (isset($_SESSION['already_submitted']))
{
	hesk_forceStop();
}

// Connect to database
hesk_dbConnect();

$hesk_error_buffer = array();

// Check anti-SPAM question
if ($hesk_settings['question_use'])
{
	$question = hesk_input( hesk_POST('question') );

	if ( strlen($question) == 0)
	{
		$hesk_error_buffer['question'] = $hesklang['q_miss'];
	}
	elseif (strtolower($question) != strtolower($hesk_settings['question_ans']))
	{
		$hesk_error_buffer['question'] = $hesklang['q_wrng'];
	}
	else
	{
		$_SESSION['c_question'] = $question;
	}
}

// Check anti-SPAM image
if ($hesk_settings['secimg_use'] && ! isset($_SESSION['img_verified']))
{
	// Using ReCaptcha?
	if ($hesk_settings['recaptcha_use'] == 1)
	{
		require(HESK_PATH . 'inc/recaptcha/recaptchalib.php');

		$resp = recaptcha_check_answer($hesk_settings['recaptcha_private_key'],
		$_SERVER['REMOTE_ADDR'],
		hesk_POST('recaptcha_challenge_field', ''),
		hesk_POST('recaptcha_response_field', '')
        );
		if ($resp->is_valid)
		{
			$_SESSION['img_verified']=true;
		}
		else
		{
			$hesk_error_buffer['mysecnum']=$hesklang['recaptcha_error'];
		}

	}
    // Using ReCaptcha API v2?
    elseif ($hesk_settings['recaptcha_use'] == 2)
    {
        require(HESK_PATH . 'inc/recaptcha/recaptchalib_v2.php');

        $resp = null;
        $reCaptcha = new ReCaptcha($hesk_settings['recaptcha_private_key']);

        // Was there a reCAPTCHA response?
        if ( isset($_POST["g-recaptcha-response"]) )
        {
            $resp = $reCaptcha->verifyResponse($_SERVER["REMOTE_ADDR"], hesk_POST("g-recaptcha-response") );
        }

        if ($resp != null && $resp->success)
        {
            $_SESSION['img_verified']=true;
        }
        else
        {
            $hesk_error_buffer['mysecnum']=$hesklang['recaptcha_error'];
        }
    }
	// Using PHP generated image
	else
	{
		$mysecnum = intval( hesk_POST('mysecnum', 0) );

		if ( empty($mysecnum) )
		{
			$hesk_error_buffer['mysecnum']=$hesklang['sec_miss'];
		}
		else
		{
			require(HESK_PATH . 'inc/secimg.inc.php');
			$sc = new PJ_SecurityImage($hesk_settings['secimg_sum']);
			if ( isset($_SESSION['checksum']) && $sc->checkCode($mysecnum, $_SESSION['checksum']) )
			{
				$_SESSION['img_verified']=true;
			}
			else
			{
				$hesk_error_buffer['mysecnum']=$hesklang['sec_wrng'];
			}
		}
	}
}

$tmpvar['name']	 = hesk_input( hesk_POST('name') ) or $hesk_error_buffer['name']=$hesklang['enter_your_name'];
$tmpvar['email'] = hesk_validateEmail( hesk_POST('email'), 'ERR', 0) or $hesk_error_buffer['email']=$hesklang['enter_valid_email'];

if ($hesk_settings['confirm_email'])
{
	$tmpvar['email2'] = hesk_input( hesk_POST('email2') ) or $hesk_error_buffer['email2']=$hesklang['confemail2'];

	if (strlen($tmpvar['email2']) && ( strtolower($tmpvar['email']) != strtolower($tmpvar['email2']) ))
	{
	    $tmpvar['email2'] = '';
	    $_POST['email2'] = '';
        $_SESSION['c_email2'] = '';
        $_SESSION['isnotice'][] = 'email';
	    $hesk_error_buffer['email2']=$hesklang['confemaile'];
	}
	else
	{
		$_SESSION['c_email2'] = $_POST['email2'];
	}
}

$tmpvar['category'] = intval( hesk_POST('category') ) or $hesk_error_buffer['category']=$hesklang['sel_app_cat'];

// Do we allow customer to select priority?
if ($hesk_settings['cust_urgency'])
{
    $tmpvar['priority'] = intval( hesk_POST('priority') );

    // We don't allow customers select "Critical". If priority is not valid set it to "low".
    if ($tmpvar['priority'] < 1 || $tmpvar['priority'] > 3)
    {
        // If we are showing "Click to select" priority needs to be selected
        if ($hesk_settings['select_pri'])
        {
            $tmpvar['priority'] = -1;
            $hesk_error_buffer['priority'] = $hesklang['select_priority'];
        }
        else
        {
            $tmpvar['priority'] = 3;
        }
    }
}
// Priority will be selected based on the category selected
else
{
    $res = hesk_dbQuery("SELECT `priority` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` WHERE `id`=".intval($tmpvar['category']));
    if ( hesk_dbNumRows($res) == 1 )
    {
        $tmpvar['priority'] = intval( hesk_dbResult($res) );
    }
    else
    {
        $tmpvar['priority'] = 3;
    }
}

$tmpvar['subject']  = hesk_input( hesk_POST('subject') ) or $hesk_error_buffer['subject']=$hesklang['enter_ticket_subject'];
$tmpvar['message']  = hesk_input( hesk_POST('message') ) or $hesk_error_buffer['message']=$hesklang['enter_message'];

// Is category a valid choice?
if ($tmpvar['category'])
{
	hesk_verifyCategory();

	// Is auto-assign of tickets disabled in this category?
	if ( empty($hesk_settings['category_data'][$tmpvar['category']]['autoassign']) )
	{
		$hesk_settings['autoassign'] = false;
	}
}

// Custom fields
foreach ($hesk_settings['custom_fields'] as $k=>$v)
{
	if ($v['use'])
    {
        if ($modsForHesk_settings['custom_field_setting'])
        {
            $v['name'] = $hesklang[$v['name']];
        }

        if ($v['type'] == 'checkbox' || $v['type'] == 'multiselect')
        {
			$tmpvar[$k]='';

        	if (isset($_POST[$k]))
            {
				if (is_array($_POST[$k]))
				{
					foreach ($_POST[$k] as $myCB)
					{
						$tmpvar[$k] .= ( is_array($myCB) ? '' : hesk_input($myCB) ) . '<br />';;
					}
					$tmpvar[$k]=substr($tmpvar[$k],0,-6);
				}
            }
            else
            {
            	if ($v['req'])
                {
					$hesk_error_buffer[$k]=$hesklang['fill_all'].': '.$v['name'];
                }
            	$_POST[$k] = '';
            }

            $_SESSION["c_$k"]=hesk_POST_array($k);
        }
		elseif ($v['req'])
        {
        	$tmpvar[$k]=hesk_makeURL(nl2br(hesk_input( hesk_POST($k) )));
            $_SESSION["c_$k"]=hesk_POST($k);
            if (!strlen($tmpvar[$k]))
            {
            	$hesk_error_buffer[$k]=$hesklang['fill_all'].': '.$v['name'];
            }

            if ($v['type'] == 'date')
            {
                $tmpvar[$k] = strtotime($_POST[$k]);
            }
        }
		else
        {
            if ($v['type'] == 'date' && $_POST[$k] != '')
            {
                $tmpvar[$k] = strtotime($_POST[$k]);
            } else
            {
                $tmpvar[$k] = hesk_makeURL(nl2br(hesk_input(hesk_POST($k))));
            }
        }
	}
    else
    {
    	$tmpvar[$k] = '';
    }
}

// Check bans
if ( ! isset($hesk_error_buffer['email']) && hesk_isBannedEmail($tmpvar['email']) || hesk_isBannedIP($_SERVER['REMOTE_ADDR']) )
{
    hesk_error($hesklang['baned_e']);
}

// Check maximum open tickets limit
$below_limit = true;
if ($hesk_settings['max_open'] && ! isset($hesk_error_buffer['email']) )
{
	$res = hesk_dbQuery("SELECT COUNT(*) FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `status` IN (SELECT `ID` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` WHERE `IsClosed` = 0) AND " . hesk_dbFormatEmail($tmpvar['email']));
	$num = hesk_dbResult($res);

	if ($num >= $hesk_settings['max_open'])
    {
    	$hesk_error_buffer = array( 'max_open' => sprintf($hesklang['maxopen'], $num, $hesk_settings['max_open']) );
        $below_limit = false;
    }
}

// If we reached max tickets let's save some resources
if ($below_limit)
{
	// Generate tracking ID
	$tmpvar['trackid'] = hesk_createID();

	// Attachments
	if ($hesk_settings['attachments']['use'])
	{
	    require_once(HESK_PATH . 'inc/attachments.inc.php');

	    $attachments = array();
        $trackingID  = $tmpvar['trackid'];

	    for ($i = 1; $i <= $hesk_settings['attachments']['max_number']; $i++)
	    {
	        $att = hesk_uploadFile($i);
	        if ($att !== false && ! empty($att) )
	        {
	            $attachments[$i] = $att;
	        }
	    }
	}
	$tmpvar['attachments'] = '';
}

// If we have any errors lets store info in session to avoid re-typing everything
if (count($hesk_error_buffer))
{
	$_SESSION['iserror'] = array_keys($hesk_error_buffer);

    $_SESSION['c_name']     = hesk_POST('name');
    $_SESSION['c_email']    = hesk_POST('email');
    $_SESSION['c_category'] = hesk_POST('category');
    $_SESSION['c_priority'] = hesk_POST('priority');
    $_SESSION['c_subject']  = hesk_POST('subject');
    $_SESSION['c_message']  = hesk_POST('message');

    $tmp = '';
    foreach ($hesk_error_buffer as $error)
    {
        $tmp .= "<li>$error</li>\n";
    }

	// Remove any successfully uploaded attachments
	if ($below_limit && $hesk_settings['attachments']['use'])
    {
    	hesk_removeAttachments($attachments);
    }

    $hesk_error_buffer = $hesklang['pcer'] . '<br /><br /><ul>' . $tmp . '</ul>';
    hesk_process_messages($hesk_error_buffer, 'index.php?a=add');
}

$tmpvar['message']=hesk_makeURL($tmpvar['message']);
$tmpvar['message']=nl2br($tmpvar['message']);

// Track suggested knowledgebase articles
if ($hesk_settings['kb_enable'] && $hesk_settings['kb_recommendanswers'] && isset($_POST['suggested']) && is_array($_POST['suggested']) )
{
    $tmpvar['articles'] = implode(',', array_unique( array_map('intval', $_POST['suggested']) ) );
}

// All good now, continue with ticket creation
$tmpvar['owner']   = 0;
$tmpvar['history'] = sprintf($hesklang['thist15'], hesk_date(), $tmpvar['name']);

// Auto assign tickets if aplicable
$autoassign_owner = hesk_autoAssignTicket($tmpvar['category']);
if ($autoassign_owner)
{
	$tmpvar['owner']    = $autoassign_owner['id'];
    $tmpvar['history'] .= sprintf($hesklang['thist10'], hesk_date(), $autoassign_owner['name'].' ('.$autoassign_owner['user'].')');
}

// Insert attachments
if ($hesk_settings['attachments']['use'] && ! empty($attachments) )
{
    foreach ($attachments as $myatt)
    {
        hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` (`ticket_id`,`saved_name`,`real_name`,`size`) VALUES ('".hesk_dbEscape($tmpvar['trackid'])."','".hesk_dbEscape($myatt['saved_name'])."','".hesk_dbEscape($myatt['real_name'])."','".intval($myatt['size'])."')");
        $tmpvar['attachments'] .= hesk_dbInsertID() . '#' . $myatt['real_name'] .',';
    }
}

// Should the helpdesk validate emails?
$createTicket = true;
if ($modsForHesk_settings['customer_email_verification_required'])
{
    $verifiedEmailSql = "SELECT `Email` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."verified_emails` WHERE `Email` = '".hesk_dbEscape($tmpvar['email'])."'";
    $verifiedEmailRS = hesk_dbQuery($verifiedEmailSql);
    if ($verifiedEmailRS->num_rows == 0)
    {
        //-- email has not yet been verified.
        $ticket = hesk_newTicket($tmpvar, false);

        //-- generate the activation key, which is a hash of their email address along with the current time.
        $unhashedKey = $tmpvar['email'].time();
        $key = hash('sha512', $unhashedKey);

        $escapedEmail = hesk_dbEscape($tmpvar['email']);
        $escapedKey = hesk_dbEscape($key);
        hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."pending_verification_emails` (`Email`, `ActivationKey`)
        VALUES ('".$escapedEmail."', '".$escapedKey."')");

        hesk_notifyCustomerForVerifyEmail('verify_email', $key);
        $createTicket = false;
    }
}
if ($createTicket)
{
    //-- email has been verified, and a ticket can be created
    $ticket = hesk_newTicket($tmpvar);

    // Notify the customer
    if ($hesk_settings['notify_new'])
    {
        hesk_notifyCustomer();
    }

    // Need to notify staff?
    // --> From autoassign?
        if ($tmpvar['owner'] && $autoassign_owner['notify_assigned'])
        {
            hesk_notifyAssignedStaff($autoassign_owner, 'ticket_assigned_to_you');
        }
    // --> No autoassign, find and notify appropriate staff
        elseif ( ! $tmpvar['owner'] )
        {
            hesk_notifyStaff('new_ticket_staff', " `notify_new_unassigned` = '1' ");
        }
}

// Next ticket show suggested articles again
$_SESSION['ARTICLES_SUGGESTED']=false;
$_SESSION['already_submitted']=1;

// Need email to view ticket? If yes, remember it by default
if ($hesk_settings['email_view_ticket'])
{
	setcookie('hesk_myemail', $tmpvar['email'], strtotime('+1 year'));
}

// Unset temporary variables
unset($tmpvar);
hesk_cleanSessionVars('tmpvar');
hesk_cleanSessionVars('c_category');
hesk_cleanSessionVars('c_priority');
hesk_cleanSessionVars('c_subject');
hesk_cleanSessionVars('c_message');
hesk_cleanSessionVars('c_question');
hesk_cleanSessionVars('img_verified');

// Print header
require_once(HESK_PATH . 'inc/header.inc.php');

?>

<ol class="breadcrumb">
  <li><a href="<?php echo $hesk_settings['site_url']; ?>"><?php echo $hesk_settings['site_title']; ?></a></li>
  <li><a href="<?php echo $hesk_settings['hesk_url']; ?>"><?php echo $hesk_settings['hesk_title']; ?></a></li>
  <li class="active"><?php echo $hesklang['tid_sent']; ?></li>
</ol>

<div style="width: 80%; margin-left: auto; margin-right: auto;">
    <?php
    if ($createTicket) {
        // Show success message with link to ticket
        hesk_show_success(

            $hesklang['ticket_submitted'] . '<br /><br />' .
            $hesklang['ticket_submitted_success'] . ': <b>' . $ticket['trackid'] . '</b><br /><br /> ' .
            ($hesk_settings['notify_new'] && $hesk_settings['spam_notice'] ? $hesklang['spam_inbox'] . '<br /><br />' : '') .
            '<a href="' . $hesk_settings['hesk_url'] . '/ticket.php?track=' . $ticket['trackid'] . '">' . $hesklang['view_your_ticket'] . '</a>'
        );
    } else
    {
        hesk_show_notice($hesklang['verify_your_email'].'<br><br>'.$hesklang['check_spambox']);
    }

    // Any other messages to display?
    hesk_handle_messages();
    ?>
</div>

<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


function hesk_forceStop()
{
	global $hesklang;
	?>
	<html>
	<head>
	<meta http-equiv="Refresh" content="0; url=index.php?a=add" />
	</head>
	<body>
	<p><a href="index.php?a=add"><?php echo $hesklang['c2c']; ?></a>.</p>
	</body>
	</html>
	<?php
    exit();
} // END hesk_forceStop()
?>
