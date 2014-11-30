<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');
?>
<html>
    <head>
        <title>Mods For HESK 1.6.0 Install / Upgrade</title>
    </head>
    <body>
        <h1>Mods for HESK 1.6.0 Install / Upgrade</h1>
        <h3>Select your current Mods for HESK version number to upgrade.</h3>
        <ul style="list-style-type: none">
            <li><a href="updateTo1-6-0.php">v1.5.0</a></li>
            <li><a href="updateTo1-5-0.php">v1.4.1</a></li>
            <li><a href="updateTo1-4-1.php?ar=true">v1.4.0</a></li>
            <li><a href="updateTo1-4-1.php">v1.2.4 or v1.3.0</a></li>
            <li><a href="freshInstall.php">No previous version</a> (make sure you have installed/updated HESK first; otherwise installation will <b>fail</b>!)</li>
        </ul>
        <p>Please verify the database information below.  Additionally, ensure that the database user has CREATE and ALTER permissions.</p>
        <p><b>Database Host: </b> <?php echo $hesk_settings['db_host']; ?></p>
        <p><b>Database Name: </b><?php echo $hesk_settings['db_name']; ?></p>
        <p><b>Database User: </b><?php echo $hesk_settings['db_user']; ?></p>
        <p><b>Database Password: </b><?php echo $hesk_settings['db_pass']; ?></p>
        <p><b>Database Prefix: </b><?php echo $hesk_settings['db_pfix']; ?></p>
        
        <p>By proceeding, you agree to the terms of the <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank">Creative Commons Attribution-ShareAlike 4.0 International License.</a></p>
    </body>
</html>
