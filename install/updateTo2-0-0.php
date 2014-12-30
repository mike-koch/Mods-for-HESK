<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');

$updateSuccess = true;

hesk_dbConnect();
hesk_dbQuery("CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."verified_emails` (`Email` VARCHAR(255) NOT NULL)");
hesk_dbQuery("CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."pending_verification_emails` (`Email` VARCHAR(255) NOT NULL, `ActivationKey` VARCHAR(500) NOT NULL)");
hesk_dbQuery("CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."stage_tickets` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `trackid` varchar(13) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `category` smallint(5) unsigned NOT NULL DEFAULT '1',
  `priority` enum('0','1','2','3') COLLATE utf8_unicode_ci NOT NULL DEFAULT '3',
  `subject` varchar(70) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `message` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `dt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastchange` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip` varchar(46) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `language` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
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
  `parent` mediumint(8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trackid` (`trackid`),
  KEY `archive` (`archive`),
  KEY `categories` (`category`),
  KEY `statuses` (`status`),
  KEY `owner` (`owner`)
)");
hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."settings` SET `Value` = '2.0.0' WHERE `Key` = 'modsForHeskVersion'");

//-- Add the new custom field property to modsForHesk_settings.inc.php
$file = file_get_contents(HESK_PATH . 'modsForHesk_settings.inc.php');
$file .= '

//-- Set this to 1 to enable custom field names as keys
$modsForHesk_settings[\'custom_field_setting\'] = 0;';

if (!file_put_contents(HESK_PATH.'modsForHesk_settings.inc.php', $file))
{
    $updateSuccess = false;
    echo '<h1>Failure!</h1><p>An issue occurred when trying to update the modsForHesk_settings.inc.php file.</p>';
}

if ($updateSuccess) {
?>

<h1>Installation / Update complete!</h1>
<p>Please delete the <b>install</b> folder for security reasons, and then proceed back to the <a href="../">Help Desk</a></p>

<?php } ?>