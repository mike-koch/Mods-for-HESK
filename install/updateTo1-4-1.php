<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');
hesk_dbConnect();
if (!isset($_GET['ar'])) {
	hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` ADD COLUMN `autorefresh` BIGINT NOT NULL DEFAULT 0 AFTER `replies`;");

	hesk_dbQuery("CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."denied_ips` (
	  `ID` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	  `RangeStart` VARCHAR(100) NOT NULL,
	  `RangeEnd` VARCHAR(100) NOT NULL)");
}
hesk_dbQuery("CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."denied_emails` (ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT, Email VARCHAR(100) NOT NULL);");
hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` ADD COLUMN `parent` MEDIUMINT(8) NULL AFTER `custom20`;");
?>

<h1>Update complete!</h1>
<p>Please delete the <b>install</b> folder for security reasons, and then proceed back to the <a href="../">Help Desk</a></p>