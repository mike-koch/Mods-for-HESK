<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');

hesk_dbConnect();
?>
<html>
<head>
    <title>Mods For HESK <?php echo MODS_FOR_HESK_NEW_VERSION; ?> Install / Upgrade</title>
    <link href="../../hesk_style.css?<?php echo HESK_NEW_VERSION; ?>" type="text/css" rel="stylesheet"/>
    <link href="<?php echo HESK_PATH; ?>css/bootstrap.css?v=<?php echo $hesk_settings['hesk_version']; ?>"
          type="text/css" rel="stylesheet"/>
    <link href="<?php echo HESK_PATH; ?>css/bootstrap-theme.css?v=<?php echo $hesk_settings['hesk_version']; ?>"
          type="text/css" rel="stylesheet"/>
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="../../css/hesk_newStyle.css" type="text/css" rel="stylesheet"/>
    <script src="<?php echo HESK_PATH; ?>js/jquery-1.10.2.min.js"></script>
    <script language="Javascript" type="text/javascript" src="<?php echo HESK_PATH; ?>js/bootstrap.min.js"></script>
    <script language="Javascript" type="text/javascript"
            src="<?php echo HESK_PATH; ?>js/modsForHesk-javascript.js"></script>
    <script language="JavaScript" type="text/javascript"
            src="<?php echo HESK_PATH; ?>install/mods-for-hesk/js/ui-scripts.js"></script>
    <script language="JavaScript" type="text/javascript"
            src="<?php echo HESK_PATH; ?>install/mods-for-hesk/js/version-scripts.js"></script>
    <script language="JavaScript" type="text/javascript"
            src="<?php echo HESK_PATH; ?>js/bootstrap-datepicker.js"></script>
</head>
<body>
<div class="headersm">Mods for HESK <?php echo MODS_FOR_HESK_NEW_VERSION; ?> Install / Upgrade</div>
<div class="container">
    <div class="page-header">
        <h1>Mods for HESK <?php echo MODS_FOR_HESK_NEW_VERSION; ?> Install / Upgrade</h1>
    </div>
    <?php
    $allowInstallation = true;
    ?>
    <div class="row">
        <div class="col-md-5 col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">Database/File Requirements</div>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th colspan="2">Database Information / File Permissions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Database Host:</td>
                        <td><?php echo $hesk_settings['db_host']; ?></td>
                    </tr>
                    <tr>
                        <td>Database Name:</td>
                        <td><?php echo $hesk_settings['db_name']; ?></td>
                    </tr>
                    <tr>
                        <td>Database User:</td>
                        <td><?php echo $hesk_settings['db_user']; ?></td>
                    </tr>
                    <tr>
                        <td>Database Password:</td>
                        <td><?php echo $hesk_settings['db_pass']; ?></td>
                    </tr>
                    <tr>
                        <td>Database Prefix:</td>
                        <td><?php echo $hesk_settings['db_pfix']; ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        $tableSql = hesk_dbQuery('SHOW TABLES LIKE \'' . hesk_dbEscape($hesk_settings['db_pfix']) . 'settings\'');
        $version = NULL;
        $disableAllExcept = NULL;
        if (hesk_dbNumRows($tableSql) > 0) {
            $versionRS = hesk_dbQuery('SELECT `Value` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'settings` WHERE `Key` = \'modsForHeskVersion\'');
            $versionArray = hesk_dbFetchAssoc($versionRS);
            $version = $versionArray['Value'];
        }
        ?>
        <div class="col-md-7 col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">Install / Upgrade</div>
                <div class="panel-body">
                    <?php if ($allowInstallation) {
                        $prereqDiv = 'none';
                        $installDiv = 'block';
                    } else {
                        $prereqDiv = 'block';
                        $installDiv = 'none';
                    }
                    ?>
                    <div class="prereqsFailedDiv" style="display:<?php echo $prereqDiv; ?>">
                        <div class="alert alert-danger">
                            <p><i class="fa fa-times-circle"></i> You cannot install/upgrade Mods for HESK until the
                                requirements on the left have been met.</p>

                            <p><a href="modsForHesk.php" class="btn btn-default">Refresh</a></p>
                        </div>
                    </div>
                    <div class="installDiv" style="display:<?php echo $installDiv; ?>">
                        <div class="alert alert-info">
                            <p><i class="fa fa-exclamation-triangle"></i> Make sure that you have updated / installed
                                HESK first; otherwise installation will <b>fail</b>!</p>
                        </div>
                        <p>Select your current Mods for HESK version and click "Upgrade" to upgrade your installation.
                            If you have never installed Mods for HESK before, click "No previous installation".</p>
                        <hr>
                        <div class="row">
                            <form class="form-horizontal">
                                <input type="hidden" name="current-version-hidden" value="<?php echo $version != NULL && $version != MODS_FOR_HESK_NEW_VERSION ? $version : 0; ?>">
                                <label for="current-version" class="col-sm-3 control-label">Current Version</label>
                                <div class="col-md-9">
                                    <div class="col-md-8">
                                        <select name="current-version" class="form-control">
                                            <option disabled>Select One, or "No Previous Installation" Below</option>
                                            <optgroup label="Mods for HESK 3">
                                                <option value="38">3.0.4</option>
                                                <option value="37">3.0.3</option>
                                                <option value="36">3.0.2</option>
                                                <option value="35">3.0.1</option>
                                                <option value="34">3.0.0</option>
                                                <option value="33">3.0.0 RC 1 [Prerelease Build]</option>
                                                <option value="32">3.0.0 beta 1 [Prerelease Build]</option>
                                            </optgroup>
                                            <optgroup label="Mods for HESK 2">
                                                <option value="31">2.6.4</option>
                                                <option value="30">2.6.3</option>
                                                <option value="29">2.6.2</option>
                                                <option value="28">2.6.1</option>
                                                <option value="27">2.6.0</option>
                                                <option value="26">2.5.5</option>
                                                <option value="25">2.5.4</option>
                                                <option value="24">2.5.3</option>
                                                <option value="23">2.5.2</option>
                                                <option value="22">2.5.1</option>
                                                <option value="21">2.5.0</option>
                                                <option value="20">2.4.2</option>
                                                <option value="19">2.4.1</option>
                                                <option value="18">2.4.0</option>
                                                <option value="17">2.3.2</option>
                                                <option value="16">2.3.1</option>
                                                <option value="15">2.3.0</option>
                                                <option value="14">2.2.1</option>
                                                <option value="13">2.2.0</option>
                                                <option value="12">2.1.1</option>
                                                <option value="11">2.1.0</option>
                                                <option value="10">2.0.1</option>
                                                <option value="9">2.0.0</option>
                                            </optgroup>
                                            <optgroup label="Mods for HESK 1">
                                                <option value="8">1.7.0</option>
                                                <option value="7">1.6.1</option>
                                                <option value="6">1.6.0</option>
                                                <option value="5">1.5.0</option>
                                                <option value="4">1.5.1</option>
                                                <option value="3">1.4.0</option>
                                                <option value="2">1.3.0</option>
                                                <option value="1">1.2.4</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="#" class="btn btn-success" id="upgrade-link">Upgrade</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="row" style="margin-top: 20px">
                            <div class="col-sm-12">
                                <div class="btn-group-vertical" role="group" style="width: 100%">
                                    <a class="btn btn-primary btn-block disablable" href="installModsForHesk.php?v=0">No
                                        previous installation</a>
                                    <button type="button" class="btn btn-danger btn-block" data-toggle="modal"
                                            data-target="#uninstallModal"><i class="fa fa-trash"></i> Uninstall Mods for
                                        HESK
                                    </button>
                                    <a class="btn btn-default btn-block" href="database-validation.php">
                                        <i class="fa fa-check-circle"></i> Validate Database
                                    </a>
                                </div>
                            </div>
                        </div>
                        <br>

                        <div class="row">
                            <div class="col-sm-12">
                                By proceeding, you agree to the terms of the <a
                                    href="http://opensource.org/licenses/MIT" target="_blank">MIT License.</a>
                            </div>
                        </div>
                    </div>
                </div>
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
                <a class="btn btn-success" href="uninstallModsForHesk.php"><i class="fa fa-check"></i> Yes</a>
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> No
                </button>
            </div>
        </div>
    </div>
</div>
<?php
if ($disableAllExcept !== NULL) {
    echo '<script>disableAllDisablable(\'' . $disableAllExcept . '\')</script>';
}
?>
<script>
    var dropdown = $('select[name="current-version"]');
    dropdown.change(updateLink);

    var currentVersion = $('input[name="current-version-hidden"]').val();
    dropdown.find('option').each(function() {
        var $that = $(this);

        if ($that.text() == currentVersion) {
            $that.attr('selected', true);
            updateLink();
        }
    })
</script>
</body>
</html>
