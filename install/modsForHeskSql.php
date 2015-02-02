<?php
// Version 1.0.0 - <1.4.0
function executePre140Scripts() {
    hesk_dbConnect();
    //-- Need to do this since we are no longer restricted on IDs and we want an INT for proper INNER JOINs
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` ADD COLUMN `status_int` INT NOT NULL DEFAULT 0 AFTER `status`");
    $ticketsRS = hesk_dbQuery("SELECT `id`, `status` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets`");
    while ($currentResult = $ticketsRS->fetch_assoc())
    {
        hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `status_int` = ".$currentResult['status']." WHERE `id` = ".$currentResult['id']);
    }
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` DROP COLUMN `status`");
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` CHANGE COLUMN `status_int` `status` INT NOT NULL");

    hesk_dbQuery("CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` (
                      `ID` INT NOT NULL,
                      `ShortNameContentKey` TEXT NOT NULL,
                      `TicketViewContentKey` TEXT NOT NULL,
                      `TextColor` TEXT NOT NULL,
                      `IsNewTicketStatus` BIT NOT NULL DEFAULT 0,
                      `IsClosed` BIT NOT NULL DEFAULT 0,
                      `IsClosedByClient` BIT NOT NULL DEFAULT 0,
                      `IsCustomerReplyStatus` BIT NOT NULL DEFAULT 0,
                      `IsStaffClosedOption` BIT NOT NULL DEFAULT 0,
                      `IsStaffReopenedStatus` BIT NOT NULL DEFAULT 0,
                      `IsDefaultStaffReplyStatus` BIT NOT NULL DEFAULT 0,
                      `LockedTicketStatus` BIT NOT NULL DEFAULT 0,
                        PRIMARY KEY (`ID`))");
    hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (0, 'open', 'open', '#FF0000', 1, 0, 0, 0, 0, 0, 0, 0);");

    hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (1, 'wait_reply', 'wait_staff_reply', '#FF9933', 0, 0, 0, 1, 0, 1, 0, 0);");
    hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (2, 'replied', 'wait_cust_reply', '#0000FF', 0, 0, 0, 0, 0, 0, 1, 0);");
    hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (3, 'resolved', 'resolved', '#008000', 0, 1, 1, 0, 1, 0, 0, 1);");
    hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (4, 'in_progress', 'in_progress', '#000000', 0, 0, 0, 0, 0, 0, 0, 0);");
    hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (5, 'on_hold', 'on_hold', '#000000', 0, 0, 0, 0, 0, 0, 0, 0);");
}

// Version 1.4.0
function execute140Scripts() {
    hesk_dbConnect();
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` ADD COLUMN `autorefresh` BIGINT NOT NULL DEFAULT 0 AFTER `replies`;");

    hesk_dbQuery("CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."denied_ips` (
	  `ID` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	  `RangeStart` VARCHAR(100) NOT NULL,
	  `RangeEnd` VARCHAR(100) NOT NULL)");
}

// Version 1.4.1
function execute141Scripts() {
    hesk_dbConnect();
    hesk_dbQuery("CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."denied_emails` (ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT, Email VARCHAR(100) NOT NULL);");
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` ADD COLUMN `parent` MEDIUMINT(8) NULL AFTER `custom20`;");
}

// Version 1.5.0
function execute150Scripts() {
    hesk_dbConnect();
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` ADD COLUMN `active` ENUM('0', '1') NOT NULL DEFAULT '1'");
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` ADD COLUMN `can_manage_settings` ENUM('0', '1') NOT NULL DEFAULT '1'");
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` ADD COLUMN `default_notify_customer_email` ENUM ('0', '1') NOT NULL DEFAULT '1'");
}

// Version 1.6.0
function execute160Scripts() {
    hesk_dbConnect();
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` ADD COLUMN `notify_note_unassigned` ENUM('0', '1') NOT NULL DEFAULT '0'");
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` ADD COLUMN `can_change_notification_settings` ENUM('0', '1') NOT NULL DEFAULT '1'");
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."notes` ADD COLUMN `edit_date` DATETIME NULL");
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."notes` ADD COLUMN `number_of_edits` INT NOT NULL DEFAULT 0");
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` ADD COLUMN `note_id` INT NULL AFTER `ticket_id`");
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` MODIFY COLUMN `ticket_id` VARCHAR(13) NULL");
    hesk_dbQuery("CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."settings` (`Key` NVARCHAR(200) NOT NULL, `Value` NVARCHAR(200) NOT NULL)");
    hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."settings` (`Key`, `Value`) VALUES ('modsForHeskVersion', '1.6.0')");
}

// Version 1.6.1
function execute161Scripts() {
    hesk_dbConnect();
    hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."settings` SET `Value` = '1.6.1' WHERE `Key` = 'modsForHeskVersion'");
}

// BEGIN Version 1.7.0
function execute170Scripts() {
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
    hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."settings` SET `Value` = '1.7.0' WHERE `Key` = 'modsForHeskVersion'");
}

function execute170FileUpdate() {
    //-- Add the new custom field property to modsForHesk_settings.inc.php
    $file = file_get_contents(HESK_PATH . 'modsForHesk_settings.inc.php');

    //-- Only add the additional settings if they aren't already there.
    if (strpos($file, 'custom_field_setting') !== true)
    {
        $file .= '

        //-- Set this to 1 to enable custom field names as keys
        $modsForHesk_settings[\'custom_field_setting\'] = 0;

        //-- Set this to 1 to enable email verification for new customers
        $modsForHesk_settings[\'customer_email_verification_required\'] = 0;';
    }

    return file_put_contents(HESK_PATH.'modsForHesk_settings.inc.php', $file);
}
// END Version 1.7.0

// BEGIN Version 2.0.0
function execute200Scripts() {
    hesk_dbConnect();
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` DROP COLUMN `note_id`");
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."notes` DROP COLUMN `edit_date`");
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."notes` DROP COLUMN `number_of_edits`");
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` DROP COLUMN `default_notify_customer_email`");
    hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."settings` SET `Value` = '2.0.0' WHERE `Key` = 'modsForHeskVersion'");

    $keyRs = hesk_dbQuery("SHOW KEYS FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE Key_name='statuses'");
    if (hesk_dbNumRows($keyRs) == 0)
    {
        //-- Add the key
        hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` ADD KEY `statuses` (`status`)");
    }
}

function execute200FileUpdate() {
    //-- Add the new HTML email property to modsForHesk_settings.inc.php
    $file = file_get_contents(HESK_PATH . 'modsForHesk_settings.inc.php');

    //-- Only add the additional settings if they aren't already there.
    if (strpos($file, 'html_emails') !== true)
    {
        $file .= '

        //-- Set this to 1 to enable HTML-formatted emails.
        $modsForHesk_settings[\'html_emails\'] = 0;

        //-- Mailgun Settings
        $modsForHesk_settings[\'use_mailgun\'] = 0;
        $modsForHesk_settings[\'mailgun_api_key\'] = \'API Key\';
        $modsForHesk_settings[\'mailgun_domain\'] = \'mail.domain.com\';';
    }

    return file_put_contents(HESK_PATH.'modsForHesk_settings.inc.php', $file);
}

function checkForIpOrEmailBans() {
    hesk_dbConnect();
    $banRS = hesk_dbQuery("SELECT `ID` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."denied_emails`
                        UNION ALL SELECT `ID` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."denied_ips`");

    return hesk_dbNumRows($banRS);
}

function getUsers() {
    hesk_dbConnect();
    $users = array();

    $usersRS = hesk_dbQuery("SELECT `id`, `name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `active` = '1' ORDER BY `name`");
    while ($row = hesk_dbFetchAssoc($usersRS)) {
        $users[$row['id']] = $row['name'];
    }

    return $users;
}

function migrateBans() {
    hesk_dbConnect();

    // Get the ID of the creator
    $creator = $_POST['user'];

    // Insert the email bans
    $emailBanRS = hesk_dbQuery("SELECT `Email` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."denied_emails`");
    while ($row = hesk_dbFetchAssoc($emailBanRS)) {
        hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."banned_emails` (`email`, `banned_by`, `dt`)
                VALUES ('".hesk_dbEscape($row['Email'])."', ".$creator.", NOW())");
    }

    // Insert the IP bans
    $ipBanRS = hesk_dbQuery("SELECT `RangeStart`, `RangeEnd` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."denied_ips`");
    while ($row = hesk_dbFetchAssoc($ipBanRS)) {
        $ipFrom = long2ip($row['RangeStart']);
        $ipTo = long2ip($row['RangeEnd']);
        $ipDisplay = $ipFrom == $ipTo ? $ipFrom : $ipFrom . ' - ' . $ipTo;
        hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."banned_ips` (`ip_from`, `ip_to`, `ip_display`, `banned_by`, `dt`)
                VALUES (".$row['RangeStart'].", ".$row['RangeEnd'].", '".$ipDisplay."', ".$creator.", NOW())");
    }
    // Migration Complete. Drop Tables.
    hesk_dbQuery("DROP TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."denied_ips`");
    hesk_dbQuery("DROP TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."denied_emails`");
}
// END Version 2.0.0