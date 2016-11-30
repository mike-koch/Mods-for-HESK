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
        }
    }
}

function replaceStatusColumn()
{
    global $hesk_settings;

    hesk_dbConnect();

    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `status_int` ENUM('0','1','2','3','4','5') NOT NULL AFTER `status`;");
    $ticketsRS = executeQuery("SELECT `id`, `status` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets`;");
    while ($currentResult = hesk_dbFetchAssoc($ticketsRS)) {

        executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `status_int` = '" . intval($currentResult['status']) . "' WHERE `id` = " . $currentResult['id']);
    }
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `status`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` CHANGE COLUMN `status_int` `status` ENUM('0','1','2','3','4','5') NOT NULL");
    executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses`");
}

function removeOtherColumns()
{
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `autorefresh`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `parent`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `can_manage_settings`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `active`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `notify_note_unassigned`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `can_change_notification_settings`");
    executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings`");
    executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "verified_emails`");
    executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "pending_verification_emails`");
    executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages` DROP COLUMN `icon`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `latitude`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `longitude`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` DROP COLUMN `latitude`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` DROP COLUMN `longitude`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` DROP COLUMN `manager`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `permission_template`");
    executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "permission_templates`");
    executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections`");
    executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "text_to_status_xref`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` DROP COLUMN `download_count`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_attachments` DROP COLUMN `download_count`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `html`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` DROP COLUMN `html`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` DROP COLUMN `html`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `user_agent`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `screen_resolution_width`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `screen_resolution_height`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` DROP COLUMN `user_agent`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` DROP COLUMN `screen_resolution_width`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` DROP COLUMN `screen_resolution_height`");
    executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "logging`");
    executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "user_api_tokens`");
    executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "temp_attachment`");
    executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event`");
    executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event_reminder`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `due_date`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` DROP COLUMN `overdue_email_sent`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` DROP COLUMN `color`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` DROP COLUMN `usage`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `notify_overdue_unassigned`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `default_calendar_view`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` DROP COLUMN `due_date`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` DROP COLUMN `overdue_email_sent`");


    // These queries are ran in case someone used an unfortunate installation they may have not properly cleaned up tables
    executeQuery('DROP TABLE `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'denied_ips`');
    executeQuery('DROP TABLE `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'denied_emails`');
}