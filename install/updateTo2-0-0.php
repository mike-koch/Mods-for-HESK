<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');

$updateSuccess = true;

hesk_dbConnect();
hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` DROP COLUMN `note_id`");
hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."notes` DROP COLUMN `edit_date`");
hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."notes` DROP COLUMN `number_of_edits`");
hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` DROP COLUMN `default_notify_customer_email`");

//TODO Migrate Mods for HESK Banned IPs / Emails to HESK 2.6.0's tables. Luckily the table names are different, so there won't be a problem when HESK tries to install.

hesk_dbQuery("DROP TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."denied_ips`");
hesk_dbQuery("DROP TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."denied_emails`");

if ($updateSuccess) {
?>

<h1>Installation / Update complete!</h1>
<p>Please delete the <b>install</b> folder for security reasons, and then proceed back to the <a href="../">Help Desk</a></p>

<?php } ?>