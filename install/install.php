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

define('INSTALL_PAGE', 'install.php');
require(HESK_PATH . 'install/install_functions.inc.php');

// If no step is defined, start with step 1
if ( ! isset($_SESSION['step']) )
{
    $_SESSION['step']=1;
}
// Check if the license has been agreed to and verify sessions are working
elseif ($_SESSION['step']==1)
{
    $agree = hesk_POST('agree', '');
    if ($agree == 'YES')
    {
		// Are sessions working?
		if ( empty($_SESSION['works']) )
        {
        	hesk_iSessionError();
        }

		// All OK, continue
        $_SESSION['license_agree']=1;
        $_SESSION['step']=2;
    }
    else
    {
        $_SESSION['step']=1;
    }
}

// Test database connection?
if ($_SESSION['step'] == 3 && isset($_POST['dbtest']))
{
	// Username
	$_SESSION['admin_user'] = hesk_input( hesk_POST('admin_user') );
	if ( strlen($_SESSION['admin_user']) == 0 )
	{
		$_SESSION['admin_user'] = 'Administrator';
	}

	// Password
	$_SESSION['admin_pass'] = hesk_input( hesk_POST('admin_pass') );
	if ( strlen($_SESSION['admin_pass']) == 0 )
	{
		$_SESSION['admin_pass'] = substr(str_shuffle("23456789abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ"), 0, mt_rand(8,12) );
	}

	// Password hash for the database
	$_SESSION['admin_hash'] = hesk_Pass2Hash($_SESSION['admin_pass']);
    
    $hesk_db_link = hesk_iTestDatabaseConnection();

	// Get table prefix, don't allow any special chars
    $hesk_settings['db_pfix'] = preg_replace('/[^0-9a-zA-Z_]/', '', hesk_POST('pfix', 'hesk_') );

    // Generate HESK table names
	$hesk_tables = array(
		$hesk_settings['db_pfix'].'attachments',
		$hesk_settings['db_pfix'].'categories',
		$hesk_settings['db_pfix'].'kb_articles',
		$hesk_settings['db_pfix'].'kb_attachments',
		$hesk_settings['db_pfix'].'kb_categories',
		$hesk_settings['db_pfix'].'logins',
		$hesk_settings['db_pfix'].'mail',
		$hesk_settings['db_pfix'].'notes',
		$hesk_settings['db_pfix'].'online',
		$hesk_settings['db_pfix'].'pipe_loops',
		$hesk_settings['db_pfix'].'replies',
		$hesk_settings['db_pfix'].'std_replies',
		$hesk_settings['db_pfix'].'tickets',
		$hesk_settings['db_pfix'].'users',
	);

	// Check if any of the HESK tables exists
	$res = hesk_dbQuery('SHOW TABLES FROM `'.hesk_dbEscape($hesk_settings['db_name']).'`');

	while ($row = hesk_dbFetchRow($res))
	{
		if (in_array($row[0],$hesk_tables))
		{
			hesk_iDatabase(2);
		}
	}

	// All ok, let's save settings
	hesk_iSaveSettings();

	// Now install HESK database tables
	hesk_iTables();

	// And move to the next step
	$_SESSION['step']=4;
}

// Which step are we at?
switch ($_SESSION['step'])
{
	case 2:
	   hesk_iCheckSetup();
	   break;
	case 3:
	   hesk_iDatabase();
	   break;
	case 4:
	   hesk_iFinish();
	   break;
	default:
	   hesk_iStart();
}


// ******* FUNCTIONS ******* //


function hesk_iFinish()
{
    global $hesk_settings;
    hesk_iHeader();
	?>
	
	<div class="row">
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <p>Summary</p>
                </div>
                <div class="panel-body">
                    <p>Congratulations, you have successfully completed HESK database setup!</p>
                </div>
          </div>
	  </div>
	  <div class="col-md-8">
		<div class="alert alert-success"><strong>Success!</strong> HESK Successfully installed</div>
		<div class="h3">Next Steps:<br/><br/></div>
        <ol>
            <li><span style="color:#ff0000">Don't forget to run the <a href="<?php echo HESK_PATH . 'install/updateModsForHesk.php'; ?>">Mods for HESK Installation</a>!</li>
            <li>Remember your login details:<br />

<pre style="font-size: 1.17em">
Username: <span style="color:red; font-weight:bold"><?php echo stripslashes($_SESSION['admin_user']); ?></span>
Password: <span style="color:red; font-weight:bold"><?php echo stripslashes($_SESSION['admin_pass']); ?></span>
</pre>
            </li>
        </ol>
		
		<form action="<?php echo HESK_PATH; ?>admin/index.php" method="post">
			<input type="hidden" name="a" value="do_login" />
			<input type="hidden" name="remember_user" value="JUSTUSER" />
			<input type="hidden" name="user" value="<?php echo stripslashes($_SESSION['admin_user']); ?>" />
			<input type="hidden" name="pass" value="<?php echo stripslashes($_SESSION['admin_pass']); ?>" />
			<input type="hidden" name="goto" value="admin_settings.php" />
			<center><button type="submit" class="btn btn-default btn-lg">Login</button></center>
		</form>
	</div>
	</div>

	<?php
    hesk_iFooter();
} // End hesk_iFinish()


function hesk_iTables()
{
	global $hesk_settings;

// -> Attachments
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` (
  `att_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` varchar(13) NOT NULL DEFAULT '',
  `saved_name` varchar(255) NOT NULL DEFAULT '',
  `real_name` varchar(255) NOT NULL DEFAULT '',
  `size` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`att_id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// -> Categories
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL DEFAULT '',
  `cat_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `autoassign` enum('0','1') NOT NULL DEFAULT '1',
  `type` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// ---> Insert default category
hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` (`id`, `name`, `cat_order`, `autoassign`, `type`) VALUES (1, 'General', 10, '1', '0')");

// -> KB Articles
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_articles` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `catid` smallint(5) unsigned NOT NULL,
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `author` smallint(5) unsigned NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `keywords` mediumtext NOT NULL,
  `rating` float NOT NULL DEFAULT '0',
  `votes` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `views` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `type` enum('0','1','2') NOT NULL DEFAULT '0',
  `html` enum('0','1') NOT NULL DEFAULT '0',
  `sticky` enum('0','1') NOT NULL DEFAULT '0',
  `art_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `history` mediumtext NOT NULL,
  `attachments` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `catid` (`catid`),
  KEY `type` (`type`),
  KEY `sticky` (`sticky`),
  FULLTEXT KEY `subject` (`subject`,`content`,`keywords`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// -> KB Attachments
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_attachments` (
  `att_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `saved_name` varchar(255) NOT NULL DEFAULT '',
  `real_name` varchar(255) NOT NULL DEFAULT '',
  `size` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`att_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// -> KB Categories
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_categories` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `parent` smallint(5) unsigned NOT NULL,
  `articles` smallint(5) unsigned NOT NULL DEFAULT '0',
  `articles_private` smallint(5) unsigned NOT NULL DEFAULT '0',
  `articles_draft` smallint(5) unsigned NOT NULL DEFAULT '0',
  `cat_order` smallint(5) unsigned NOT NULL,
  `type` enum('0','1') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `parent` (`parent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// ---> Insert default KB category
hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_categories` (`id`, `name`, `parent`, `articles`, `cat_order`, `type`) VALUES (1, 'Knowledgebase', 0, 0, 10, '0')");

// -> Login attempts
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."logins` (
  `ip` varchar(46) NOT NULL,
  `number` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `last_attempt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// -> Private messages
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."mail` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from` smallint(5) unsigned NOT NULL,
  `to` smallint(5) unsigned NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` mediumtext NOT NULL,
  `dt` datetime NOT NULL,
  `read` enum('0','1') NOT NULL DEFAULT '0',
  `deletedby` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `to` (`to`,`read`,`deletedby`),
  KEY `from` (`from`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// ---> Insert rate this script email
hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."mail` (`id`, `from`, `to`, `subject`, `message`, `dt`, `read`, `deletedby`) VALUES (1, 9999, 1, 'Rate this script', '<div style=\"text-align:justify;padding:3px\">\r\n\r\n<p style=\"color:green;font-weight:bold\">Enjoy using HESK? Please let others know!</p>\r\n\r\n<p>You are invited to rate HESK or even write a short review here:<br />&nbsp;<br /><img src=\"../img/link.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"\" style=\"vertical-align:text-bottom\" /> <a href=\"http://www.hotscripts.com/Detailed/46973.html\" target=\"_blank\">Rate this script @ Hot Scripts</a><br />&nbsp;<br /><img src=\"../img/link.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"\" style=\"vertical-align:text-bottom\" /> <a href=\"http://php.resourceindex.com/detail/04946.html\" target=\"_blank\">Rate this script @ The PHP Resource Index</a></p>\r\n\r\n<p>Thank you,<br />&nbsp;<br />Klemen,<br />\r\n<a href=\"http://www.hesk.com/\" target=\"_blank\">www.hesk.com</a>\r\n\r\n<p>&nbsp;</p>', NOW(), '0', 9999)");

// ---> Insert welcome email
hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."mail` (`id`, `from`, `to`, `subject`, `message`, `dt`, `read`, `deletedby`) VALUES (2, 9999, 1, 'Welcome to HESK!', '<div style=\"text-align:justify;padding:3px\">\r\n\r\n<p style=\"color:green;font-weight:bold\">Congratulations for installing HESK, a lightweight and easy-to-use ticket support system!</p>\r\n\r\n<p>I am sure you are eager to use your <b>HESK&trade;</b> helpdesk to improve your customer support and reduce your workload, so check the rest of this message for some quick  &quot;Getting Started&quot; tips.</p>\r\n\r\n<p>Once you have learned the power of <b>HESK&trade;</b>, please consider supporting its future enhancement by purchasing an <a href=\"https://www.hesk.com/buy.php\" target=\"_blank\">inexpensive license</a>. Having a site license will remove the &quot;Powered by Help Desk Software HESK&quot; links from the bottom of your screens to make it look even more professional.</p>\r\n\r\n<p>Enjoy using HESK&trade; - and I value receiving your constructive feedback and feature suggestions.</p>\r\n\r\n<p>Klemen Stirn,<br />\r\nHESK owner and author<br />\r\n<a href=\"http://www.hesk.com/\" target=\"_blank\">www.hesk.com</a>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p style=\"text-align:center;font-weight:bold\">*** Quick &quot;Getting Started&quot; Tips ***</p>\r\n\r\n<ul style=\"padding-left:20px;padding-right:10px\">\r\n<li>Click the profile link to set your Profile name, e-mail, signature, and *CHANGE YOUR PASSWORD*.<br />&nbsp;</li>\r\n<li>Click the settings link in the top menu to get to the Settings page. Take some time and get familiar with all the available settings. Most should be self-explanatory; for additional information about each setting, click the [?] link for help about the current setting.<br />&nbsp;</li>\r\n<li>Create new staff accounts on the Users page. The default user (Administrator) cannot be deleted, but you can change the password on the Profile page.<br />&nbsp;</li>\r\n<li>Add new categories (departments) on the Categories page. The default category cannot be deleted, but it can be renamed.<br />&nbsp;</li>\r\n<li>Use the integrated Knowledgebase - it is one of the most powerful support tools as it gives self-help resources to your customers. A comprehensive and well-written knowledgebase can drastically reduce the number of support tickets you receive and save a lot of your time in the long run. Arrange answers to frequently asked questions and articles into categories.<br />&nbsp;</li>\r\n<li>Create canned responses on the Canned Responses page. These are pre-written replies to common support questions. However, you should also contribute by adding answers to other typical questions in the Knowledgebase.<br />&nbsp;</li>\r\n<li>Subscribe to the <a href=\"http://www.hesk.com/newsletter.php\" target=\"_blank\">HESK Newsletter</a> to be notified of updates and new versions.<br />&nbsp;</li>\r\n<li><a href=\"https://www.hesk.com/buy.php\" target=\"_blank\">Buy a license</a> to remove the &quot;<span class=\"smaller\">Powered by Help Desk Software HESK</span>&quot; links from the bottom of your help desk.<br />&nbsp;</li></ul>\r\n\r\n</div>', NOW(), '0', 9999)");

// -> Notes
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."notes` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ticket` mediumint(8) unsigned NOT NULL,
  `who` smallint(5) unsigned NOT NULL,
  `dt` datetime NOT NULL,
  `message` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ticketid` (`ticket`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// -> Online
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."online` (
  `user_id` smallint(5) unsigned NOT NULL,
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `tmp` int(11) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `user_id` (`user_id`),
  KEY `dt` (`dt`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// -> Pipe loops
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."pipe_loops` (
  `email` varchar(255) NOT NULL,
  `hits` smallint(1) unsigned NOT NULL DEFAULT '0',
  `message_hash` char(32) NOT NULL,
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `email` (`email`,`hits`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// -> Replies
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."replies` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `replyto` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `message` mediumtext NOT NULL,
  `dt` datetime DEFAULT NULL,
  `attachments` mediumtext,
  `staffid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rating` enum('0','1','5') NOT NULL DEFAULT '0',
  `read` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `replyto` (`replyto`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// -> Canned Responses
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."std_replies` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '',
  `message` mediumtext NOT NULL,
  `reply_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// -> Tickets
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `trackid` varchar(13) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `category` smallint(5) unsigned NOT NULL DEFAULT '1',
  `priority` enum('0','1','2','3') NOT NULL DEFAULT '3',
  `subject` varchar(70) NOT NULL DEFAULT '',
  `message` mediumtext NOT NULL,
  `dt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastchange` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip` varchar(46) NOT NULL DEFAULT '',
  `language` varchar(50) DEFAULT NULL,
  `status` enum('0','1','2','3','4','5') NOT NULL DEFAULT '0',
  `owner` smallint(5) unsigned NOT NULL DEFAULT '0',
  `time_worked` time NOT NULL DEFAULT '00:00:00',
  `lastreplier` enum('0','1') NOT NULL DEFAULT '0',
  `replierid` smallint(5) unsigned DEFAULT NULL,
  `archive` enum('0','1') NOT NULL DEFAULT '0',
  `locked` enum('0','1') NOT NULL DEFAULT '0',
  `attachments` mediumtext NOT NULL,
  `merged` mediumtext NOT NULL,
  `history` mediumtext NOT NULL,
  `custom1` mediumtext NOT NULL,
  `custom2` mediumtext NOT NULL,
  `custom3` mediumtext NOT NULL,
  `custom4` mediumtext NOT NULL,
  `custom5` mediumtext NOT NULL,
  `custom6` mediumtext NOT NULL,
  `custom7` mediumtext NOT NULL,
  `custom8` mediumtext NOT NULL,
  `custom9` mediumtext NOT NULL,
  `custom10` mediumtext NOT NULL,
  `custom11` mediumtext NOT NULL,
  `custom12` mediumtext NOT NULL,
  `custom13` mediumtext NOT NULL,
  `custom14` mediumtext NOT NULL,
  `custom15` mediumtext NOT NULL,
  `custom16` mediumtext NOT NULL,
  `custom17` mediumtext NOT NULL,
  `custom18` mediumtext NOT NULL,
  `custom19` mediumtext NOT NULL,
  `custom20` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `trackid` (`trackid`),
  KEY `archive` (`archive`),
  KEY `categories` (`category`),
  KEY `statuses` (`status`),
  KEY `owner` (`owner`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// -> Users
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(20) NOT NULL DEFAULT '',
  `pass` char(40) NOT NULL,
  `isadmin` enum('0','1') NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `signature` varchar(255) NOT NULL DEFAULT '',
  `language` varchar(50) DEFAULT NULL,
  `categories` varchar(255) NOT NULL DEFAULT '',
  `afterreply` enum('0','1','2') NOT NULL DEFAULT '0',
  `autostart` enum('0','1') NOT NULL DEFAULT '1',
  `notify_new_unassigned` enum('0','1') NOT NULL DEFAULT '1',
  `notify_new_my` enum('0','1') NOT NULL DEFAULT '1',
  `notify_reply_unassigned` enum('0','1') NOT NULL DEFAULT '1',
  `notify_reply_my` enum('0','1') NOT NULL DEFAULT '1',
  `notify_assigned` enum('0','1') NOT NULL DEFAULT '1',
  `notify_pm` enum('0','1') NOT NULL DEFAULT '1',
  `notify_note` enum('0','1') NOT NULL DEFAULT '1',
  `default_list` varchar(255) NOT NULL DEFAULT '',
  `autoassign` enum('0','1') NOT NULL DEFAULT '1',
  `heskprivileges` mediumtext NOT NULL,
  `ratingneg` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ratingpos` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rating` float NOT NULL DEFAULT '0',
  `replies` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `autoassign` (`autoassign`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."users` (`id`, `user`, `pass`, `isadmin`, `name`, `email`, `signature`, `heskprivileges`) VALUES (1, '".hesk_dbEscape($_SESSION['admin_user'])."', '".hesk_dbEscape($_SESSION['admin_hash'])."', '1', 'Your name', 'you@me.com', 'Sincerely,\r\n\r\nYour name\r\nYour website\r\nhttp://www.yourwebsite.com', '')");

	return true;

} // End hesk_iTables()


function hesk_iSaveSettings()
{
	global $hesk_settings, $hesklang;

	$spam_question = hesk_generate_SPAM_question();

	$hesk_settings['secimg_use'] = empty($_SESSION['set_captcha']) ? 0 : 1;
	$hesk_settings['use_spamq'] = empty($_SESSION['use_spamq']) ? 0 : 1;
	$hesk_settings['question_ask'] = $spam_question[0];
	$hesk_settings['question_ans'] = $spam_question[1];
	$hesk_settings['set_attachments'] = empty($_SESSION['set_attachments']) ? 0 : 1;
	$hesk_settings['hesk_version'] = HESK_NEW_VERSION;

	if (isset($_SERVER['HTTP_HOST']))
	{
		$hesk_settings['site_url']='http://' . $_SERVER['HTTP_HOST'];

		if (isset($_SERVER['REQUEST_URI']))
		{
			$hesk_settings['hesk_url']='http://' . $_SERVER['HTTP_HOST'] . str_replace('/install/install.php','',$_SERVER['REQUEST_URI']);
		}
	}

	/* Encode and escape characters */
	$set = $hesk_settings;
	foreach ($hesk_settings as $k=> $v)
	{
		if (is_array($v))
		{
			continue;
		}
		$set[$k] = addslashes($v);
	}
	$set['debug_mode'] = 0;

	$set['email_providers'] = count($set['email_providers']) ?  "'" . implode("','", $set['email_providers']) . "'" : '';

    // Check if PHP version is 5.2.3+ and MySQL is 5.0.7+
	$res = hesk_dbQuery('SELECT VERSION() AS version');
	$set['db_vrsn'] = (version_compare(PHP_VERSION, '5.2.3') >= 0 && version_compare( hesk_dbResult($res) , '5.0.7') >= 0) ? 1 : 0;

	hesk_iSaveSettingsFile($set);

	return true;
} // End hesk_iSaveSettings()
?>
