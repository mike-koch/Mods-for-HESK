#!/usr/bin/php -q
<?php

define('IN_SCRIPT',1);
define('HESK_PATH', dirname(dirname(__FILE__)) . '/');

#echo HESK_PATH."\n";

// Get required files and functions
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');

if (hesk_check_maintenance(false)) {
    // If Debug mode is ON show "Maintenance mode" message
    $message = $hesk_settings['debug_mode'] ? $hesklang['mm1'] : '';
    $message .= "\n";
    die($message);
}


// Get other required includes
require(HESK_PATH . 'inc/email_functions.inc.php');

hesk_load_internal_api_database_functions();
hesk_dbConnect();

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
$case_statement = "CASE
      WHEN `unit` = '0' THEN DATE_SUB(`event`.`start`, INTERVAL `reminder`.`amount` MINUTE)
      WHEN `unit` = '1' THEN DATE_SUB(`event`.`start`, INTERVAL `reminder`.`amount` HOUR)
      WHEN `unit` = '2' THEN DATE_SUB(`event`.`start`, INTERVAL `reminder`.`amount` DAY)
      WHEN `unit` = '3' THEN DATE_SUB(`event`.`start`, INTERVAL `reminder`.`amount` WEEK)
    END";
$sql = "SELECT `reminder`.`id` AS `reminder_id`, `reminder`.`user_id` AS `user_id`, `reminder`.`event_id` AS `event_id`,
    `event`.`name` AS `event_name`, `event`.`location` AS `event_location`, `event`.`comments` AS `event_comments`,
    `category`.`name` AS `event_category`, `event`.`start` AS `event_start`, `event`.`end` AS `event_end`,
    `event`.`all_day` AS `event_all_day`, `user`.`language` AS `user_language`, `user`.`email` AS `user_email`
    " . $case_statement . " AS `reminder_date`
    FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event_reminder` AS `reminder`
    INNER JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event` AS `event`
        ON `reminder`.`event_id` = `event`.`id`
    INNER JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` AS `category`
        ON `event`.`category` = `category`.`id`
    INNER JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` AS `user`
        ON `reminder`.`user_id` = `user`.`id`
    WHERE (" . $case_statement . ") <= NOW()
    AND `email_sent` = '0'";

$rs = hesk_dbQuery($sql);

$i = 0;
while ($row = hesk_dbFetchAssoc($rs)) {
    $i++;

    echo "Sent e-mail reminder for event: {$row['event_name']}\n";
}


if ($hesk_settings['debug_mode']) {
    echo "Finished Calendar Reminders. {$i} reminder e-mails sent. \n";
}