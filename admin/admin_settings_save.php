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
require(HESK_PATH . 'modsForHesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/email_functions.inc.php');
require(HESK_PATH . 'inc/setup_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

// Check permissions for this feature
hesk_checkPermission('can_manage_settings');

// A security check
hesk_token_check('POST');

// Demo mode
if ( defined('HESK_DEMO') )
{
	hesk_process_messages($hesklang['sdemo'], 'admin_settings.php');
}

//-- Before we do anything, make sure the statuses are valid.
$rows = hesk_dbQuery('SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'statuses`');
while ($row = $rows->fetch_assoc())
{
    if (!isset($_POST['s'.$row['ID'].'_delete']))
    {
        validateStatus($_POST['s'.$row['ID'].'_shortName'], $_POST['s'.$row['ID'].'_longName'], $_POST['s'.$row['ID'].'_textColor']);
    }
}

//-- Validate the new one if at least one of the fields are used / checked
if ($_POST['sN_shortName'] != null || $_POST['sN_longName'] != null || $_POST['sN_textColor'] != null || isset($_POST['sN_isClosed']))
{
    validateStatus($_POST['sN_shortName'], $_POST['sN_longName'], $_POST['sN_textColor']);
}

$set=array();

/*** GENERAL ***/

/* --> General settings */
$set['site_title']		= hesk_input( hesk_POST('s_site_title'), $hesklang['err_sname']);
$set['site_title']		= str_replace('\\&quot;','&quot;',$set['site_title']);
$set['site_url']		= hesk_input( hesk_POST('s_site_url'), $hesklang['err_surl']);
$set['webmaster_mail']	= hesk_validateEmail( hesk_POST('s_webmaster_mail'), $hesklang['err_wmmail']);
$set['noreply_mail']	= hesk_validateEmail( hesk_POST('s_noreply_mail'), $hesklang['err_nomail']);
$set['noreply_name']	= hesk_input( hesk_POST('s_noreply_name') );
$set['noreply_name']	= str_replace(array('\\&quot;','&lt;','&gt;'),'',$set['noreply_name']);
$set['noreply_name']	= trim( preg_replace('/\s{2,}/', ' ', $set['noreply_name']) );

/* --> Language settings */
$set['can_sel_lang']	= empty($_POST['s_can_sel_lang']) ? 0 : 1;
$set['languages'] 		= hesk_getLanguagesArray();
$lang					= explode('|', hesk_input( hesk_POST('s_language') ) );
if (isset($lang[1]) && in_array($lang[1],hesk_getLanguagesArray(1) ))
{
	$set['language'] = $lang[1];
}
else
{
	hesk_error($hesklang['err_lang']);
}

/* --> Database settings */
hesk_dbClose();

if ( hesk_testMySQL() )
{
	// Database connection OK
}
elseif ($mysql_log)
{
	hesk_error($mysql_error . '<br /><br /><b>' . $hesklang['mysql_said'] . ':</b> ' . $mysql_log);
}
else
{
	hesk_error($mysql_error);
}

/*** HELP DESK ***/

/* --> Helpdesk settings */
$set['hesk_title']		= hesk_input( hesk_POST('s_hesk_title'), $hesklang['err_htitle']);
$set['hesk_title']		= str_replace('\\&quot;','&quot;',$set['hesk_title']);
$set['hesk_url']		= hesk_input( hesk_POST('s_hesk_url'), $hesklang['err_hurl']);

// ---> check admin folder
$set['admin_dir'] = isset($_POST['s_admin_dir']) && ! is_array($_POST['s_admin_dir']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['s_admin_dir']) : 'admin';
/*
if ( ! is_dir(HESK_PATH . $set['admin_dir']) )
{
	hesk_error( sprintf($hesklang['err_adf'], $set['admin_dir']) );
}
*/

// ---> check attachments folder
$set['attach_dir'] = isset($_POST['s_attach_dir']) && ! is_array($_POST['s_attach_dir']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['s_attach_dir']) : 'attachments';
/*
if ( ! is_dir(HESK_PATH . $set['attach_dir']) )
{
	hesk_error( sprintf($hesklang['err_atf'], $set['attach_dir']) );
}
if ( ! is_writable(HESK_PATH . $set['attach_dir']) )
{
	hesk_error( sprintf($hesklang['err_atr'], $set['attach_dir']) );
}
*/

$set['max_listings']	= hesk_checkMinMax( intval( hesk_POST('s_max_listings') ) , 1, 999, 10);
$set['print_font_size']	= hesk_checkMinMax( intval( hesk_POST('s_print_font_size') ) , 1, 99, 12);
$set['autoclose']		= hesk_checkMinMax( intval( hesk_POST('s_autoclose') ) , 0, 999, 7);
$set['max_open']		= hesk_checkMinMax( intval( hesk_POST('s_max_open') ) , 0, 999, 0);
$set['new_top']			= empty($_POST['s_new_top']) ? 0 : 1;
$set['reply_top']		= empty($_POST['s_reply_top']) ? 0 : 1;

/* --> Features */
$set['autologin']		= empty($_POST['s_autologin']) ? 0 : 1;
$set['autoassign']		= empty($_POST['s_autoassign']) ? 0 : 1;
$set['custopen']		= empty($_POST['s_custopen']) ? 0 : 1;
$set['rating']			= empty($_POST['s_rating']) ? 0 : 1;
$set['cust_urgency']	= empty($_POST['s_cust_urgency']) ? 0 : 1;
$set['sequential']		= empty($_POST['s_sequential']) ? 0 : 1;
$set['list_users']		= empty($_POST['s_list_users']) ? 0 : 1;
$set['debug_mode']		= empty($_POST['s_debug_mode']) ? 0 : 1;
$set['short_link']		= empty($_POST['s_short_link']) ? 0 : 1;

/* --> SPAM prevention */
$set['secimg_use']		= empty($_POST['s_secimg_use']) ? 0 : ( hesk_POST('s_secimg_use') == 2 ? 2 : 1);
$set['secimg_sum']		= '';
for ($i=1;$i<=10;$i++)
{
    $set['secimg_sum'] .= substr('AEUYBDGHJLMNPQRSTVWXZ123456789', rand(0,29), 1);
}
$set['recaptcha_use']	= empty($_POST['s_recaptcha_use']) ? 0 : 1;
$set['recaptcha_ssl']	= empty($_POST['s_recaptcha_ssl']) ? 0 : 1;
$set['recaptcha_public_key']	= hesk_input( hesk_POST('s_recaptcha_public_key') );
$set['recaptcha_private_key']	= hesk_input( hesk_POST('s_recaptcha_private_key') );
$set['question_use']	= empty($_POST['s_question_use']) ? 0 : 1;
$set['question_ask']	= hesk_getHTML( hesk_POST('s_question_ask') ) or hesk_error($hesklang['err_qask']);
$set['question_ans']	= hesk_input( hesk_POST('s_question_ans'), $hesklang['err_qans']);

/* --> Security */
$set['attempt_limit']	= hesk_checkMinMax( intval( hesk_POST('s_attempt_limit') ) , 0, 999, 5);
if ($set['attempt_limit'] > 0)
{
	$set['attempt_limit']++;
}
$set['attempt_banmin']	= hesk_checkMinMax( intval( hesk_POST('s_attempt_banmin') ) , 5, 99999, 60);
$set['email_view_ticket'] = empty($_POST['s_email_view_ticket']) ? 0 : 1;

/* --> Attachments */
$set['attachments']['use'] = empty($_POST['s_attach_use']) ? 0 : 1;
if ($set['attachments']['use'])
{
    $set['attachments']['max_number'] = intval( hesk_POST('s_max_number', 2) );

    $size = floatval( hesk_POST('s_max_size', '1.0') );
    $unit = hesk_htmlspecialchars( hesk_POST('s_max_unit', 'MB') );

    $set['attachments']['max_size'] = hesk_formatUnits($size . ' ' . $unit);

	$set['attachments']['allowed_types'] = isset($_POST['s_allowed_types']) && ! is_array($_POST['s_allowed_types']) && strlen($_POST['s_allowed_types']) ? explode(',', strtolower( preg_replace('/[^a-zA-Z0-9,]/', '', $_POST['s_allowed_types']) ) ) : array();
	$set['attachments']['allowed_types'] = array_diff($set['attachments']['allowed_types'], array('php', 'php4', 'php3', 'php5', 'phps', 'phtml', 'shtml', 'shtm', 'cgi', 'pl') );

	if (count($set['attachments']['allowed_types']))
	{
		$keep_these = array();

		foreach ($set['attachments']['allowed_types'] as $ext)
		{
			if (strlen($ext) > 1)
			{
				$keep_these[] = '.' . $ext;
			}
		}

		$set['attachments']['allowed_types'] = $keep_these;
	}
	else
	{
		$set['attachments']['allowed_types'] = array('.gif','.jpg','.png','.zip','.rar','.csv','.doc','.docx','.xls','.xlsx','.txt','.pdf');
	}
}
else
{
    $set['attachments']['max_number']=2;
    $set['attachments']['max_size']=1048576;
    $set['attachments']['allowed_types']=array('.gif','.jpg','.png','.zip','.rar','.csv','.doc','.docx','.xls','.xlsx','.txt','.pdf');
}

/*** KNOWLEDGEBASE ***/

/* --> Knowledgebase settings */
$set['kb_enable']			= empty($_POST['s_kb_enable']) ? 0 : 1;
$set['kb_wysiwyg']			= empty($_POST['s_kb_wysiwyg']) ? 0 : 1;
$set['kb_search']			= empty($_POST['s_kb_search']) ? 0 : ( hesk_POST('s_kb_search') == 2 ? 2 : 1);
$set['kb_recommendanswers']	= empty($_POST['s_kb_recommendanswers']) ? 0 : 1;
$set['kb_views']			= empty($_POST['s_kb_views']) ? 0 : 1;
$set['kb_date']				= empty($_POST['s_kb_date']) ? 0 : 1;
$set['kb_rating']			= empty($_POST['s_kb_rating']) ? 0 : 1;
$set['kb_search_limit']		= hesk_checkMinMax( intval( hesk_POST('s_kb_search_limit') ) , 1, 99, 10);
$set['kb_substrart']		= hesk_checkMinMax( intval( hesk_POST('s_kb_substrart') ) , 20, 9999, 200);
$set['kb_cols']				= hesk_checkMinMax( intval( hesk_POST('s_kb_cols') ) , 1, 5, 2);
$set['kb_numshow']			= intval( hesk_POST('s_kb_numshow') ); // Popular articles on subcat listing
$set['kb_popart']			= intval( hesk_POST('s_kb_popart') ); // Popular articles on main category page
$set['kb_latest']			= intval( hesk_POST('s_kb_latest') ); // Popular articles on main category page
$set['kb_index_popart']		= intval( hesk_POST('s_kb_index_popart') );
$set['kb_index_latest']		= intval( hesk_POST('s_kb_index_latest') );


/*** EMAIL ***/

/* --> Email sending */
$smtp_OK = true;
$set['smtp'] = empty($_POST['s_smtp']) ? 0 : 1;
if ($set['smtp'])
{
	// Test SMTP connection
    $smtp_OK = hesk_testSMTP();

	// If SMTP not working, disable it
	if ( ! $smtp_OK)
    {
    	$set['smtp'] = 0;
    }
}
else
{
	$set['smtp_host_name']	= hesk_input( hesk_POST('tmp_smtp_host_name', 'localhost') );
	$set['smtp_host_port']	= intval( hesk_POST('tmp_smtp_host_port', 25) );
	$set['smtp_timeout']	= intval( hesk_POST('tmp_smtp_timeout', 10) );
	$set['smtp_ssl']		= empty($_POST['tmp_smtp_ssl']) ? 0 : 1;
	$set['smtp_tls']		= empty($_POST['tmp_smtp_tls']) ? 0 : 1;
	$set['smtp_user']		= hesk_input( hesk_POST('tmp_smtp_user') );
	$set['smtp_password']	= hesk_input( hesk_POST('tmp_smtp_password') );
}

/* --> Email piping */
$set['email_piping']	= empty($_POST['s_email_piping']) ? 0 : 1;

/* --> POP3 fetching */
$pop3_OK = true;
$set['pop3'] = empty($_POST['s_pop3']) ? 0 : 1;
if ($set['pop3'])
{
	// Test POP3 connection
    $pop3_OK = hesk_testPOP3();

	// If POP3 not working, disable it
	if ( ! $pop3_OK)
    {
    	$set['pop3'] = 0;
    }
}
else
{
	$set['pop3_host_name']	= hesk_input( hesk_POST('tmp_pop3_host_name', 'mail.domain.com') );
	$set['pop3_host_port']	= intval( hesk_POST('tmp_pop3_host_port', 110) );
	$set['pop3_tls']		= empty($_POST['tmp_pop3_tls']) ? 0 : 1;
    $set['pop3_keep']		= empty($_POST['tmp_pop3_keep']) ? 0 : 1;
	$set['pop3_user']		= hesk_input( hesk_POST('tmp_pop3_user') );
	$set['pop3_password']	= hesk_input( hesk_POST('tmp_pop3_password') );
}

/* --> Email loops */
$set['loop_hits']	= hesk_checkMinMax( intval( hesk_POST('s_loop_hits') ) , 0, 999, 5);
$set['loop_time']	= hesk_checkMinMax( intval( hesk_POST('s_loop_time') ) , 1, 86400, 300);

/* --> Detect email typos */
$set['detect_typos']	= empty($_POST['s_detect_typos']) ? 0 : 1;
$set['email_providers'] = array();

if ( ! empty($_POST['s_email_providers']) && ! is_array($_POST['s_email_providers']) )
{
	$lines = preg_split('/$\R?^/m', hesk_input($_POST['s_email_providers']) );
	foreach ($lines as $domain)
	{
		$domain = trim($domain);
        $domain = str_replace('@', '', $domain);
		$domainLen = strlen($domain);

		/* Check domain part length */
		if ($domainLen < 1 || $domainLen > 254)
		{
			continue;
		}

		/* Check domain part characters */
		if ( ! preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain) )
		{
			continue;
		}

		/* Domain part mustn't have two consecutive dots */
		if ( strpos($domain, '..') !== false  )
		{
			continue;
		}

		$set['email_providers'][] = $domain;
	}
}

if ( ! $set['detect_typos'] || count($set['email_providers']) < 1 )
{
	$set['detect_typos'] = 0;
	$set['email_providers'] = array('gmail.com','hotmail.com','hotmail.co.uk','yahoo.com','yahoo.co.uk','aol.com','aol.co.uk','msn.com','live.com','live.co.uk','mail.com','googlemail.com','btinternet.com','btopenworld.com');
}

$set['email_providers'] = count($set['email_providers']) ?  "'" . implode("','", $set['email_providers']) . "'" : '';

/* --> Other */
$set['strip_quoted']	= empty($_POST['s_strip_quoted']) ? 0 : 1;
$set['save_embedded']	= empty($_POST['s_save_embedded']) ? 0 : 1;
$set['multi_eml']		= empty($_POST['s_multi_eml']) ? 0 : 1;
$set['confirm_email']	= empty($_POST['s_confirm_email']) ? 0 : 1;
$set['open_only']		= empty($_POST['s_open_only']) ? 0 : 1;


/*** MISC ***/

/* --> Date & Time */
$set['diff_hours']		= floatval( hesk_POST('s_diff_hours', 0) );
$set['diff_minutes']	= floatval( hesk_POST('s_diff_minutes', 0) );
$set['daylight']		= empty($_POST['s_daylight']) ? 0 : 1;
$set['timeformat']		= hesk_input( hesk_POST('s_timeformat') ) or $set['timeformat'] = 'Y-m-d H:i:s';

/* --> Other */
$set['alink']			= empty($_POST['s_alink']) ? 0 : 1;
$set['submit_notice']	= empty($_POST['s_submit_notice']) ? 0 : 1;
$set['online']			= empty($_POST['s_online']) ? 0 : 1;
$set['online_min']		= hesk_checkMinMax( intval( hesk_POST('s_online_min') ) , 1, 999, 10);
$set['check_updates']	= empty($_POST['s_check_updates']) ? 0 : 1;

/*** CUSTOM FIELDS ***/

for ($i=1;$i<=20;$i++)
{
	$this_field='custom' . $i;
	$set['custom_fields'][$this_field]['use'] = ! empty($_POST['s_custom'.$i.'_use']) ? 1 : 0;

	if ($set['custom_fields'][$this_field]['use'])
	{
		$set['custom_fields'][$this_field]['place']		= empty($_POST['s_custom'.$i.'_place']) ? 0 : 1;
		$set['custom_fields'][$this_field]['type']		= hesk_htmlspecialchars( hesk_POST('s_custom'.$i.'_type', 'text') );
		$set['custom_fields'][$this_field]['req']		= ! empty($_POST['s_custom'.$i.'_req']) ? 1 : 0;
		$set['custom_fields'][$this_field]['name']		= hesk_input( hesk_POST('s_custom'.$i.'_name'), $hesklang['err_custname']);
		$set['custom_fields'][$this_field]['maxlen']	= intval( hesk_POST('s_custom'.$i.'_maxlen', 255) );
        $set['custom_fields'][$this_field]['value']		= hesk_input( hesk_POST('s_custom'.$i.'_val') );

        if (!in_array($set['custom_fields'][$this_field]['type'],array('text','textarea','select','radio','checkbox')))
        {
        	$set['custom_fields'][$this_field]['type'] = 'text';
        }
	}
	else
	{
		$set['custom_fields'][$this_field] = array('use'=>0,'place'=>0,'type'=>'text','req'=>0,'name'=>'Custom field '.$i ,'maxlen'=>255,'value'=>'');
	}
}

//-- Update the statuses
hesk_dbConnect();
$wasStatusDeleted = false;
//-- Get all the status IDs
$statusesSql = 'SELECT * FROM `'.$hesk_settings['db_pfix'].'statuses`';
$results = hesk_dbQuery($statusesSql);
while ($row = $results->fetch_assoc())
{
    //-- If the status is marked for deletion, delete it and skip everything below.
    if (isset($_POST['s'.$row['ID'].'_delete']))
    {
        $delete = "DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` WHERE `ID` = ?";
        $stmt = hesk_dbConnect()->prepare($delete);
        $stmt->bind_param('i', $row['ID']);
        $stmt->execute();
        $wasStatusDeleted = true;
    } else
    {
        //-- Update the information in the database with what is on the page
        $query = "UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` SET `ShortNameContentKey` = ?, `TicketViewContentKey` = ?, `TextColor` = ?, `IsClosed` = ? WHERE `ID` = ?";
        $stmt = hesk_dbConnect()->prepare($query);
        $isStatusClosed = (isset($_POST['s'.$row['ID'].'_isClosed']) ? 1 : 0);
        $stmt->bind_param('sssii', $_POST['s'.$row['ID'].'_shortName'], $_POST['s'.$row['ID'].'_longName'], $_POST['s'.$row['ID'].'_textColor'], $isStatusClosed, $row['ID']);
        $stmt->execute();
    }
}

//-- If any statuses were deleted, re-index them before adding a new one
if ($wasStatusDeleted) {
    //-- First drop and re-add the ID column
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` DROP COLUMN `ID`");
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` ADD `ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");

    //-- Since statuses should be zero-based, but are now one-based, subtract one from each ID
    hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` SET `ID` = `ID`-1");
}

//-- Insert the addition if there is anything to add
if ($_POST['sN_shortName'] != null && $_POST['sN_longName'] != null && $_POST['sN_textColor'] != null)
{
    //-- The next ID is equal to the number of rows, since the IDs are zero-indexed.
    $nextValue = hesk_dbQuery('SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'statuses`')->num_rows;
    $insert = "INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` (`ID`, `ShortNameContentKey`, `TicketViewContentKey`, `TextColor`, `IsClosed`) VALUES (?, ?, ?, ?, ?)";
    $stmt = hesk_dbConnect()->prepare($insert);
    $isClosed = isset($_POST['sN_isClosed']) ? 1 : 0;
    $stmt->bind_param('isssi', $nextValue, $_POST['sN_shortName'], $_POST['sN_longName'], $_POST['sN_textColor'], $isClosed);
    $stmt->execute();
}

//-- Update default status for actions
$defaultQuery = "UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` SET ";

hesk_dbConnect()->query($defaultQuery . "`IsNewTicketStatus` = 0");
$updateQuery = $defaultQuery . "`IsNewTicketStatus` = 1 WHERE `ID` = ?";
$stmt = hesk_dbConnect()->prepare($updateQuery);
$stmt->bind_param('i', $_POST['newTicket']);
$stmt->execute();


hesk_dbConnect()->query($defaultQuery . "`IsClosedByClient` = 0");
$updateQuery = $defaultQuery . "`IsClosedByClient` = 1 WHERE `ID` = ?";
$stmt = hesk_dbConnect()->prepare($updateQuery);
$stmt->bind_param('i', $_POST['closedByClient']);
$stmt->execute();

hesk_dbConnect()->query($defaultQuery . "`IsCustomerReplyStatus` = 0");
$updateQuery = $defaultQuery . "`IsCustomerReplyStatus` = 1 WHERE `ID` = ?";
$stmt = hesk_dbConnect()->prepare($updateQuery);
$stmt->bind_param('i', $_POST['replyFromClient']);
$stmt->execute();

hesk_dbConnect()->query($defaultQuery . "`IsStaffClosedOption` = 0");
$updateQuery = $defaultQuery . "`IsStaffClosedOption` = 1 WHERE `ID` = ?";
$stmt = hesk_dbConnect()->prepare($updateQuery);
$stmt->bind_param('i', $_POST['staffClosedOption']);
$stmt->execute();

hesk_dbConnect()->query($defaultQuery . "`IsStaffReopenedStatus` = 0");
$updateQuery = $defaultQuery . "`IsStaffReopenedStatus` = 1 WHERE `ID` = ?";
$stmt = hesk_dbConnect()->prepare($updateQuery);
$stmt->bind_param('i', $_POST['staffReopenedStatus']);
$stmt->execute();

hesk_dbConnect()->query($defaultQuery . "`IsDefaultStaffReplyStatus` = 0");
$updateQuery = $defaultQuery . "`IsDefaultStaffReplyStatus` = 1 WHERE `ID` = ?";
$stmt = hesk_dbConnect()->prepare($updateQuery);
$stmt->bind_param('i', $_POST['defaultStaffReplyStatus']);
$stmt->execute();

hesk_dbConnect()->query($defaultQuery . "`LockedTicketStatus` = 0");
$updateQuery = $defaultQuery . "`LockedTicketStatus` = 1 WHERE `ID` = ?";
$stmt = hesk_dbConnect()->prepare($updateQuery);
$stmt->bind_param('i', $_POST['lockedTicketStatus']);
$stmt->execute();

//-- IP Bans
$ipBanSql = hesk_dbQuery('SELECT * FROM `'.$hesk_settings['db_pfix'].'denied_ips`');
while ($row = $ipBanSql->fetch_assoc()) {
    if (isset($_POST['ipDelete'][$row['ID']])) {
        hesk_dbQuery('DELETE FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'denied_ips` WHERE ID = '.hesk_dbEscape($row['ID']));
    } else {
        $ipAddressFrom = ip2long($_POST['ipFrom'][$row['ID']]);
        $ipAddressTo = ip2long($_POST['ipTo'][$row['ID']]);
        hesk_dbQuery('UPDATE `'.hesk_dbEscape($hesk_settings['db_pfix']).'denied_ips`
            SET `RangeStart` = \''.hesk_dbEscape($ipAddressFrom).'\',
                `RangeEnd` = \''.hesk_dbEscape($ipAddressTo).'\'
            WHERE ID = '.hesk_dbEscape($row['ID']));
    }
}
if (!empty($_POST['addIpFrom']) && !empty($_POST['addIpTo'])) {
    $ipAddressFrom = ip2long($_POST['addIpFrom']);
    $ipAddressTo = ip2long($_POST['addIpTo']);
    hesk_dbQuery('INSERT INTO `'.hesk_dbEscape($hesk_settings['db_pfix']).'denied_ips` (`RangeStart`, `RangeEnd`)
        VALUES (\''.hesk_dbEscape($ipAddressFrom).'\', \''.hesk_dbEscape($ipAddressTo).'\')');
}

//-- Email Bans
$emailBanSql = hesk_dbQuery('SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'denied_emails`');
while ($row = $emailBanSql->fetch_assoc()) {
    if (isset($_POST['emailDelete'][$row['ID']])) {
        hesk_dbQuery('DELETE FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'denied_emails` WHERE ID = '.hesk_dbEscape($row['ID']));
    } else {
        hesk_dbQuery('UPDATE `'.hesk_dbEscape($hesk_settings['db_pfix']).'denied_emails`
            SET Email = \''.hesk_dbEscape($_POST['email'][$row['ID']]).'\'
            WHERE ID = '.hesk_dbEscape($row['ID']));
    }
}
if (!empty($_POST['addEmail'])) {
    hesk_dbQuery('INSERT INTO `'.hesk_dbEscape($hesk_settings['db_pfix']).'denied_emails` (Email) VALUES (\''.hesk_dbEscape($_POST['addEmail']).'\')');
}

$set['hesk_version'] = $hesk_settings['hesk_version'];

// Save the modsForHesk_settings.inc.php file
$set['rtl'] = empty($_POST['rtl']) ? 0 : 1;
$set['show-icons'] = empty($_POST['show-icons']) ? 0 : 1;
$set['navbarBackgroundColor'] = hesk_input(hesk_POST('navbarBackgroundColor'));
$set['navbarBrandColor'] = hesk_input(hesk_POST('navbarBrandColor'));
$set['navbarBrandHoverColor'] = hesk_input(hesk_POST('navbarBrandHoverColor'));
$set['navbarItemTextColor'] = hesk_input(hesk_POST('navbarItemTextColor'));
$set['navbarItemTextHoverColor'] = hesk_input(hesk_POST('navbarItemTextHoverColor'));
$set['navbarItemTextSelectedColor'] = hesk_input(hesk_POST('navbarItemTextSelectedColor'));
$set['navbarItemSelectedBackgroundColor'] = hesk_input(hesk_POST('navbarItemSelectedBackgroundColor'));
$set['dropdownItemTextColor'] = hesk_input(hesk_POST('dropdownItemTextColor'));
$set['dropdownItemTextHoverColor'] = hesk_input(hesk_POST('dropdownItemTextHoverColor'));
$set['questionMarkColor'] = hesk_input(hesk_POST('questionMarkColor'));
$set['dropdownItemTextHoverBackgroundColor'] = hesk_input(hesk_POST('dropdownItemTextHoverBackgroundColor'));
$modsForHesk_file_content='<?php

//-- Mods For Hesk Theme Color Settings
$modsForHesk_settings[\'navbarBackgroundColor\'] = \''.$set['navbarBackgroundColor'].'\';
$modsForHesk_settings[\'navbarBrandColor\'] = \''.$set['navbarBrandColor'].'\';
$modsForHesk_settings[\'navbarBrandHoverColor\'] = \''.$set['navbarBrandHoverColor'].'\';
$modsForHesk_settings[\'navbarItemTextColor\'] = \''.$set['navbarItemTextColor'].'\';
$modsForHesk_settings[\'navbarItemTextHoverColor\'] = \''.$set['navbarItemTextHoverColor'].'\';
$modsForHesk_settings[\'navbarItemTextSelectedColor\'] = \''.$set['navbarItemTextSelectedColor'].'\';
$modsForHesk_settings[\'navbarItemSelectedBackgroundColor\'] = \''.$set['navbarItemSelectedBackgroundColor'].'\';
$modsForHesk_settings[\'dropdownItemTextColor\'] = \''.$set['dropdownItemTextColor'].'\';
$modsForHesk_settings[\'dropdownItemTextHoverColor\'] = \''.$set['dropdownItemTextHoverColor'].'\';
$modsForHesk_settings[\'dropdownItemTextHoverBackgroundColor\'] = \''.$set['dropdownItemTextHoverBackgroundColor'].'\';
$modsForHesk_settings[\'questionMarkColor\'] = \''.$set['questionMarkColor'].'\';

//-- Set this to 1 for right-to-left text.
$modsForHesk_settings[\'rtl\'] = '.$set['rtl'].';

//-- Set this to 1 to show icons next to navigation menu items
$modsForHesk_settings[\'show_icons\'] = '.$set['show-icons'].';';

// Write the file
if ( ! file_put_contents(HESK_PATH . 'modsForHesk_settings.inc.php', $modsForHesk_file_content) )
{
    hesk_error($hesklang['err_modsForHesk_settings']);
}


// Prepare settings file and save it
$settings_file_content='<?php
// Settings file for HESK ' . $set['hesk_version'] . '

// ==> GENERAL

// --> General settings
$hesk_settings[\'site_title\']=\'' . $set['site_title'] . '\';
$hesk_settings[\'site_url\']=\'' . $set['site_url'] . '\';
$hesk_settings[\'webmaster_mail\']=\'' . $set['webmaster_mail'] . '\';
$hesk_settings[\'noreply_mail\']=\'' . $set['noreply_mail'] . '\';
$hesk_settings[\'noreply_name\']=\'' . $set['noreply_name'] . '\';

// --> Language settings
$hesk_settings[\'can_sel_lang\']=' . $set['can_sel_lang'] . ';
$hesk_settings[\'language\']=\'' . $set['language'] . '\';
$hesk_settings[\'languages\']=array(
'.$set['languages'].');

// --> Database settings
$hesk_settings[\'db_host\']=\'' . $set['db_host'] . '\';
$hesk_settings[\'db_name\']=\'' . $set['db_name'] . '\';
$hesk_settings[\'db_user\']=\'' . $set['db_user'] . '\';
$hesk_settings[\'db_pass\']=\'' . $set['db_pass'] . '\';
$hesk_settings[\'db_pfix\']=\'' . $set['db_pfix'] . '\';
$hesk_settings[\'db_vrsn\']=' . $set['db_vrsn'] . ';


// ==> HELP DESK

// --> Help desk settings
$hesk_settings[\'hesk_title\']=\'' . $set['hesk_title'] . '\';
$hesk_settings[\'hesk_url\']=\'' . $set['hesk_url'] . '\';
$hesk_settings[\'admin_dir\']=\'' . $set['admin_dir'] . '\';
$hesk_settings[\'attach_dir\']=\'' . $set['attach_dir'] . '\';
$hesk_settings[\'max_listings\']=' . $set['max_listings'] . ';
$hesk_settings[\'print_font_size\']=' . $set['print_font_size'] . ';
$hesk_settings[\'autoclose\']=' . $set['autoclose'] . ';
$hesk_settings[\'max_open\']=' . $set['max_open'] . ';
$hesk_settings[\'new_top\']=' . $set['new_top'] . ';
$hesk_settings[\'reply_top\']=' . $set['reply_top'] . ';

// --> Features
$hesk_settings[\'autologin\']=' . $set['autologin'] . ';
$hesk_settings[\'autoassign\']=' . $set['autoassign'] . ';
$hesk_settings[\'custopen\']=' . $set['custopen'] . ';
$hesk_settings[\'rating\']=' . $set['rating'] . ';
$hesk_settings[\'cust_urgency\']=' . $set['cust_urgency'] . ';
$hesk_settings[\'sequential\']=' . $set['sequential'] . ';
$hesk_settings[\'list_users\']=' . $set['list_users'] . ';
$hesk_settings[\'debug_mode\']=' . $set['debug_mode'] . ';
$hesk_settings[\'short_link\']=' . $set['short_link'] . ';

// --> SPAM Prevention
$hesk_settings[\'secimg_use\']=' . $set['secimg_use'] . ';
$hesk_settings[\'secimg_sum\']=\'' . $set['secimg_sum'] . '\';
$hesk_settings[\'recaptcha_use\']=' . $set['recaptcha_use'] . ';
$hesk_settings[\'recaptcha_ssl\']=' . $set['recaptcha_ssl'] . ';
$hesk_settings[\'recaptcha_public_key\']=\'' . $set['recaptcha_public_key'] . '\';
$hesk_settings[\'recaptcha_private_key\']=\'' . $set['recaptcha_private_key'] . '\';
$hesk_settings[\'question_use\']=' . $set['question_use'] . ';
$hesk_settings[\'question_ask\']=\'' . $set['question_ask'] . '\';
$hesk_settings[\'question_ans\']=\'' . $set['question_ans'] . '\';

// --> Security
$hesk_settings[\'attempt_limit\']=' . $set['attempt_limit'] . ';
$hesk_settings[\'attempt_banmin\']=' . $set['attempt_banmin'] . ';
$hesk_settings[\'email_view_ticket\']=' . $set['email_view_ticket'] . ';

// --> Attachments
$hesk_settings[\'attachments\']=array (
\'use\' => ' . $set['attachments']['use'] . ',
\'max_number\' => ' . $set['attachments']['max_number'] . ',
\'max_size\' => ' . $set['attachments']['max_size'] . ',
\'allowed_types\' => array(\'' . implode('\',\'',$set['attachments']['allowed_types']) . '\')
);


// ==> KNOWLEDGEBASE

// --> Knowledgebase settings
$hesk_settings[\'kb_enable\']=' . $set['kb_enable'] . ';
$hesk_settings[\'kb_wysiwyg\']=' . $set['kb_wysiwyg'] . ';
$hesk_settings[\'kb_search\']=' . $set['kb_search'] . ';
$hesk_settings[\'kb_search_limit\']=' . $set['kb_search_limit'] . ';
$hesk_settings[\'kb_views\']=' . $set['kb_views'] . ';
$hesk_settings[\'kb_date\']=' . $set['kb_date'] . ';
$hesk_settings[\'kb_recommendanswers\']=' . $set['kb_recommendanswers'] . ';
$hesk_settings[\'kb_rating\']=' . $set['kb_rating'] . ';
$hesk_settings[\'kb_substrart\']=' . $set['kb_substrart'] . ';
$hesk_settings[\'kb_cols\']=' . $set['kb_cols'] . ';
$hesk_settings[\'kb_numshow\']=' . $set['kb_numshow'] . ';
$hesk_settings[\'kb_popart\']=' . $set['kb_popart'] . ';
$hesk_settings[\'kb_latest\']=' . $set['kb_latest'] . ';
$hesk_settings[\'kb_index_popart\']=' . $set['kb_index_popart'] . ';
$hesk_settings[\'kb_index_latest\']=' . $set['kb_index_latest'] . ';


// ==> EMAIL

// --> Email sending
$hesk_settings[\'smtp\']=' . $set['smtp'] . ';
$hesk_settings[\'smtp_host_name\']=\'' . $set['smtp_host_name'] . '\';
$hesk_settings[\'smtp_host_port\']=' . $set['smtp_host_port'] . ';
$hesk_settings[\'smtp_timeout\']=' . $set['smtp_timeout'] . ';
$hesk_settings[\'smtp_ssl\']=' . $set['smtp_ssl'] . ';
$hesk_settings[\'smtp_tls\']=' . $set['smtp_tls'] . ';
$hesk_settings[\'smtp_user\']=\'' . $set['smtp_user'] . '\';
$hesk_settings[\'smtp_password\']=\'' . $set['smtp_password'] . '\';

// --> Email piping
$hesk_settings[\'email_piping\']=' . $set['email_piping'] . ';

// --> POP3 Fetching
$hesk_settings[\'pop3\']=' . $set['pop3'] . ';
$hesk_settings[\'pop3_host_name\']=\'' . $set['pop3_host_name'] . '\';
$hesk_settings[\'pop3_host_port\']=' . $set['pop3_host_port'] . ';
$hesk_settings[\'pop3_tls\']=' . $set['pop3_tls'] . ';
$hesk_settings[\'pop3_keep\']=' . $set['pop3_keep'] . ';
$hesk_settings[\'pop3_user\']=\'' . $set['pop3_user'] . '\';
$hesk_settings[\'pop3_password\']=\'' . $set['pop3_password'] . '\';

// --> Email loops
$hesk_settings[\'loop_hits\']=' . $set['loop_hits'] . ';
$hesk_settings[\'loop_time\']=' . $set['loop_time'] . ';

// --> Detect email typos
$hesk_settings[\'detect_typos\']=' . $set['detect_typos'] . ';
$hesk_settings[\'email_providers\']=array(' . $set['email_providers'] . ');

// --> Other
$hesk_settings[\'strip_quoted\']=' . $set['strip_quoted'] . ';
$hesk_settings[\'save_embedded\']=' . $set['save_embedded'] . ';
$hesk_settings[\'multi_eml\']=' . $set['multi_eml'] . ';
$hesk_settings[\'confirm_email\']=' . $set['confirm_email'] . ';
$hesk_settings[\'open_only\']=' . $set['open_only'] . ';


// ==> MISC

// --> Date & Time
$hesk_settings[\'diff_hours\']=' . $set['diff_hours'] . ';
$hesk_settings[\'diff_minutes\']=' . $set['diff_minutes'] . ';
$hesk_settings[\'daylight\']=' . $set['daylight'] . ';
$hesk_settings[\'timeformat\']=\'' . $set['timeformat'] . '\';

// --> Other
$hesk_settings[\'alink\']=' . $set['alink'] . ';
$hesk_settings[\'submit_notice\']=' . $set['submit_notice'] . ';
$hesk_settings[\'online\']=' . $set['online'] . ';
$hesk_settings[\'online_min\']=' . $set['online_min'] . ';
$hesk_settings[\'check_updates\']=' . $set['check_updates'] . ';


// ==> CUSTOM FIELDS

$hesk_settings[\'custom_fields\']=array (
';

for ($i=1;$i<=20;$i++) {
    $settings_file_content.='\'custom'.$i.'\'=>array(\'use\'=>'.$set['custom_fields']['custom'.$i]['use'].',\'place\'=>'.$set['custom_fields']['custom'.$i]['place'].',\'type\'=>\''.$set['custom_fields']['custom'.$i]['type'].'\',\'req\'=>'.$set['custom_fields']['custom'.$i]['req'].',\'name\'=>\''.$set['custom_fields']['custom'.$i]['name'].'\',\'maxlen\'=>'.$set['custom_fields']['custom'.$i]['maxlen'].',\'value\'=>\''.$set['custom_fields']['custom'.$i]['value'].'\')';
    if ($i!=20) {$settings_file_content.=',
';}
}

$settings_file_content.='
);

#############################
#     DO NOT EDIT BELOW     #
#############################
$hesk_settings[\'hesk_version\']=\'' . $set['hesk_version'] . '\';
if ($hesk_settings[\'debug_mode\'])
{
    error_reporting(E_ALL);
}
else
{
    error_reporting(0);
}
if (!defined(\'IN_SCRIPT\')) {die(\'Invalid attempt!\');}';

// Write to the settings file
if ( ! file_put_contents(HESK_PATH . 'hesk_settings.inc.php', $settings_file_content) )
{
	hesk_error($hesklang['err_openset']);
}

// Any settings problems?
$tmp = array();

if ( ! $smtp_OK)
{
    $tmp[] = '<span style="color:red; font-weight:bold">'.$hesklang['sme'].':</span> '.$smtp_error.'<br /><br /><a href="Javascript:void(0)" onclick="Javascript:hesk_toggleLayerDisplay(\'smtplog\')">'.$hesklang['scl'].'</a><div id="smtplog" style="display:none">&nbsp;<br /><textarea name="log" rows="10" cols="60">'.$smtp_log.'</textarea></div>';
}

if ( ! $pop3_OK)
{
    $tmp[] = '<span style="color:red; font-weight:bold">'.$hesklang['pop3e'].':</span> '.$pop3_error.'<br /><br /><a href="Javascript:void(0)" onclick="Javascript:hesk_toggleLayerDisplay(\'pop3log\')">'.$hesklang['pop3log'].'</a><div id="pop3log" style="display:none">&nbsp;<br /><textarea name="log" rows="10" cols="60">'.$pop3_log.'</textarea></div>';
}

// Show the settings page and display any notices or success
if ( count($tmp) )
{
	$errors = implode('<br /><br />', $tmp);
    hesk_process_messages( $hesklang['sns'] . '<br /><br />' . $errors,'admin_settings.php','NOTICE');
}
else
{
	hesk_process_messages($hesklang['set_were_saved'],'admin_settings.php','SUCCESS');
}
exit();


/** FUNCTIONS **/

function hesk_checkMinMax($myint,$min,$max,$defval)
{
	if ($myint > $max || $myint < $min)
	{
		return $defval;
	}
	return $myint;
} // END hesk_checkMinMax()


function hesk_getLanguagesArray($returnArray=0)
{
	global $hesk_settings, $hesklang;

	/* Get a list of valid emails */
    $hesk_settings['smtp'] = 0;
    $valid_emails = array_keys( hesk_validEmails() );

	$dir = HESK_PATH . 'language/';
	$path = opendir($dir);
    $code = '';
    $langArray = array();

    /* Test all folders inside the language folder */
	while (false !== ($subdir = readdir($path)))
	{
		if ($subdir == "." || $subdir == "..")
	    {
	    	continue;
	    }

		if (filetype($dir . $subdir) == 'dir')
		{
        	$add   = 1;
	    	$langu = $dir . $subdir . '/text.php';
	        $email = $dir . $subdir . '/emails';

			/* Check the text.php */
	        if (file_exists($langu))
	        {
	        	$tmp = file_get_contents($langu);

				// Some servers add slashes to file_get_contents output
				if ( strpos ($tmp, '[\\\'LANGUAGE\\\']') !== false )
				{
					$tmp = stripslashes($tmp);
				}                

	            $err = '';
	        	if ( ! preg_match('/\$hesklang\[\'LANGUAGE\'\]\=\'(.*)\'\;/', $tmp, $l) )
	            {
	                $add = 0;
	            }
	            elseif ( ! preg_match('/\$hesklang\[\'ENCODING\'\]\=\'(.*)\'\;/', $tmp) )
	            {
	            	$add = 0;
	            }
                elseif ( ! preg_match('/\$hesklang\[\'_COLLATE\'\]\=\'(.*)\'\;/', $tmp) )
                {
                	$add = 0;
                }
                elseif ( ! preg_match('/\$hesklang\[\'EMAIL_HR\'\]\=\'(.*)\'\;/', $tmp, $hr) )
                {
                	$add = 0;
                }
                elseif ( ! preg_match('/\$hesklang\[\'recaptcha_error\'\]/', $tmp) )
                {
                	$add = 0;
                }
	        }
	        else
	        {
                $add   = 0;
	        }

            /* Check emails folder */
	        if (file_exists($email) && filetype($email) == 'dir')
	        {
	            foreach ($valid_emails as $eml)
	            {
	            	if (!file_exists($email.'/'.$eml.'.txt'))
	                {
	                	$add = 0;
	                }
	            }
	        }
	        else
	        {
	        	$add = 0;
	        }

            /* Add an option for the <select> if needed */
            if ($add)
            {
				$code .= "'".addslashes($l[1])."' => array('folder'=>'".$subdir."','hr'=>'".addslashes($hr[1])."'),\n";
                $langArray[] = $l[1];
            }
		}
	}

	closedir($path);

    if ($returnArray)
    {
		return $langArray;
    }
    else
    {
    	return $code;
    }
} // END hesk_getLanguagesArray()


function hesk_formatUnits($size)
{
    $units = array(
    	'GB' => 1073741824,
        'MB' => 1048576,
        'kB' => 1024,
        'B'  => 1
    );

    list($size, $suffix) = explode(' ', $size);

    if ( isset($units[$suffix]) )
    {
    	return round( $size * $units[$suffix] );
    }

    return false;
} // End hesk_formatBytes()

function validateStatus($shortName, $longName, $textColor)
{
    global $hesklang;

    //-- Validation logic
    if ($shortName == '')
    {
        hesk_process_messages($hesklang['shortNameRequired'], 'admin_settings.php');
    } elseif ($longName == '')
    {
        hesk_process_messages($hesklang['longNameRequired'], 'admin_settings.php');
    } elseif ($textColor == '')
    {
        hesk_process_messages($hesklang['textColorRequired'], 'admin_settings.php');
    }
}
?>
