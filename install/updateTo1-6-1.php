<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');
hesk_dbConnect();
hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."settings` SET `Value` = '1.6.1' WHERE `Key` = 'modsForHeskVersion'");
?>
<script type="text/javascript">
    window.location.href = "updateTo1-7-0.php"
</script>
<p>Redirecting to next install script. <a href="updateTo1-7-0.php">Click here if you are not redirected automatically.</a></p>