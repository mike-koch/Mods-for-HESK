#!/usr/bin/php -q
<?php

define('IN_SCRIPT',1);
define('HESK_PATH', dirname(dirname(__FILE__)) . '/');
$LOCATION = 'Calendar Reminders Cron Job';

#echo HESK_PATH."\n";

// Get required files and functions
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');

if (defined('HESK_DEMO')) {
    echo '>>>>>>> DEMO MODE IS ENABLED. CRON JOBS CANNOT BE EXECUTED WHILE IN DEMO MODE! <<<<<<<';
    die();
}

if (hesk_check_maintenance(false)) {
    // If Debug mode is ON show "Maintenance mode" message
    $message = $hesk_settings['debug_mode'] ? $hesklang['mm1'] : '';
    $message .= "\n";
    die($message);
}

hesk_load_cron_database_functions();
hesk_dbConnect();

$modsForHesk_settings = mfh_getSettings();
$skip_events = $modsForHesk_settings['enable_calendar'] == 0;

if ($hesk_settings['debug_mode']) {
    echo "Starting Calendar Reminders...\n";
}

// Get all reminders that have a reminder date that is now or earlier, and an email has not been sent for the event yet.
/*
 * Reminder units:
 * 0 - minutes
 * 1 - hours
 * 2 - days
 * 3 - weeks
 */
$old_timeformat_setting = $hesk_settings['timeformat'];
$hesk_settings['timeformat'] = 'Y-m-d H:i:s';
$current_date = hesk_date();

$case_statement = "CASE
      WHEN `unit` = '0' THEN DATE_SUB(`event`.`start`, INTERVAL `reminder`.`amount` MINUTE)
      WHEN `unit` = '1' THEN DATE_SUB(`event`.`start`, INTERVAL `reminder`.`amount` HOUR)
      WHEN `unit` = '2' THEN DATE_SUB(`event`.`start`, INTERVAL `reminder`.`amount` DAY)
      WHEN `unit` = '3' THEN DATE_SUB(`event`.`start`, INTERVAL `reminder`.`amount` WEEK)
    END";
$sql = "SELECT `reminder`.`id` AS `reminder_id`, `reminder`.`user_id` AS `user_id`, `reminder`.`event_id` AS `event_id`,
    `event`.`name` AS `event_name`, `event`.`location` AS `event_location`, `event`.`comments` AS `event_comments`,
    `category`.`name` AS `event_category`, `event`.`start` AS `event_start`, `event`.`end` AS `event_end`,
    `event`.`all_day` AS `event_all_day`, `user`.`language` AS `user_language`, `user`.`email` AS `user_email`,
    " . $case_statement . " AS `reminder_date`, 'EVENT' AS `type`
    FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event_reminder` AS `reminder`
    INNER JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event` AS `event`
        ON `reminder`.`event_id` = `event`.`id`
    INNER JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` AS `category`
        ON `event`.`category` = `category`.`id`
    INNER JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` AS `user`
        ON `reminder`.`user_id` = `user`.`id`
    WHERE (" . $case_statement . ") <= '{$current_date}'
    AND `email_sent` = '0'";

$rs = hesk_dbQuery($sql);
$reminders_to_flag = array();
$tickets_to_flag = array();

$included_email_functions = false;
if (hesk_dbNumRows($rs) > 0 && !$skip_events) {
    require(HESK_PATH . 'inc/email_functions.inc.php');
    $included_email_functions = true;
}

$successful_emails = 0;
$failed_emails = 0;
while ($row = hesk_dbFetchAssoc($rs)) {
    if ($skip_events) {
        return true;
    }

    if (mfh_sendCalendarReminder($row, $modsForHesk_settings)) {
        $reminders_to_flag[] = $row['reminder_id'];
        $successful_emails++;

        if ($hesk_settings['debug_mode']) {
            $debug_msg = "Sent e-mail reminder for event: {$row['event_name']} to {$row['user_email']}\n";
            echo $debug_msg;
            mfh_log_debug($LOCATION, $debug_msg, 'CRON');
        }
    } else {
        $failed_emails++;

        $warning_text = "Failed to send reminder e-mail for event: {$row['event_name']} to {$row['user_email']}. This will be re-sent next time reminders are processed.\n";
        mfh_log_warning($LOCATION, $warning_text, 'CRON');
        echo $warning_text;
    }
}

if (count($reminders_to_flag) > 0) {
    foreach ($reminders_to_flag as $reminder_id) {
        $sql = "UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event_reminder` SET `email_sent` = '1' WHERE `id` = " . intval($reminder_id);
        hesk_dbQuery($sql);
    }
}


if ($hesk_settings['debug_mode']) {
    $debug_msg = "Finished Calendar Reminders. {$successful_emails} reminder e-mails sent. {$failed_emails} emails failed to send.\n";
    echo $debug_msg;
    mfh_log_debug($LOCATION, $debug_msg, 'CRON');
}

// Overdue tickets
if ($hesk_settings['debug_mode']) {
    echo "Starting Overdue Tickets...\n";
}

$sql = "SELECT `ticket`.`id` AS `id`, `ticket`.`trackid` AS `trackid`, `ticket`.`name` AS `name`, `ticket`.`subject` AS `subject`,
    `ticket`.`message` AS `message`, `ticket`.`category` AS `category`, `ticket`.`priority` AS `priority`,
    `ticket`.`owner` AS `owner`, `ticket`.`status` AS `status`, `ticket`.`email` AS `email`, `ticket`.`dt` AS `dt`,
    `ticket`.`lastchange` AS `lastchange`, `ticket`.`due_date` AS `due_date`, `user`.`language` AS `user_language`, `user`.`email` AS `user_email`,
    `ticket`.`custom1` AS `custom1`, `ticket`.`custom2` AS `custom2`, `ticket`.`custom3` AS `custom3`, `ticket`.`custom4` AS `custom4`,
    `ticket`.`custom5` AS `custom5`, `ticket`.`custom6` AS `custom6`, `ticket`.`custom7` AS `custom7`, `ticket`.`custom8` AS `custom8`,
    `ticket`.`custom9` AS `custom9`, `ticket`.`custom10` AS `custom10`, `ticket`.`custom11` AS `custom11`, `ticket`.`custom12` AS `custom12`,
    `ticket`.`custom13` AS `custom13`, `ticket`.`custom14` AS `custom14`, `ticket`.`custom15` AS `custom15`, `ticket`.`custom16` AS `custom16`,
    `ticket`.`custom17` AS `custom17`, `ticket`.`custom18` AS `custom19`, `ticket`.`custom19` AS `custom19`, `ticket`.`custom20` AS `custom20`,
    `ticket`.`html` AS `html`
    FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` AS `ticket`
    INNER JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` AS `status`
        ON `ticket`.`status` = `status`.`ID`
        AND `status`.`IsClosed` = 0
    LEFT JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` AS `user`
        ON `ticket`.`owner` = `user`.`id`
    WHERE `due_date` IS NOT NULL
        AND `due_date` <= '{$current_date}'
        AND `overdue_email_sent` = '0'";

$successful_emails = 0;
$failed_emails = 0;
$rs = hesk_dbQuery($sql);

if (hesk_dbNumRows($rs) > 0 && !$included_email_functions) {
    require(HESK_PATH . 'inc/email_functions.inc.php');
    $included_email_functions = true;
}

$user_rs = hesk_dbQuery("SELECT `id`, `isadmin`, `categories`, `email`,
    CASE WHEN `heskprivileges` LIKE '%can_view_unassigned%' THEN 1 ELSE 0 END AS `can_view_unassigned`
    FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `notify_overdue_unassigned` = '1'
    AND (`heskprivileges` LIKE '%can_view_tickets%' OR `isadmin` = '1')");

$users = array();
while ($row = hesk_dbFetchAssoc($user_rs)) {
    $users[] = $row;
}

$tickets_to_flag = array();
while ($row = hesk_dbFetchAssoc($rs)) {
    if (mfh_sendOverdueTicketReminder($row, $users, $modsForHesk_settings)) {
        $tickets_to_flag[] = $row['id'];
        $successful_emails++;

        if ($hesk_settings['debug_mode']) {
            $debug_msg = "Sent overdue e-mail for ticket: {$row['trackid']} to user id: {$row['owner']}\n";
            mfh_log_debug($LOCATION, $debug_msg, 'CRON');
            echo $debug_msg;
        }
    } else {
        $failed_emails++;

        $warning_text = "Failed to send overdue reminder for ticket: {$row['trackid']} to user id: {$row['owner']}. This will be re-sent next time overdue tickets are processed.\n";\
        mfh_log_warning($LOCATION, $warning_text, 'CRON');
        echo $warning_text;
    }
}

if (count($tickets_to_flag) > 0) {
    foreach ($tickets_to_flag as $ticket_id) {
        $sql = "UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `overdue_email_sent` = '1' WHERE `id` = " . intval($ticket_id);
        hesk_dbQuery($sql);
    }
}

if ($hesk_settings['debug_mode']) {
    $debug_msg = "Finished Overdue Tickets. {$successful_emails} e-mails sent. {$failed_emails} emails failed to send.\n";
    echo $debug_msg;
    mfh_log_debug($LOCATION, $debug_msg, 'CRON');
}

$hesk_settings['timeformat'] = $old_timeformat_setting;