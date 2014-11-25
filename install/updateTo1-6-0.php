<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');
hesk_dbConnect();
hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` ADD COLUMN `notify_note_unassigned` ENUM('0', '1') NOT NULL DEFAULT '0'");
hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` ADD COLUMN `can_change_notification_settings` ENUM('0', '1') NOT NULL DEFAULT '1'");
?>

<h1>Installation / Update complete!</h1>
<p>Please delete the <b>install</b> folder for security reasons, and then proceed back to the <a href="../">Help Desk</a></p>