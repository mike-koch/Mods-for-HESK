<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.6.0 beta 1 from 30th December 2014
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
        $hesk_settings['db_pfix'].'banned_emails',
        $hesk_settings['db_pfix'].'banned_ips',
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
        $hesk_settings['db_pfix'].'reply_drafts',
        $hesk_settings['db_pfix'].'reset_password',
        $hesk_settings['db_pfix'].'service_messages',
		$hesk_settings['db_pfix'].'std_replies',
		$hesk_settings['db_pfix'].'tickets',
        $hesk_settings['db_pfix'].'ticket_templates',
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
            <li><span style="color:#ff0000">Don't forget to run the <a href="<?php echo HESK_PATH . 'install/mods-for-hesk/modsForHesk.php'; ?>">Mods for HESK Installation</a>!</li>
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
  `ticket_id` varchar(13) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `saved_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `real_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `size` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`att_id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// -> Banned emails
    hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."banned_emails` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `banned_by` smallint(5) unsigned NOT NULL,
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8
");

// -> Banned IPs
    hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."banned_ips` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `ip_from` int(10) unsigned NOT NULL DEFAULT '0',
  `ip_to` int(10) unsigned NOT NULL DEFAULT '0',
  `ip_display` varchar(100) NOT NULL,
  `banned_by` smallint(5) unsigned NOT NULL,
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8
");

// -> Categories
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cat_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `autoassign` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `type` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `priority` enum('0','1','2','3') COLLATE utf8_unicode_ci NOT NULL DEFAULT '3',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// ---> Insert default category
hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` (`id`, `name`, `cat_order`) VALUES (1, 'General', 10)");

// -> KB Articles
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_articles` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `catid` smallint(5) unsigned NOT NULL,
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `author` smallint(5) unsigned NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `keywords` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `rating` float NOT NULL DEFAULT '0',
  `votes` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `views` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `type` enum('0','1','2') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `html` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `sticky` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `art_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `history` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `attachments` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `catid` (`catid`),
  KEY `sticky` (`sticky`),
  KEY `type` (`type`),
  FULLTEXT KEY `subject` (`subject`,`content`,`keywords`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// -> KB Attachments
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_attachments` (
  `att_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `saved_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `real_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `size` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`att_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// -> KB Categories
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_categories` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `parent` smallint(5) unsigned NOT NULL,
  `articles` smallint(5) unsigned NOT NULL DEFAULT '0',
  `articles_private` smallint(5) unsigned NOT NULL DEFAULT '0',
  `articles_draft` smallint(5) unsigned NOT NULL DEFAULT '0',
  `cat_order` smallint(5) unsigned NOT NULL,
  `type` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `parent` (`parent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// ---> Insert default KB category
hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_categories` (`id`, `name`, `parent`, `cat_order`, `type`) VALUES (1, 'Knowledgebase', 0, 10, '0')");

// -> Login attempts
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."logins` (
  `ip` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
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
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `message` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `deletedby` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `from` (`from`),
  KEY `to` (`to`,`read`,`deletedby`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// ---> Insert rate this script email
hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."mail` (`id`, `from`, `to`, `subject`, `message`, `dt`, `read`, `deletedby`) VALUES (1, 9999, 1, 'Rate this script', '<div style=\"text-align:justify;padding:3px\">\r\n\r\n<p style=\"color:green;font-weight:bold\">Enjoy using HESK? Please let others know!</p>\r\n\r\n<p>You are invited to rate HESK or even write a short review here:<br />&nbsp;<br /><img src=\"../img/link.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"\" style=\"vertical-align:text-bottom\" /> <a href=\"http://www.hotscripts.com/Detailed/46973.html\" target=\"_blank\">Rate this script @ Hot Scripts</a><br />&nbsp;<br /><img src=\"../img/link.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"\" style=\"vertical-align:text-bottom\" /> <a href=\"http://php.resourceindex.com/detail/04946.html\" target=\"_blank\">Rate this script @ The PHP Resource Index</a></p>\r\n\r\n<p>Thank you,<br />&nbsp;<br />Klemen,<br />\r\n<a href=\"http://www.hesk.com/\" target=\"_blank\">www.hesk.com</a>\r\n\r\n<p>&nbsp;</p>', NOW(), '0', 9999)");

// ---> Insert welcome email
hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."mail` (`id`, `from`, `to`, `subject`, `message`, `dt`, `read`, `deletedby`) VALUES (2, 9999, 1, 'Welcome to HESK! Here are some quick tips...', '<p style=\"color:green;font-weight:bold\">HESK quick &quot;Getting Started&quot; tips:<br />&nbsp;</p>\r\n\r\n<ol style=\"padding-left:20px;padding-right:10px;text-align:justify\">\r\n<li>Click the Profile link to set your name, email, signature and password.<br />&nbsp;</li>\r\n<li>Click the Settings link in the top menu to get to the Settings page. For additional information about each setting, click the [?] link.<br />&nbsp;</li>\r\n<li>Add new categories (departments) on the Categories page. The default category cannot be deleted, but it can be renamed.<br />&nbsp;</li>\r\n<li>Create new staff accounts on the Users page. You can give them unlimited (Administrator) or restricted (Staff) access.<br />&nbsp;</li>\r\n<li>Use the integrated Knowledgebase. A comprehensive and well-written knowledgebase can drastically reduce the number of support tickets you receive and save a lot of your time in the long run.<br />&nbsp;</li>\r\n<li>You can create response and new ticket templates on the Canned page.<br />&nbsp;</li>\r\n<li>Subscribe to the <a href=\"http://www.hesk.com/newsletter.php\" target=\"_blank\">HESK Newsletter</a> to be notified of updates and new versions.<br />&nbsp;</li>\r\n<li>You should follow HESK on Twitter <a href=\"https://twitter.com/HESKdotCOM\" target=\"_blank\">here</a>.<br />&nbsp;</li>\r\n<li>To remove the &quot;<span class=\"smaller\">Powered by Help Desk Software HESK</span>&quot; links from the bottom of your help desk <a href=\"https://www.hesk.com/buy.php\" target=\"_blank\">buy a license here</a>.<br />&nbsp;</li></ol>\r\n\r\n<p>Enjoy using HESK and please feel free to share your constructive feedback and feature suggestions.</p>\r\n\r\n<p>Klemen Stirn<br />\r\nHESK owner and author<br />\r\n<a href=\"http://www.hesk.com/\" target=\"_blank\">www.hesk.com</a>', NOW(), '0', 9999)");

// -> Notes
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."notes` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ticket` mediumint(8) unsigned NOT NULL,
  `who` smallint(5) unsigned NOT NULL,
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `message` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `attachments` mediumtext COLLATE utf8_unicode_ci NOT NULL,
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
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hits` smallint(1) unsigned NOT NULL DEFAULT '0',
  `message_hash` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `email` (`email`,`hits`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// -> Replies
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."replies` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `replyto` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `message` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `attachments` mediumtext COLLATE utf8_unicode_ci,
  `staffid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rating` enum('1','5') COLLATE utf8_unicode_ci DEFAULT NULL,
  `read` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `replyto` (`replyto`),
  KEY `dt` (`dt`),
  KEY `staffid` (`staffid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// -> Reply drafts
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."reply_drafts` (
  `owner` smallint(5) unsigned NOT NULL,
  `ticket` mediumint(8) unsigned NOT NULL,
  `message` mediumtext CHARACTER SET utf8 NOT NULL,
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `owner` (`owner`),
  KEY `ticket` (`ticket`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// -> Reset password
    hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."reset_password` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user` smallint(5) unsigned NOT NULL,
  `hash` char(40) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
");

// -> Service messages
    hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."service_messages` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `author` smallint(5) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `message` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `style` enum('0','1','2','3','4') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `type` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `order` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

// -> Canned Responses
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."std_replies` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `message` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `reply_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// -> Tickets
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `trackid` varchar(13) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(1000) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `category` smallint(5) unsigned NOT NULL DEFAULT '1',
  `priority` enum('0','1','2','3') COLLATE utf8_unicode_ci NOT NULL DEFAULT '3',
  `subject` varchar(70) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `message` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `dt` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastchange` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `firstreply` timestamp NULL DEFAULT NULL,
  `closedat` timestamp NULL DEFAULT NULL,
  `articles` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `language` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('0','1','2','3','4','5') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `openedby` smallint(5) unsigned DEFAULT '0',
  `firstreplyby` smallint(5) unsigned DEFAULT NULL,
  `closedby` smallint(5) unsigned DEFAULT NULL,
  `replies` smallint(5) unsigned NOT NULL DEFAULT '0',
  `staffreplies` smallint(5) unsigned NOT NULL DEFAULT '0',
  `owner` smallint(5) unsigned NOT NULL DEFAULT '0',
  `time_worked` time NOT NULL DEFAULT '00:00:00',
  `lastreplier` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `replierid` smallint(5) unsigned DEFAULT NULL,
  `archive` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `locked` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `attachments` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `merged` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `history` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `custom1` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `custom2` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `custom3` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `custom4` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `custom5` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `custom6` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `custom7` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `custom8` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `custom9` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `custom10` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `custom11` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `custom12` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `custom13` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `custom14` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `custom15` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `custom16` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `custom17` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `custom18` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `custom19` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `custom20` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `trackid` (`trackid`),
  KEY `archive` (`archive`),
  KEY `categories` (`category`),
  KEY `statuses` (`status`),
  KEY `owner` (`owner`),
  KEY `openedby` (`openedby`,`firstreplyby`,`closedby`),
  KEY `dt` (`dt`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// -> Ticket templates
    hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."ticket_templates` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `message` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `tpl_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

// -> Users
hesk_dbQuery("
CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `pass` char(40) COLLATE utf8_unicode_ci NOT NULL,
  `isadmin` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `signature` varchar(1000) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `language` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `categories` varchar(500) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `afterreply` enum('0','1','2') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `autostart` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `notify_customer_new` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `notify_customer_reply` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `show_suggested` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `notify_new_unassigned` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `notify_new_my` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `notify_reply_unassigned` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `notify_reply_my` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `notify_assigned` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `notify_pm` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `notify_note` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `default_list` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `autoassign` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `heskprivileges` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ratingneg` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ratingpos` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rating` float NOT NULL DEFAULT '0',
  `replies` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `autoassign` (`autoassign`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
");

    hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."users` (`id`, `user`, `pass`, `isadmin`, `name`, `email`, `heskprivileges`) VALUES (1, '".hesk_dbEscape($_SESSION['admin_user'])."', '".hesk_dbEscape($_SESSION['admin_hash'])."', '1', 'Your name', 'you@me.com', '')");

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
    $set['notify_spam_tags'] = count($set['notify_spam_tags']) ?  "'" . implode("','", $set['notify_spam_tags']) . "'" : '';

    // Check if PHP version is 5.2.3+
    $set['db_vrsn'] = (version_compare(PHP_VERSION, '5.2.3') >= 0) ? 1 : 0;

	hesk_iSaveSettingsFile($set);

	return true;
} // End hesk_iSaveSettings()
?>
