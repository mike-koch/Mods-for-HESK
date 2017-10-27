<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');

hesk_dbConnect();

/*
We have four possible installation scenarios:

1. Fresh install - the user has never installed Mods for HESK before. Simply start at migration #0.
2. Installed a really old version - we don't have a previous version to start from.
3. Installed a recent version, but before migrations began - just pull the version # and use the dictionary below.
4. Migration number present in the settings table. Take that number and run with it.
 */

$tableSql = hesk_dbQuery("SHOW TABLES LIKE '" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings'");
$startingMigrationNumber = 1;
if (hesk_dbNumRows($tableSql) > 0) {
    // They have installed at LEAST to version 1.6.0. Just pull the version number OR migration number
    $migrationNumberSql = hesk_dbQuery("SELECT `Value` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` WHERE `Key` = 'migrationNumber'");
    if ($migrationRow = hesk_dbFetchAssoc($migrationNumberSql)) {
        $startingMigrationNumber = intval($migrationRow['Value']) + 1;
    } else {
        $versionSql = hesk_dbQuery("SELECT `Value` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` WHERE `Key` = 'modsForHeskVersion'");
        $versionRow = hesk_dbFetchAssoc($versionSql);

        $migration_map = array(
            // Pre-1.4.0 to 1.5.0 did not have a settings table
            '1.6.0' => 16, '1.6.1' => 17, '1.7.0' => 21, '2.0.0' => 27, '2.0.1' => 28, '2.1.0' => 29, '2.1.1' => 31,
            '2.2.0' => 34, '2.2.1' => 35, '2.3.0' => 41, '2.3.1' => 42, '2.3.2' => 43, '2.4.0' => 48, '2.4.1' => 49,
            '2.4.2' => 50, '2.5.0' => 54, '2.5.1' => 55, '2.5.2' => 56, '2.5.3' => 57, '2.5.4' => 58, '2.5.5' => 59,
            '2.6.0' => 66, '2.6.1' => 67, '2.6.2' => 69, '2.6.3' => 70, '2.6.4' => 71, '3.0.0' => 75, '3.0.1' => 76,
            '3.0.2' => 78, '3.0.3' => 79, '3.0.4' => 80, '3.0.5' => 81, '3.0.6' => 82, '3.0.7' => 83, '3.1.0' => 90,
            '3.1.1' => 91
        );
        $startingMigrationNumber = $migration_map[$versionRow['Value']];
    }
} else {
    // migration # => sql for checking
    $versionChecks = array(
        // 1.5.0 -> users.active
        9 => "SHOW COLUMNS FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` LIKE 'active'",
        // 1.4.1 -> denied_emails
        6 => "SHOW TABLES LIKE '" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_emails'",
        // 1.4.0 -> denied ips
        4 => "SHOW TABLES LIKE '" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_ips'",
        // Pre-1.4.0 but still something -> statuses
        2 => "SHOW TABLES LIKE '" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses'"
    );

    foreach ($versionChecks as $migrationNumber => $sql) {
        $rs = hesk_dbQuery($sql);
        if (hesk_dbNumRows($rs) > 0) {
            $startingMigrationNumber = $migrationNumber;
            break;
        }
    }
}

$installMismatch = false;
if (HESK_NEW_VERSION != $hesk_settings['hesk_version']) {
    $installMismatch = true;
}
?>
<html>
<head>
    <title>Mods for HESK <?php echo MODS_FOR_HESK_NEW_VERSION; ?> Install / Upgrade</title>
    <link href="<?php echo HESK_PATH; ?>css/bootstrap.css?v=<?php echo $hesk_settings['hesk_version']; ?>"
          type="text/css" rel="stylesheet"/>
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="<?php echo HESK_PATH; ?>css/AdminLTE.min.css" type="text/css" rel="stylesheet">
    <link href="<?php echo HESK_PATH; ?>css/mods-for-hesk-new.css" type="text/css" rel="stylesheet">
    <link href="<?php echo HESK_PATH; ?>css/colors.css" type="text/css" rel="stylesheet">
    <script src="<?php echo HESK_PATH; ?>js/jquery-1.10.2.min.js"></script>
    <script language="Javascript" type="text/javascript" src="<?php echo HESK_PATH; ?>js/bootstrap.min.js"></script>
    <script language="JavaScript" type="text/javascript" src="<?php echo HESK_PATH; ?>install/js/install-script.js"></script>
    <style>
        body, .login-box-background {
            background: url('<?php echo HESK_PATH; ?>install/background.jpg') no-repeat center center fixed;
            background-size: cover;
        }
    </style>
</head>
<body class="fixed" style="min-height: initial;">
<div class="login-box installer-login-box">
    <div class="login-box-container">
        <div class="login-box-background"></div>
        <div class="login-box-body">
            <div class="login-logo">
                <img src="<?php echo HESK_PATH; ?>install/logo.png" alt="Mods for HESK logo"><br>
                <span id="header-text">
                    <?php if ($installMismatch): ?>
                    HESK Version Mismatch
                    <?php else: ?>
                    Thanks for choosing Mods for HESK.
                    <?php endif; ?>
                </span>
            </div>
            <?php if ($installMismatch): ?>
            <div class="text-center">
                <h4>You need to be running HESK 2.7.3 to install Mods for HESK. You currently have HESK <?php echo $hesk_settings['hesk_version']; ?>.</h4>
            </div>
            <?php else: // BEGIN INSTALL SCREENS ?>
            <div data-step="intro" class="login-box-msg">
                <h4>Let's get started.</h4>
                <p>By continuing, you agree to the terms of the
                    <a href="http://opensource.org/licenses/MIT" target="_blank">MIT License</a>.</p>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="usage-stats" checked>
                        Submit anonymous usage statistics
                    </label>
                </div>
            </div>
            <div data-step="db-confirm" style="display: none">
                <table class="table table-striped" style="background: #fff">
                    <thead>
                    <tr>
                        <th colspan="4">Database Information / File Permissions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Database Host:</td>
                        <td><?php echo $hesk_settings['db_host']; ?></td>
                        <td>Database Name:</td>
                        <td><?php echo $hesk_settings['db_name']; ?></td>
                    </tr>
                    <tr>
                        <td>Database User:</td>
                        <td><?php echo $hesk_settings['db_user']; ?></td>
                        <td>Database Password:</td>
                        <td><?php echo $hesk_settings['db_pass']; ?></td>
                    </tr>
                    <tr>
                        <td>Database Prefix:</td>
                        <td><?php echo $hesk_settings['db_pfix']; ?></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div data-step="install-or-update" class="text-center" style="display: none">
                <div id="spinner">
                    <i class="fa fa-spin fa-spinner fa-4x"></i>
                    <h4>Initializing...</h4>
                </div>
                <div id="progress-bar" class="progress" style="display: none">
                    <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"
                         style="width: 0">
                    </div>
                </div>
                <br>
                <div id="error-block" class="well" style="display: none; font-family: 'Courier New', Courier, monospace">
                </div>
                <input type="hidden" name="starting-migration-number" value="<?php echo $startingMigrationNumber; ?>">
            </div>
            <div data-step="complete" class="text-center" style="display: none">
                <i class="fa fa-check-circle fa-4x" style="color: green"></i><br><br>
                <h4>Make sure to delete your <code>/install</code> folder and then proceed to your
                    <a href="<?php echo $hesk_settings['hesk_url']; ?>">help desk</a>.</h4>
                <br>
            </div>
            <?php // END INSTALL SCREENS ?>
            <div id="buttons">
                <div class="btn btn-primary" id="back-button" style="display: none;"><i class="fa fa-chevron-left"></i>&nbsp;&nbsp;&nbsp;Back</div>
                <div class="btn btn-default dropdown-toggle" id="tools-button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Tools <span class="caret"></span>
                </div>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo HESK_PATH; ?>install/database-validation.php"><i class="fa fa-check-circle"></i> Database Validator</a></li>
                    <li><a href="<?php echo HESK_PATH; ?>install/uninstall.php"><i class="fa fa-trash"></i> Uninstall Mods for HESK</a></li>
                </ul>
                <div class="btn btn-primary pull-right" id="next-button">Next&nbsp;&nbsp;&nbsp;<i class="fa fa-chevron-right"></i></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<p id="hesk-path" style="display: none"><?php echo HESK_PATH; ?></p>
</body>
</html>