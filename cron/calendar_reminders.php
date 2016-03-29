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
// The reminder time is calculated by
$sql = "SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event_reminder`
    WHERE `reminder_time` <= NOW()
    AND `email_sent` = '0'";

$rs = hesk_dbQuery($sql);

$i = 0;
while ($row = hesk_dbFetchAssoc($rs)) {

}


if ($hesk_settings['debug_mode']) {
    echo "Finished Calendar Reminders. {$i} reminder e-mails sent. \n";
}