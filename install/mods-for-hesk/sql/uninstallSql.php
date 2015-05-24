<?php
require(HESK_PATH . 'hesk_settings.inc.php');

function executeQuery($sql) {
    global $hesk_last_query;
    global $hesk_db_link;
    if ( function_exists('mysqli_connect') ) {

        if ( ! $hesk_db_link && ! hesk_dbConnect())
        {
            return false;
        }

        $hesk_last_query = $sql;

        if ($res = @mysqli_query($hesk_db_link, $sql))
        {
            return $res;
        } else
        {
            print "Could not execute query: $sql. MySQL said: ".mysqli_error($hesk_db_link);
            http_response_code(500);
            die();
        }
    } else {
        if ( ! $hesk_db_link && ! hesk_dbConnect())
        {
            return false;
        }

        $hesk_last_query = $sql;

        if ($res = @mysql_query($sql, $hesk_db_link))
        {
            return $res;
        } else
        {
            print "Could not execute query: $sql. MySQL said: ".mysql_error();
            http_response_code(500);
            die();
        }
    }
}

function replaceStatusColumn() {
    global $hesk_settings;

    hesk_dbConnect();

    executeQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` ADD COLUMN `status_int` ENUM('0','1','2','3','4','5') NOT NULL AFTER `status`;");
    $ticketsRS = executeQuery("SELECT `id`, `status` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets`;");
    while ($currentResult = $ticketsRS->fetch_assoc())
    {

        executeQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `status_int` = '".intval($currentResult['status'])."' WHERE `id` = ".$currentResult['id']);
    }
    executeQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` DROP COLUMN `status`");
    executeQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` CHANGE COLUMN `status_int` `status` ENUM('0','1','2','3','4','5') NOT NULL");
    executeQuery("DROP TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses`");
}

function removeAutorefresh() {
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` DROP COLUMN `autorefresh`");
}

function removeParentColumn() {
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` DROP COLUMN `parent`");
}

function removeHelpDeskSettingsPermission() {
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` DROP COLUMN `can_manage_settings`");
}

function removeActiveColumn() {
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` DROP COLUMN `active`");
}

function removeNotifyNoteUnassigned() {
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` DROP COLUMN `notify_note_unassigned`");
}

function removeUserManageOwnNotificationSettingsColumn() {
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` DROP COLUMN `can_change_notification_settings`");
}

function removeSettingsTable() {
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("DROP TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."settings`");
}

function removeVerifiedEmailsTable() {
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("DROP TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."verified_emails`");
}

function removePendingVerificationEmailsTable() {
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("DROP TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."pending_verification_emails`");
}

function removeTicketsPendingVerificationTable() {
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("DROP TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."stage_tickets`");
}

function removeServiceMessageCustomIcon() {
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."service_messages` DROP COLUMN `icon`");
}

function removeTicketLocation() {
    global $hesk_settings;

    hesk_dbConnect();
    executeQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` DROP COLUMN `latitude`");
    executeQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` DROP COLUMN `longitude`");
    executeQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."stage_tickets` DROP COLUMN `latitude`");
    executeQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."stage_tickets` DROP COLUMN `longitude`");
}

function executeMiscellaneousSql() {
    global $hesk_settings;

    hesk_dbConnect();
    // These queries are ran in case someone used an unfortunate installation they may have not properly cleaned up tables
    executeQuery('DROP TABLE IF EXISTS `'.hesk_dbEscape($hesk_settings['db_pfix']).'denied_ips`');
    executeQuery('DROP TABLE IF EXISTS `'.hesk_dbEscape($hesk_settings['db_pfix']).'denied_emails`');
}