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
$sql = "SELECT `reminder`.`id`, `reminder`.`user_id`, `reminder`.`event_id`,
    " . $case_statement . " AS `reminder_date`
    FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event_reminder` AS `reminder`
    INNER JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event` AS `event`
        ON `reminder`.`event_id` = `event`.`id`
    WHERE (" . $case_statement . ") <= NOW()
    AND `email_sent` = '0'";

$rs = hesk_dbQuery($sql);

$i = 0;
while ($row = hesk_dbFetchAssoc($rs)) {

}


if ($hesk_settings['debug_mode']) {
    echo "Finished Calendar Reminders. {$i} reminder e-mails sent. \n";
}