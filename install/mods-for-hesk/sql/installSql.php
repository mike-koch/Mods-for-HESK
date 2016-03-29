<?php
require(HESK_PATH . 'hesk_settings.inc.php');

function executeQuery($sql)
{
    global $hesk_last_query;
    global $hesk_db_link;
    if (function_exists('mysqli_connect')) {

        if (!$hesk_db_link && !hesk_dbConnect()) {
            return false;
        }

        $hesk_last_query = $sql;

        if ($res = @mysqli_query($hesk_db_link, $sql)) {
            return $res;
        } else {
            print "Could not execute query: $sql. MySQL said: " . mysqli_error($hesk_db_link);
            http_response_code(500);
            die();
        }
    } else {
        if (!$hesk_db_link && !hesk_dbConnect()) {
            return false;
        }

        $hesk_last_query = $sql;

        if ($res = @mysql_query($sql, $hesk_db_link)) {
            return $res;
        } else {
            print "Could not execute query: $sql. MySQL said: " . mysql_error();
            http_response_code(500);
            die();
        }
    }
}

// Version 1.0.0 - <1.4.0
function executePre140Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();
    //-- Need to do this since we are no longer restricted on IDs and we want an INT for proper INNER JOINs
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `status_int` INT NOT NULL DEFAULT 0 AFTER `status`;");

    $ticketsRS = executeQuery("SELECT `id`, `status` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets`;");
    while ($currentResult = $ticketsRS->fetch_assoc()) {

        executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `status_int` = " . $currentResult['status'] . " WHERE `id` = " . $currentResult['id']);
    }
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `status`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` CHANGE COLUMN `status_int` `status` INT NOT NULL");

    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` (
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
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (0, 'open', 'open', '#FF0000', 1, 0, 0, 0, 0, 0, 0, 0);");

    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (1, 'wait_reply', 'wait_staff_reply', '#FF9933', 0, 0, 0, 1, 0, 1, 0, 0);");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (2, 'replied', 'wait_cust_reply', '#0000FF', 0, 0, 0, 0, 0, 0, 1, 0);");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (3, 'resolved', 'resolved', '#008000', 0, 1, 1, 0, 1, 0, 0, 1);");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (4, 'in_progress', 'in_progress', '#000000', 0, 0, 0, 0, 0, 0, 0, 0);");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (5, 'on_hold', 'on_hold', '#000000', 0, 0, 0, 0, 0, 0, 0, 0);");

    $keyRs = executeQuery("SHOW KEYS FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE Key_name='statuses'");
    if (hesk_dbNumRows($keyRs) == 0) {
        //-- Add the key
        executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD KEY `statuses` (`status`)");
    }
}

// Version 1.4.0
function execute140Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `autorefresh` BIGINT NOT NULL DEFAULT 0 AFTER `replies`;");

    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_ips` (
	  `ID` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	  `RangeStart` VARCHAR(100) NOT NULL,
	  `RangeEnd` VARCHAR(100) NOT NULL)");
}

// Version 1.4.1
function execute141Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_emails` (ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT, Email VARCHAR(100) NOT NULL);");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `parent` MEDIUMINT(8) NULL AFTER `custom20`;");
}

// Version 1.5.0
function execute150Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `active` ENUM('0', '1') NOT NULL DEFAULT '1'");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `can_manage_settings` ENUM('0', '1') NOT NULL DEFAULT '1'");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `default_notify_customer_email` ENUM ('0', '1') NOT NULL DEFAULT '1'");
}

// Version 1.6.0
function execute160Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `notify_note_unassigned` ENUM('0', '1') NOT NULL DEFAULT '0'");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `can_change_notification_settings` ENUM('0', '1') NOT NULL DEFAULT '1'");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` ADD COLUMN `edit_date` DATETIME NULL");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` ADD COLUMN `number_of_edits` INT NOT NULL DEFAULT 0");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` ADD COLUMN `note_id` INT NULL AFTER `ticket_id`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` MODIFY COLUMN `ticket_id` VARCHAR(13) NULL");
    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key` NVARCHAR(200) NOT NULL, `Value` NVARCHAR(200) NOT NULL)");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('modsForHeskVersion', '1.6.0')");
}

// Version 1.6.1
function execute161Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '1.6.1' WHERE `Key` = 'modsForHeskVersion'");
}

// BEGIN Version 1.7.0
function execute170Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "verified_emails` (`Email` VARCHAR(255) NOT NULL)");
    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "pending_verification_emails` (`Email` VARCHAR(255) NOT NULL, `ActivationKey` VARCHAR(500) NOT NULL)");
    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` (
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
    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '1.7.0' WHERE `Key` = 'modsForHeskVersion'");
}
// END Version 1.7.0

// BEGIN Version 2.0.0
function execute200Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` DROP COLUMN `note_id`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` DROP COLUMN `edit_date`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` DROP COLUMN `number_of_edits`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `default_notify_customer_email`");
    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.0.0' WHERE `Key` = 'modsForHeskVersion'");

    $keyRs = executeQuery("SHOW KEYS FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE Key_name='statuses'");
    if (hesk_dbNumRows($keyRs) == 0) {
        //-- Add the key
        executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD KEY `statuses` (`status`)");
    }
}

function checkForIpOrEmailBans()
{
    global $hesk_settings;

    hesk_dbConnect();
    $banRS = executeQuery("SELECT `ID` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_emails`
                        UNION ALL SELECT `ID` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_ips`");

    return hesk_dbNumRows($banRS);
}

function getUsers()
{
    global $hesk_settings;

    hesk_dbConnect();
    $users = array();
    $usersRS = executeQuery("SELECT `id`, `name` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `active` = '1' ORDER BY `name`");
    while ($row = hesk_dbFetchAssoc($usersRS)) {
        array_push($users, $row);
    }

    return $users;
}

function migrateBans($creator)
{
    global $hesk_settings;

    hesk_dbConnect();

    // Insert the email bans
    $emailBanRS = executeQuery("SELECT `Email` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_emails`");
    while ($row = hesk_dbFetchAssoc($emailBanRS)) {
        executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "banned_emails` (`email`, `banned_by`, `dt`)
                VALUES ('" . hesk_dbEscape($row['Email']) . "', " . $creator . ", NOW())");
    }

    // Insert the IP bans
    $ipBanRS = executeQuery("SELECT `RangeStart`, `RangeEnd` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_ips`");
    while ($row = hesk_dbFetchAssoc($ipBanRS)) {
        $ipFrom = long2ip($row['RangeStart']);
        $ipTo = long2ip($row['RangeEnd']);
        $ipDisplay = $ipFrom == $ipTo ? $ipFrom : $ipFrom . ' - ' . $ipTo;
        executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "banned_ips` (`ip_from`, `ip_to`, `ip_display`, `banned_by`, `dt`)
                VALUES (" . $row['RangeStart'] . ", " . $row['RangeEnd'] . ", '" . $ipDisplay . "', " . $creator . ", NOW())");
    }
    // Migration Complete. Drop Tables.
    executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_ips`");
    executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_emails`");
}

// END Version 2.0.0

// Version 2.0.1
function execute201Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.0.1' WHERE `Key` = 'modsForHeskVersion'");
}

// BEGIN Version 2.1.0
function execute210Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.1.0' WHERE `Key` = 'modsForHeskVersion'");

    // Some old tables may not have been dropped during the 2.0.0 upgrade. Check and drop if necessary
    executeQuery("DROP TABLE IF EXISTS `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_ips`");
    executeQuery("DROP TABLE IF EXISTS `" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_emails`");
}
// END Version 2.1.0

// BEGIN Version 2.1.1
function execute211Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("ALTER IGNORE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` CHANGE `dt` `dt` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets`
					CHANGE `email` `email` VARCHAR( 1000 ) NOT NULL DEFAULT '',
					CHANGE `ip` `ip` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
					ADD `firstreply` TIMESTAMP NULL DEFAULT NULL AFTER `lastchange`,
					ADD `closedat` TIMESTAMP NULL DEFAULT NULL AFTER `firstreply`,
					ADD `articles` VARCHAR(255) NULL DEFAULT NULL AFTER `closedat`,
					ADD `openedby` MEDIUMINT(8) DEFAULT '0' AFTER `status`,
					ADD `firstreplyby` SMALLINT(5) UNSIGNED NULL DEFAULT NULL AFTER `openedby`,
					ADD `closedby` MEDIUMINT(8) NULL DEFAULT NULL AFTER `firstreplyby`,
					ADD `replies` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `closedby`,
					ADD `staffreplies` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `replies`,
					ADD INDEX ( `openedby` , `firstreplyby` , `closedby` ),
					ADD INDEX(`dt`)");
    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.1.1' WHERE `Key` = 'modsForHeskVersion'");
}
// END Version 2.1.1

// BEGIN Version 2.2.0
function execute220Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ADD COLUMN `IsAutocloseOption` INT NOT NULL DEFAULT 0");

    // There will only ever be one row
    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` SET `IsAutocloseOption` = 1 WHERE `IsStaffClosedOption` = 1");

    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ADD COLUMN `Closable` VARCHAR(10) NOT NULL");
    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` SET `Closable` = 'yes'");
    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.2.0' WHERE `Key` = 'modsForHeskVersion'");
}
// END Version 2.2.0

// BEGIN Version 2.2.1
function execute221Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.2.1' WHERE `Key` = 'modsForHeskVersion'");
}

// END Version 2.2.1

// BEGIN Version 2.3.0
function execute230Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages` ADD COLUMN `icon` VARCHAR(150)");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ADD COLUMN `Key` TEXT");
    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` SET `Key` = `ShortNameContentKey`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` DROP COLUMN `ShortNameContentKey`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` DROP COLUMN `TicketViewContentKey`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `latitude` VARCHAR(100) NOT NULL DEFAULT 'E-0'");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `longitude` VARCHAR(100) NOT NULL DEFAULT 'E-0'");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` ADD COLUMN `latitude` VARCHAR(100) NOT NULL DEFAULT 'E-0'");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` ADD COLUMN `longitude` VARCHAR(100) NOT NULL DEFAULT 'E-0'");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ADD COLUMN `manager` INT NOT NULL DEFAULT 0");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `permission_template` INT");
    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "permission_templates` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(255) NOT NULL,
                    `heskprivileges` VARCHAR(1000),
                    `categories` VARCHAR(500))");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "permission_templates` (`name`, `heskprivileges`, `categories`)
        VALUES ('Administrator', 'ALL', 'ALL')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "permission_templates` (`name`, `heskprivileges`, `categories`)
        VALUES ('Staff', 'can_view_tickets,can_reply_tickets,can_change_cat,can_assign_self,can_view_unassigned,can_view_online', '1')");
    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `permission_template` = 1 WHERE `isadmin` = '1'");

    // Move can_manage_settings and can_change_notification_settings into the heskprivileges list
    $res = executeQuery("SELECT `id`, `heskprivileges` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `isadmin` = '0'
        AND `can_manage_settings` = '1'");
    while ($row = hesk_dbFetchAssoc($res)) {
        if ($row['heskprivileges'] != '') {
            $currentPrivileges = explode(',', $row['heskprivileges']);
            array_push($currentPrivileges, 'can_man_settings');
            $newPrivileges = implode(',', $currentPrivileges);
            executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `heskprivileges` = '" . hesk_dbEscape($newPrivileges) . "'
            WHERE `id` = " . intval($row['id']));
        } else {
            executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `heskprivileges` = 'can_man_settings'
            WHERE `id` = " . intval($row['id']));
        }
    }
    $res = executeQuery("SELECT `id`, `heskprivileges` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `isadmin` = '0'
        AND `can_change_notification_settings` = '1'");
    while ($row = hesk_dbFetchAssoc($res)) {
        if ($row['heskprivileges'] != '') {
            $currentPrivileges = explode(',', $row['heskprivileges']);
            array_push($currentPrivileges, 'can_change_notification_settings');
            $newPrivileges = implode(',', $currentPrivileges);
            executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `heskprivileges` = '" . hesk_dbEscape($newPrivileges) . "'
            WHERE `id` = " . intval($row['id']));
        } else {
            executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `heskprivileges` = 'can_change_notification_settings'
            WHERE `id` = " . intval($row['id']));
        }
    }
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `can_manage_settings`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `can_change_notification_settings`");

    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.3.0' WHERE `Key` = 'modsForHeskVersion'");
}
// END Version 2.3.0


// BEGIN Version 2.3.1
function execute231Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.3.1' WHERE `Key` = 'modsForHeskVersion'");
}

// END Verison 2.3.1

// BEGIN Version 2.3.2
function execute232Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.3.2' WHERE `Key` = 'modsForHeskVersion'");
}

// END Version 2.3.2

// BEGIN Version 2.4.0
function execute240Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();

    // Setup quick help sections
    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `location` VARCHAR(100) NOT NULL,
      `show` ENUM('0','1') NOT NULL
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` (`location`, `show`)
      VALUES ('create_ticket', '1')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` (`location`, `show`)
      VALUES ('view_ticket_form', '1')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` (`location`, `show`)
      VALUES ('view_ticket', '1')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` (`location`, `show`)
      VALUES ('knowledgebase', '1')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` (`location`, `show`)
      VALUES ('staff_create_ticket', '1')");

    // Setup status improvement tables
    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "text_to_status_xref` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `language` VARCHAR(200) NOT NULL,
      `text` VARCHAR(200) NOT NULL,
      `status_id` INT NOT NULL) ENGINE = MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ADD COLUMN `sort` INT");
    $statusesRs = executeQuery("SELECT `ID` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ORDER BY `ID` ASC");
    $i = 10;
    while ($myStatus = hesk_dbFetchAssoc($statusesRs)) {
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` SET `sort`=" . intval($i) . "
            WHERE `id`='" . intval($myStatus['ID']) . "' LIMIT 1");
        $i += 10;
    }

    // Process attachment improvement tables
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` ADD COLUMN `download_count` INT NOT NULL DEFAULT 0");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_attachments` ADD COLUMN `download_count` INT NOT NULL DEFAULT 0");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `html` ENUM('0','1') NOT NULL DEFAULT '0'");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` ADD COLUMN `html` ENUM('0','1') NOT NULL DEFAULT '0'");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` ADD COLUMN `html` ENUM('0','1') NOT NULL DEFAULT '0'");

    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.4.0' WHERE `Key` = 'modsForHeskVersion'");
}

function initializeXrefTable()
{
    global $hesk_settings, $hesklang;

    hesk_dbConnect();
    $languages = array();
    foreach ($hesk_settings['languages'] as $key => $value) {
        $languages[$key] = $hesk_settings['languages'][$key]['folder'];
    }

    $statusesRs = executeQuery("SELECT `ID`, `Key` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses`");
    $oldSetting = $hesk_settings['can_sel_lang'];
    $hesk_settings['can_sel_lang'] = 1;
    while ($row = hesk_dbFetchAssoc($statusesRs)) {
        foreach ($languages as $language => $languageCode) {
            hesk_setLanguage($language);
            $sql = "INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "text_to_status_xref` (`language`, `text`, `status_id`)
                VALUES ('" . hesk_dbEscape($language) . "', '" . hesk_dbEscape($hesklang[$row['Key']]) . "', " . intval($row['ID']) . ")";
            executeQuery($sql);
        }
    }
    $hesk_settings['can_sel_lang'] = $oldSetting;
    hesk_resetLanguage();
}
// END Version 2.4.0

// BEGIN Version 2.4.1
function execute241Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();

    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.4.1' WHERE `Key` = 'modsForHeskVersion'");
}
// END Version 2.4.1

// Version 2.4.2
function execute242Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();

    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.4.2' WHERE `Key` = 'modsForHeskVersion'");
}

// BEGIN Version 2.5.0
function migrateSettings()
{
    global $hesk_settings;

    hesk_dbConnect();

    $modsForHesk_settings = array();
    if (file_exists(HESK_PATH . 'modsForHesk_settings.inc.php')) {
        require_once(HESK_PATH . 'modsForHesk_settings.inc.php');
    }

    $rtl = getSettingValue($modsForHesk_settings, 'rtl', 0);
    $show_icons = getSettingValue($modsForHesk_settings, 'show_icons', 0);
    $custom_field_setting = getSettingValue($modsForHesk_settings, 'custom_field_setting', 0);
    $customer_email_verification_required = getSettingValue($modsForHesk_settings, 'customer_email_verification_required', 0);
    $html_emails = getSettingValue($modsForHesk_settings, 'html_emails', 1);
    $use_mailgun = getSettingValue($modsForHesk_settings, 'use_mailgun', 0);
    $mailgun_api_key = getSettingValue($modsForHesk_settings, 'mailgun_api_key', '');
    $mailgun_domain = getSettingValue($modsForHesk_settings, 'mailgun_domain', '');
    $use_bootstrap_theme = getSettingValue($modsForHesk_settings, 'use_bootstrap_theme', 1);
    $new_kb_article_visibility = getSettingValue($modsForHesk_settings, 'new_kb_article_visibility', 0);
    $attachments = getSettingValue($modsForHesk_settings, 'attachments', 0);
    $show_number_merged = getSettingValue($modsForHesk_settings, 'show_number_merged', 1);
    $request_location = getSettingValue($modsForHesk_settings, 'request_location', 0);
    $category_order_column = getSettingValue($modsForHesk_settings, 'category_order_column', 'cat_order');
    $rich_text_for_tickets = getSettingValue($modsForHesk_settings, 'rich_text_for_tickets', 0);
    $statuses_order_column = getSettingValue($modsForHesk_settings, 'statuses_order_column', 'sort');
    $kb_attach_dir = getSettingValue($modsForHesk_settings, 'kb_attach_dir', 'attachments');
    $rich_text_for_tickets_for_customers = getSettingValue($modsForHesk_settings, 'rich_text_for_tickets_for_customers', 0);

    $navbar_background_color = getSettingValue($modsForHesk_settings, 'navbarBackgroundColor', '#414a5c');
    $navbar_brand_color = getSettingValue($modsForHesk_settings, 'navbarBrandColor', '#d4dee7');
    $navbar_brand_hover_color = getSettingValue($modsForHesk_settings, 'navbarBrandHoverColor', '#ffffff');
    $navbar_item_text_color = getSettingValue($modsForHesk_settings, 'navbarItemTextColor', '#d4dee7');
    $navbar_item_text_hover_color = getSettingValue($modsForHesk_settings, 'navbarItemTextHoverColor', '#ffffff');
    $navbar_item_text_selected_color = getSettingValue($modsForHesk_settings, 'navbarItemTextSelectedColor', '#ffffff');
    $navbar_item_selected_background_color = getSettingValue($modsForHesk_settings, 'navbarItemSelectedBackgroundColor', '#2d3646');
    $dropdown_item_text_color = getSettingValue($modsForHesk_settings, 'dropdownItemTextColor', '#333333');
    $dropdown_item_text_hover_color = getSettingValue($modsForHesk_settings, 'dropdownItemTextHoverColor', '#262626');
    $dropdown_item_text_hover_background_color = getSettingValue($modsForHesk_settings, 'dropdownItemTextHoverBackgroundColor', '#f5f5f5');
    $question_mark_color = getSettingValue($modsForHesk_settings, 'questionMarkColor', '#000000');


    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('rtl', " . intval($rtl) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('show_icons', " . intval($show_icons) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('custom_field_setting', " . intval($custom_field_setting) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('customer_email_verification_required', " . intval($customer_email_verification_required) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('html_emails', " . intval($html_emails) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('use_mailgun', " . intval($use_mailgun) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('mailgun_api_key', '" . hesk_dbEscape($mailgun_api_key) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('mailgun_domain', '" . hesk_dbEscape($mailgun_domain) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('use_bootstrap_theme', " . intval($use_bootstrap_theme) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('new_kb_article_visibility', " . intval($new_kb_article_visibility) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('attachments', " . intval($attachments) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('show_number_merged', " . intval($show_number_merged) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('request_location', " . intval($request_location) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('category_order_column', '" . hesk_dbEscape($category_order_column) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('rich_text_for_tickets', " . intval($rich_text_for_tickets) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('statuses_order_column', '" . hesk_dbEscape($statuses_order_column) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('kb_attach_dir', '" . hesk_dbEscape($kb_attach_dir) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('rich_text_for_tickets_for_customers', " . intval($rich_text_for_tickets_for_customers) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarBackgroundColor', '" . hesk_dbEscape($navbar_background_color) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarBrandColor', '" . hesk_dbEscape($navbar_brand_color) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarBrandHoverColor', '" . hesk_dbEscape($navbar_brand_hover_color) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarItemTextColor', '" . hesk_dbEscape($navbar_item_text_color) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarItemTextHoverColor', '" . hesk_dbEscape($navbar_item_text_hover_color) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarItemTextSelectedColor', '" . hesk_dbEscape($navbar_item_text_selected_color) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarItemSelectedBackgroundColor', '" . hesk_dbEscape($navbar_item_selected_background_color) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('dropdownItemTextColor', '" . hesk_dbEscape($dropdown_item_text_color) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('dropdownItemTextHoverColor', '" . hesk_dbEscape($dropdown_item_text_hover_color) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('dropdownItemTextHoverBackgroundColor', '" . hesk_dbEscape($dropdown_item_text_hover_background_color) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('questionMarkColor', '" . hesk_dbEscape($question_mark_color) . "')");
}

function getSettingValue($settings, $setting, $default)
{
    return isset($settings[$setting]) ? $settings[$setting] : $default;
}

function execute250Scripts()
{
    global $hesk_settings;

    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `user_agent` TEXT");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `screen_resolution_width` INT");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `screen_resolution_height` INT");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` ADD COLUMN `user_agent` TEXT");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` ADD COLUMN `screen_resolution_width` INT");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` ADD COLUMN `screen_resolution_height` INT");

    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('display_user_agent_information', '0')");

    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('navbar_title_url', '" . hesk_dbEscape($hesk_settings['hesk_url']) . "')");

    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.5.0' WHERE `Key` = 'modsForHeskVersion'");
}
// END Version 2.5.0

// Version 2.5.1
function execute251Scripts()
{
    global $hesk_settings;
    hesk_dbConnect();

    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.5.1' WHERE `Key` = 'modsForHeskVersion'");
}

// Version 2.5.2
function execute252Scripts()
{
    global $hesk_settings;
    hesk_dbConnect();

    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.5.2' WHERE `Key` = 'modsForHeskVersion'");
}

// Version 2.6.0
function execute260Scripts()
{
    global $hesk_settings;
    hesk_dbConnect();

    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('public_api', '1')");
	executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "logging` (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(200),
        `message` MEDIUMTEXT NOT NULL,
        `severity` INT NOT NULL,
        `location` MEDIUMTEXT,
        `timestamp` TIMESTAMP NOT NULL) ENGINE = MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "user_api_tokens` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT NOT NULL,
      `token` VARCHAR(500) NOT NULL) ENGINE = MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "temp_attachment` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `file_name` VARCHAR(255) NOT NULL,
      `saved_name` VARCHAR(255) NOT NULL,
      `size` INT(10) UNSIGNED NOT NULL,
      `type` ENUM('0','1') NOT NULL,
      `date_uploaded` TIMESTAMP NOT NULL) ENGINE = MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `start` DATETIME,
      `end` DATETIME,
      `all_day` ENUM('0','1') NOT NULL,
      `name` VARCHAR(255) NOT NULL,
      `location` VARCHAR(255),
      `comments` MEDIUMTEXT,
      `category` INT NOT NULL) ENGINE = MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event_reminder` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT NOT NULL,
      `event_id` INT NOT NULL,
      `amount` INT NOT NULL,
      `unit` INT NOT NULL,
      `email_sent` ENUM('0', '1') NOT NULL DEFAULT '0') ENGINE = MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `due_date` DATETIME");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ADD COLUMN `color` VARCHAR(7)");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ADD COLUMN `usage` INT NOT NULL DEFAULT 0");
    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.6.0' WHERE `Key` = 'modsForHeskVersion'");
}