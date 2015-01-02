<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.5.4 from 4th August 2014
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

/*** FUNCTIONS ***/


function hesk_getHHMMSS($in)
{
	$in = hesk_getTime($in);
    return explode(':', $in);
} // END hesk_getHHMMSS();


function hesk_getTime($in)
{
	$in = trim($in);

	/* If everything is OK this simple check should return true */
    if ( preg_match('/^([0-9]{2,3}):([0-5][0-9]):([0-5][0-9])$/', $in) )
    {
    	return $in;
    }

	/* No joy, let's try to figure out the correct values to use... */
    $h = 0;
    $m = 0;
    $s = 0;

    /* How many parts do we have? */
    $parts = substr_count($in, ':');

    switch ($parts)
    {
    	/* Only two parts, let's assume minutes and seconds */
		case 1:
	        list($m, $s) = explode(':', $in);
	        break;

        /* Three parts, so explode to hours, minutes and seconds */
        case 2:
	        list($h, $m, $s) = explode(':', $in);
	        break;

        /* Something other was entered, let's assume just minutes */
        default:
	        $m = $in;
    }

	/* Make sure all inputs are integers */
	$h = intval($h);
    $m = intval($m);
    $s = intval($s);

	/* Convert seconds to minutes if 60 or more seconds */
    if ($s > 59)
    {
    	$m = floor($s / 60) + $m;
        $s = intval($s % 60);
    }

	/* Convert minutes to hours if 60 or more minutes */
    if ($m > 59)
    {
    	$h = floor($m / 60) + $h;
        $m = intval($m % 60);
    }

    /* MySQL accepts max time value of 838:59:59 */
    if ($h > 838)
    {
    	return '838:59:59';
    }    

	/* That's it, let's send out formatted time string */
    return str_pad($h, 2, "0", STR_PAD_LEFT) . ':' . str_pad($m, 2, "0", STR_PAD_LEFT) . ':' . str_pad($s, 2, "0", STR_PAD_LEFT);

} // END hesk_getTime();


function hesk_mergeTickets($merge_these, $merge_into)
{
	global $hesk_settings, $hesklang, $hesk_db_link;

    /* Target ticket must not be in the "merge these" list */
    if ( in_array($merge_into, $merge_these) )
    {
        $merge_these = array_diff($merge_these, array( $merge_into ) );
    }

    /* At least 1 ticket needs to be merged with target ticket */
    if ( count($merge_these) < 1 )
    {
    	$_SESSION['error'] = $hesklang['merr1'];
    	return false;
    }

    /* Make sure target ticket exists */
	$res = hesk_dbQuery("SELECT `id`,`trackid`,`category` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `id`='".intval($merge_into)."' LIMIT 1");
	if (hesk_dbNumRows($res) != 1)
	{
    	$_SESSION['error'] = $hesklang['merr2'];
		return false;
	}
	$ticket = hesk_dbFetchAssoc($res);

	/* Make sure user has access to ticket category */
	if ( ! hesk_okCategory($ticket['category'], 0) )
	{
    	$_SESSION['error'] = $hesklang['merr3'];
		return false;
	}

    /* Set some variables for later */
    $merge['attachments'] = '';
	$merge['replies'] = array();
    $merge['notes'] = array();
    $sec_worked = 0;
    $history = '';
    $merged = '';

	/* Get messages, replies, notes and attachments of tickets that will be merged */
    foreach ($merge_these as $this_id)
    {
		/* Validate ID */
    	if ( is_array($this_id) )
        {
        	continue;
        }
    	$this_id = intval($this_id) or hesk_error($hesklang['id_not_valid']);

        /* Get required ticket information */
        $res = hesk_dbQuery("SELECT `id`,`trackid`,`category`,`name`,`message`,`dt`,`time_worked`,`attachments` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `id`='".intval($this_id)."' LIMIT 1");
		if (hesk_dbNumRows($res) != 1)
		{
			continue;
		}
        $row = hesk_dbFetchAssoc($res);

        /* Has this user access to the ticket category? */
        if ( ! hesk_okCategory($row['category'], 0) )
        {
        	continue;
        }

        /* Insert ticket message as a new reply to target ticket */
		hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."replies` (`replyto`,`name`,`message`,`dt`,`attachments`) VALUES ('".intval($ticket['id'])."','".hesk_dbEscape($row['name'])."','".hesk_dbEscape($row['message'])."','".hesk_dbEscape($row['dt'])."','".hesk_dbEscape($row['attachments'])."')");

		/* Update attachments  */
		hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` SET `ticket_id`='".hesk_dbEscape($ticket['trackid'])."' WHERE `ticket_id`='".hesk_dbEscape($row['trackid'])."'");

        /* Get old ticket replies and insert them as new replies */
        $res = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."replies` WHERE `replyto`='".intval($row['id'])."'");
        while ( $reply = hesk_dbFetchAssoc($res) )
        {
			hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."replies` (`replyto`,`name`,`message`,`dt`,`attachments`,`staffid`,`rating`,`read`) VALUES ('".intval($ticket['id'])."','".hesk_dbEscape($reply['name'])."','".hesk_dbEscape($reply['message'])."','".hesk_dbEscape($reply['dt'])."','".hesk_dbEscape($reply['attachments'])."','".intval($reply['staffid'])."','".intval($reply['rating'])."','".intval($reply['read'])."')");
        }

		/* Delete replies to the old ticket */
		hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."replies` WHERE `replyto`='".intval($row['id'])."'");

        /* Get old ticket notes and insert them as new notes */
        $res = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."notes` WHERE `ticket`='".intval($row['id'])."'");
        while ( $note = hesk_dbFetchAssoc($res) )
        {
			hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."notes` (`ticket`,`who`,`dt`,`message`) VALUES ('".intval($ticket['id'])."','".intval($note['who'])."','".hesk_dbEscape($note['dt'])."','".hesk_dbEscape($note['message'])."')");
        }

		/* Delete replies to the old ticket */
		hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."notes` WHERE `ticket`='".intval($row['id'])."'");

	    /* Delete old ticket */
		hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `id`='".intval($row['id'])."'");

		/* Log that ticket has been merged */
		$history .= sprintf($hesklang['thist13'],hesk_date(),$row['trackid'],$_SESSION['name'].' ('.$_SESSION['user'].')');

        /* Add old ticket ID to target ticket "merged" field */
        $merged .= '#' . $row['trackid'];

		/* Convert old ticket "time worked" to seconds and add to $sec_worked variable */
		list ($hr, $min, $sec) = explode(':', $row['time_worked']);
		$sec_worked += (((int)$hr) * 3600) + (((int)$min) * 60) + ((int)$sec);
    }

	/* Convert seconds to HHH:MM:SS */
	$sec_worked = hesk_getTime('0:'.$sec_worked);

    /* Update history (log) and merged IDs of target ticket */
	hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `time_worked`=ADDTIME(`time_worked`, '".hesk_dbEscape($sec_worked)."'), `merged`=CONCAT(`merged`,'".hesk_dbEscape($merged . '#')."'), `history`=CONCAT(`history`,'".hesk_dbEscape($history)."') WHERE `id`='".intval($merge_into)."' LIMIT 1");

    return true;

} // END hesk_mergeTickets()


function hesk_updateStaffDefaults()
{
	global $hesk_settings, $hesklang;

	// Demo mode
	if ( defined('HESK_DEMO') )
	{
		return true;
	}
	// Remove the part that forces saving as default - we don't need it every time
    $default_list = str_replace('&def=1','',$_SERVER['QUERY_STRING']);

    // Update database
	$res = hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` SET `default_list`='".hesk_dbEscape($default_list)."' WHERE `id`='".intval($_SESSION['id'])."'");

    // Update session values so the changes take effect immediately
    $_SESSION['default_list'] = $default_list;

    return true;
    
} // END hesk_updateStaffDefaults()


function hesk_makeJsString($in)
{
	return addslashes(preg_replace("/\s+/",' ',$in));
} // END hesk_makeJsString()


function hesk_checkNewMail()
{
	global $hesk_settings, $hesklang;

	$res = hesk_dbQuery("SELECT COUNT(*) FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."mail` WHERE `to`='".intval($_SESSION['id'])."' AND `read`='0' AND `deletedby`!='".intval($_SESSION['id'])."' ");
	$num = hesk_dbResult($res,0,0);

	return $num;
} // END hesk_checkNewMail()


function hesk_dateToString($dt, $returnName=1, $returnTime=0, $returnMonth=0, $from_database=false)
{
	global $hesk_settings, $hesklang;

	$dt = strtotime($dt);

	// Adjust MySQL time if different from PHP time
	if ($from_database)
	{
		if ( ! defined('MYSQL_TIME_DIFF') )
		{
			define('MYSQL_TIME_DIFF', time()-hesk_dbTime() );
		}

		if (MYSQL_TIME_DIFF != 0)
		{
			$dt += MYSQL_TIME_DIFF;
		}

		// Add HESK set time difference
		$dt += 3600*$hesk_settings['diff_hours'] + 60*$hesk_settings['diff_minutes'];

		// Daylight saving?
		if ($hesk_settings['daylight'] && date('I', $dt))
		{
			$dt += 3600;
		}
	}

	list($y,$m,$n,$d,$G,$i,$s) = explode('-', date('Y-n-j-w-G-i-s', $dt) );

	$m = $hesklang['m'.$m];
	$d = $hesklang['d'.$d];

	if ($returnName)
	{
		return "$d, $m $n, $y";
	}

    if ($returnTime)
    {
    	return "$d, $m $n, $y $G:$i:$s";
    }

    if ($returnMonth)
    {
    	return "$m $y";
    }

	return "$m $n, $y";
} // End hesk_dateToString()


function hesk_getCategoriesArray($kb = 0) {
	global $hesk_settings, $hesklang, $hesk_db_link;

	$categories = array();
    if ($kb)
    {
    	$result = hesk_dbQuery('SELECT `id`, `name` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'kb_categories` ORDER BY `cat_order` ASC');
    }
    else
    {
		$result = hesk_dbQuery('SELECT `id`, `name` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'categories` ORDER BY `cat_order` ASC');
    }

	while ($row=hesk_dbFetchAssoc($result))
	{
		$categories[$row['id']] = $row['name'];
	}

    return $categories;
} // END hesk_getCategoriesArray()


function hesk_getHTML($in)
{
	global $hesk_settings, $hesklang;

	$replace_from = array("\t","<?","?>","$","<%","%>");
	$replace_to   = array("","&lt;?","?&gt;","\$","&lt;%","%&gt;");

	$in = trim($in);
	$in = str_replace($replace_from,$replace_to,$in);
	$in = preg_replace('/\<script(.*)\>(.*)\<\/script\>/Uis',"<script$1></script>",$in);
	$in = preg_replace('/\<\!\-\-(.*)\-\-\>/Uis',"<!-- comments have been removed -->",$in);

	if (HESK_SLASH === true)
	{
		$in = addslashes($in);
	}
    $in = str_replace('\"','"',$in);

	return $in;
} // END hesk_getHTML()


function hesk_autoLogin($noredirect=0)
{
	global $hesk_settings, $hesklang, $hesk_db_link;

	if (!$hesk_settings['autologin'])
    {
    	return false;
    }

    $user = hesk_htmlspecialchars( hesk_COOKIE('hesk_username') );
    $hash = hesk_htmlspecialchars( hesk_COOKIE('hesk_p') );
    define('HESK_USER', $user);

	if (empty($user) || empty($hash))
    {
    	return false;
    }

	/* Login cookies exist, now lets limit brute force attempts */
	hesk_limitBfAttempts();

	/* Check username */
	$result = hesk_dbQuery('SELECT * FROM `'.$hesk_settings['db_pfix']."users` WHERE `user` = '".hesk_dbEscape($user)."' LIMIT 1");
	if (hesk_dbNumRows($result) != 1)
	{
        setcookie('hesk_username', '');
        setcookie('hesk_p', '');
        header('Location: index.php?a=login&notice=1');
        exit();
	}

	$res=hesk_dbFetchAssoc($result);
	foreach ($res as $k=>$v)
	{
	    $_SESSION[$k]=$v;
	}

	/* Check password */
	if ($hash != hesk_Pass2Hash($_SESSION['pass'] . strtolower($user) . $_SESSION['pass']) )
    {
        setcookie('hesk_username', '');
        setcookie('hesk_p', '');
        header('Location: index.php?a=login&notice=1');
        exit();
	}

    /* Check if default password */
    if ($_SESSION['pass'] == '499d74967b28a841c98bb4baaabaad699ff3c079')
    {
    	hesk_process_messages($hesklang['chdp'],'NOREDIRECT','NOTICE');
    }

	unset($_SESSION['pass']);

	/* Login successful, clean brute force attempts */
	hesk_cleanBfAttempts();

	/* Regenerate session ID (security) */
	hesk_session_regenerate_id();

	/* Get allowed categories */
	if (empty($_SESSION['isadmin']))
	{
	    $_SESSION['categories']=explode(',',$_SESSION['categories']);
	}

	/* Renew cookies */
	setcookie('hesk_username', "$user", strtotime('+1 year'));
	setcookie('hesk_p', "$hash", strtotime('+1 year'));

    /* Close any old tickets here so Cron jobs aren't necessary */
	if ($hesk_settings['autoclose'])
    {
        $dt  = date('Y-m-d H:i:s',time() - $hesk_settings['autoclose']*86400);

        $waitingForCustomerRS = hesk_dbQuery("SELECT `ID` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` WHERE `IsDefaultStaffReplyStatus` = 1");
        $waitingForCustomerStatus = hesk_dbFetchAssoc($waitingForCustomerRS);

        $result = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `status` = ".$waitingForCustomerStatus['ID']." AND `lastchange` <= '".hesk_dbEscape($dt)."' ");
        if (hesk_dbNumRows($result) > 0)
        {
            require(HESK_PATH . 'inc/email_functions.inc.php');
            global $ticket;
            while ($ticket = hesk_dbFetchAssoc($result)) {
                hesk_notifyCustomer('ticket_closed');
            }

            $revision = sprintf($hesklang['thist3'],hesk_date(),$hesklang['auto']);

            $closedStatusRS = hesk_dbQuery("SELECT `ID` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` WHERE `IsStaffClosedOption` = 1");
            $closedStatus = hesk_dbFetchAssoc($closedStatusRS);

            $sql = "UPDATE `".$hesk_settings['db_pfix']."tickets` SET `status`=".$closedStatus['ID'].", `history`=CONCAT(`history`,'".hesk_dbEscape($revision)."') WHERE `status` = ".$waitingForCustomerStatus['ID']." AND `lastchange` <= '".hesk_dbEscape($dt)."' ";
            hesk_dbQuery($sql);
		}
    }

	/* If session expired while a HESK page is open just continue using it, don't redirect */
    if ($noredirect)
    {
    	return true;
    }

	/* Redirect to the destination page */
	if ( hesk_isREQUEST('goto') && $url=hesk_REQUEST('goto') )
	{
	    $url = str_replace('&amp;','&',$url);
	    header('Location: '.$url);
	}
	else
	{
	    header('Location: admin_main.php');
	}
	exit();
} // END hesk_autoLogin()


function hesk_isLoggedIn()
{
	global $hesk_settings;

	$referer = hesk_input($_SERVER['REQUEST_URI']);
	$referer = str_replace('&amp;','&',$referer);

    if (empty($_SESSION['id']))
    {
    	if ($hesk_settings['autologin'] && hesk_autoLogin(1) )
        {
			// Users online
        	if ($hesk_settings['online'])
            {
            	require(HESK_PATH . 'inc/users_online.inc.php');
                hesk_initOnline($_SESSION['id']);
            }

        	return true;
        }

		// Some pages cannot be redirected to
		$modify_redirect = array(
			'admin_reply_ticket.php'	=> 'admin_main.php',
			'admin_settings_save.php'	=> 'admin_settings.php',
			'delete_tickets.php'		=> 'admin_main.php',
			'move_category.php'			=> 'admin_main.php',
			'priority.php'				=> 'admin_main.php',
		);

		foreach ($modify_redirect as $from => $to)
		{
			if ( strpos($referer,$from) !== false )
			{
				$referer = $to;
			}
		}

        $url = 'index.php?a=login&notice=1&goto='.urlencode($referer);
        header('Location: '.$url);
        exit();
    }
    else
    {
        hesk_session_regenerate_id();

        // Need to update permissions?
		if ( empty($_SESSION['isadmin']) )
		{
			$res = hesk_dbQuery("SELECT `isadmin`, `categories`, `heskprivileges` FROM `".$hesk_settings['db_pfix']."users` WHERE `id` = '".intval($_SESSION['id'])."' LIMIT 1");
			if (hesk_dbNumRows($res) == 1)
			{
				$me = hesk_dbFetchAssoc($res);
				foreach ($me as $k => $v)
				{
					$_SESSION[$k]=$v;
				}

				// Get allowed categories
				if  (empty($_SESSION['isadmin']) )
				{
					$_SESSION['categories']=explode(',',$_SESSION['categories']);
				}
			}
            else
            {
				hesk_session_stop();
				$url = 'index.php?a=login&notice=1&goto='.urlencode($referer);
				header('Location: '.$url);
				exit();
            }
		}

		// Users online
		if ($hesk_settings['online'])
		{
			require(HESK_PATH . 'inc/users_online.inc.php');
            hesk_initOnline($_SESSION['id']);
		}

        return true;
    }

} // END hesk_isLoggedIn()


function hesk_Pass2Hash($plaintext) {
    $majorsalt  = '';
    $len = strlen($plaintext);
    for ($i=0;$i<$len;$i++)
    {
        $majorsalt .= sha1(substr($plaintext,$i,1));
    }
    $corehash = sha1($majorsalt);
    return $corehash;
} // END hesk_Pass2Hash()


function hesk_formatDate($dt)
{
    $dt=hesk_date($dt);
	$dt=str_replace(' ','<br />',$dt);
    return $dt;
} // End hesk_formatDate()


function hesk_jsString($str)
{
	$str  = str_replace( array('\'','<br />') , array('\\\'','') ,$str);
    $from = array("/\r\n|\n|\r/", '/\<a href="mailto\:([^"]*)"\>([^\<]*)\<\/a\>/i', '/\<a href="([^"]*)" target="_blank"\>([^\<]*)\<\/a\>/i');
    $to   = array("\\r\\n' + \r\n'", "$1", "$1");
    return preg_replace($from,$to,$str);
} // END hesk_jsString()


function hesk_myCategories($what='category')
{
    if ( ! empty($_SESSION['isadmin']) )
    {
        return '1';
    }
    else
    {
        return " `".hesk_dbEscape($what)."` IN ('" . implode("','", array_map('intval', $_SESSION['categories']) ) . "')";
    }
} // END hesk_myCategories()


function hesk_okCategory($cat,$error=1,$user_isadmin=false,$user_cat=false)
{
	global $hesklang;

	/* Checking for current user or someone else? */
    if ($user_isadmin === false)
    {
		$user_isadmin = $_SESSION['isadmin'];
    }

    if ($user_cat === false)
    {
		$user_cat = $_SESSION['categories'];
    }

    /* Is admin? */
    if ($user_isadmin)
    {
        return true;
    }
    /* Staff with access? */
    elseif (in_array($cat,$user_cat))
    {
        return true;
    }
    /* No access */
    else
    {
        if ($error)
        {
        	hesk_error($hesklang['not_authorized_tickets']);
        }
        else
        {
        	return false;
        }
    }

} // END hesk_okCategory()


function hesk_checkPermission($feature,$showerror=1) {
	global $hesklang;


    /* Check if this is for managing settings */
    if ($feature == 'can_manage_settings')
    {
        if ($_SESSION['can_manage_settings']) {
            return true;
        } else {
            if ($showerror) {
                hesk_error($hesklang['no_permission'].'<p>&nbsp;</p><p align="center"><a href="index.php">'.$hesklang['click_login'].'</a>');
            } else {
                return false;
            }
        }
    }

    /* Admins have full access to all features, besides possibly settings */
    if ($_SESSION['isadmin'])
    {
        return true;
    }

    /* Check other staff for permissions */
    if (strpos($_SESSION['heskprivileges'], $feature) === false)
    {
    	if ($showerror)
        {
        	hesk_error($hesklang['no_permission'].'<p>&nbsp;</p><p align="center"><a href="index.php">'.$hesklang['click_login'].'</a>');
        }
        else
        {
        	return false;
        }
    }
    else
    {
        return true;
    }

} // END hesk_checkPermission()
