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
$startingMigrationNumber = -1;
if (hesk_dbNumRows($tableSql) > 0) {
    // They have installed at LEAST to version 1.6.0. Just pull the version number OR migration number
    $migrationNumberSql = hesk_dbQuery("SELECT `Value` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` WHERE `Key` = 'lastMigration'");
    if ($migrationRow = hesk_dbFetchAssoc($migrationNumberSql)) {
        $startingMigrationNumber = $migrationRow['Value'];
    } else {
        $versionSql = hesk_dbQuery("SELECT `Value` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` WHERE `Key` = 'modsForHeskVersion'");
        $versionRow = hesk_dbFetchAssoc($versionSql);

        //TODO Actually map this
        $startingMigrationNumber = $versionRow['Value'];
    }
} else {
    // migration # => sql for checking
    $versionChecks = array(
        // 1.5.0 -> users.active
        1 => "SHOW COLUMNS FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` LIKE 'active'",
        // 1.4.1 -> denied_emails
        2 => "SHOW TABLES LIKE '" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_emails'",
        // 1.4.0 -> denied ips
        3 => "SHOW TABLES LIKE '" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_ips'",
        // Pre-1.4.0 but still something -> statuses
        4 => "SHOW TABLES LIKE '" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses'"
    );

    foreach ($versionChecks as $migrationNumber => $sql) {
        $rs = hesk_dbQuery($sql);
        if (hesk_dbNumRows($rs) > 0) {
            $startingMigrationNumber = $migrationNumber;
            break;
        }
    }
}
?>
<html>
<head>
    <title>Mods for HESK <?php echo MODS_FOR_HESK_NEW_VERSION; ?> Install / Upgrade</title>
    <link href="<?php echo HESK_PATH; ?>css/bootstrap.css?v=<?php echo $hesk_settings['hesk_version']; ?>"
          type="text/css" rel="stylesheet"/>
    <link href="<?php echo HESK_PATH; ?>css/bootstrap-theme.ccss?v=<?php echo HESK_NEW_VERSION; ?>" type="text/css" rel="stylesheet" />
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
                <span id="header-text">Thanks for choosing Mods for HESK.</span>
            </div>
            <?php // BEGIN INSTALL SCREENS ?>
            <div data-step="intro" class="login-box-msg">
                <h4>Let's get started.</h4>
                <p>By continuing, I agree to the terms of the
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
            <div data-step="install-or-update" style="display: none">
                <div class="progress">
                    <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"
                         style="min-width: 2em; width: 100%">
                        <span class="sr-only">100% Complete</span>
                        100%
                    </div>
                </div>
            </div>
            <?php // END INSTALL SCREENS ?>
            <div id="buttons">
                <div class="btn btn-primary" id="back-button" style="display: none;"><i class="fa fa-chevron-left"></i>&nbsp;&nbsp;&nbsp;Back</div>
                <div class="btn btn-default dropdown-toggle" id="tools-button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Tools <span class="caret"></span>
                </div>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo HESK_PATH; ?>install/database-validation.php"><i class="fa fa-check-circle"></i> Database Validator</a></li>
                    <li><a href="#" data-toggle="modal"
                           data-target="#uninstallModal"><i class="fa fa-trash"></i> Uninstall Mods for HESK</a></li>
                </ul>
                <div class="btn btn-primary pull-right" id="next-button">Next&nbsp;&nbsp;&nbsp;<i class="fa fa-chevron-right"></i></div>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="uninstallModal" tabindex="-1" role="dialog" aria-labelledby="uninstallModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="uninstallModalTitle"><i class="fa fa-trash"></i> Uninstall Mods for HESK
                </h4>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to uninstall Mods for HESK?</p>
            </div>
            <div class="modal-footer">
                <a class="btn btn-success" href="<?php echo HESK_PATH; ?>install/uninstallModsForHesk.php"><i class="fa fa-check"></i> Yes</a>
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> No
                </button>
            </div>
        </div>
    </div>
</div>
</body>
</html>