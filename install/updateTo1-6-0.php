<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');
hesk_dbConnect();
hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` ADD COLUMN `notify_note_unassigned` ENUM('0', '1') NOT NULL DEFAULT '0'");
hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` ADD COLUMN `can_change_notification_settings` ENUM('0', '1') NOT NULL DEFAULT '1'");
hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."notes` ADD COLUMN `edit_date` DATETIME NULL");
hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."notes` ADD COLUMN `number_of_edits` INT NOT NULL DEFAULT 0");
hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` ADD COLUMN `note_id` INT NULL AFTER `ticket_id`");
hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` MODIFY COLUMN `ticket_id` VARCHAR(13) NULL");
hesk_dbQuery("CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."settings` (`Key` NVARCHAR(200) NOT NULL, `Value` NVARCHAR(200) NOT NULL)");
hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."settings` (`Key`, `Value`) VALUES ('modsForHeskVersion', '1.6.0')");
?>
<script type="text/javascript">
    window.location.href = "updateTo1-6-1.php"
</script>
<p>Redirecting to next install script. <a href="updateTo1-6-1.php">Click here if you are not redirected automatically.</a></p>