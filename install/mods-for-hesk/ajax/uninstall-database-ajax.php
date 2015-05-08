<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../../../');
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
hesk_load_database_functions();
require('../sql/uninstallSql.php');

$task = $_POST['task'];
if ($task == 'status-change') {
    replaceStatusColumn();
} elseif ($task == 'autorefresh') {
    removeAutorefresh();
} elseif ($task == 'parent-child') {
    removeParentColumn();
} elseif ($task == 'settings-access') {
    removeHelpDeskSettingsPermission();
} elseif ($task == 'activate-user') {
    removeActiveColumn();
} elseif ($task == 'notify-note-unassigned') {
    removeNotifyNoteUnassigned();
} elseif ($task == 'user-manage-notification-settings') {
    removeUserManageOwnNotificationSettingsColumn();
} elseif ($task == 'settings-table') {
    removeSettingsTable();
} elseif ($task == 'verified-emails-table') {
    removeVerifiedEmailsTable();
} elseif ($task == 'pending-verification-emails-table') {
    removePendingVerificationEmailsTable();
} elseif ($task == 'pending-verification-tickets-table') {
    removeTicketsPendingVerificationTable();
} elseif ($task == 'service-message-icon') {
    removeServiceMessageCustomIcon();
} elseif ($task == 'miscellaneous') {
    executeMiscellaneousSql();
} else {
    http_response_code(400);
}
return;