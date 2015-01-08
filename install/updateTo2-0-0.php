<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');

$updateSuccess = true;

hesk_dbConnect();
hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` DROP COLUMN `note_id`");


if ($updateSuccess) {
?>

<h1>Installation / Update complete!</h1>
<p>Please delete the <b>install</b> folder for security reasons, and then proceed back to the <a href="../">Help Desk</a></p>

<?php } ?>