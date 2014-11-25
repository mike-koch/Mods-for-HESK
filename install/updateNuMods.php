<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');
$showInstructions = 'block';
?>
<html>
    <head>
        <title>NuMods 1.5.0 Install / Upgrade</title>
    </head>
    <body>
        <div style="display: <?php echo $showInstructions; ?>">
        <h1>Update NuMods from v1.4.1 to v1.5.0</h1>
        <p><a href="updateTo1-5-0.php">Update here</a>. <b>Do not use the installation below!</b> </p>
        <h1>Update NuMods from v1.2.4 - v1.3.0 to v1.5.0</h1>
		<p>If you attempted the v1.4.0 installation and it failed, use <a href="updateTo1-4-1.php?ar=true">this update link</a>. Do not use the link below!</p>
        <p><a href="updateTo1-4-1.php">Update here</a>. <b>Do not use the installation below!</b></p>
        <h1>Install NuMods v1.5.0 <b>for the first time</b></h1>
        <h4><i>If you have not yet installed/updated HESK, please do so first before continuing; otherwise installation will <b>fail</b>!</i></h4>
        <br/>
        <p>Please verify the database information below.  Additionally, ensure that the database user has CREATE and ALTER permissions.</p>
        <p><b>Database Host: </b> <?php echo $hesk_settings['db_host']; ?></p>
        <p><b>Database Name: </b><?php echo $hesk_settings['db_name']; ?></p>
        <p><b>Database User: </b><?php echo $hesk_settings['db_user']; ?></p>
        <p><b>Database Password: </b><?php echo $hesk_settings['db_pass']; ?></p>
        <p><b>Database Prefix: </b><?php echo $hesk_settings['db_pfix']; ?></p>
        <a href="freshInstall.php">Proceed with installation</a>
        <p>By proceeding, you agree to the terms of the <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank">Creative Commons Attribution-ShareAlike 4.0 International License.</a></p>
        </div>
    </body>
</html>
