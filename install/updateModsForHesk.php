<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');
?>
<html>
    <head>
        <title>Mods For HESK 2.0.0 Install / Upgrade</title>
        <link href="../hesk_style.css?<?php echo HESK_NEW_VERSION; ?>" type="text/css" rel="stylesheet" />
        <link href="<?php echo HESK_PATH; ?>css/bootstrap.css?v=<?php echo $hesk_settings['hesk_version']; ?>" type="text/css" rel="stylesheet" />
        <link href="<?php echo HESK_PATH; ?>css/bootstrap-theme.css?v=<?php echo $hesk_settings['hesk_version']; ?>" type="text/css" rel="stylesheet" />
        <link href="//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
        <link href="../css/hesk_newStyle.php" type="text/css" rel="stylesheet" />
    </head>
    <body>
        <div class="headersm">Mods for HESK 2.0.0 Install / Upgrade</div>
        <div class="container">
            <div class="page-header">
                <h1>Mods for HESK 2.0.0 Install / Upgrade</h1>
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
                            <tr>
                                <td>CREATE, ALTER, DROP Permissions:</td>
                                <td class="warning"><i class="fa fa-exclamation-triangle"></i> Please check before continuing!*</td>
                            </tr>
                            <tr>
                                <td>
                                    modsForHesk_settings.inc.php
                                </td>
                                <?php
                                $fileperm = substr(sprintf('%o', fileperms(HESK_PATH.'modsForHesk_settings.inc.php')), -4);
                                $class =  (intval($fileperm) < 666) ? 'class="danger"' : 'class="success"';
                                ?>
                                <td <?php echo $class; ?>>
                                    <?php if ($class == 'class="success"') {
                                        echo '<i class="fa fa-check-circle"></i> Success';
                                    } else {
                                        echo '<i class="fa fa-times-circle"></i> CHMOD to 0666, yours is '.$fileperm;
                                        $allowInstallation = false;
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    * Mods for HESK is unable to check database permissions automatically.
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
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
                                    <p><i class="fa fa-times-circle"></i> You cannot install/upgrade Mods for HESK until the requirements on the left have been met.</p>
                                    <p><a href="updateModsForHesk.php" class="btn btn-default">Refresh</a></p>
                                </div>
                            </div>
                            <div class="installDiv"  style="display:<?php echo $installDiv; ?>">
                                <div class="alert alert-info">
                                    <p><i class="fa fa-exclamation-triangle"></i> Make sure that you have updated / installed HESK first; otherwise installation will <b>fail</b>!</p>
                                </div>
                                <p>What version of Mods for HESK do you currently have installed?</p>
                                <hr>
                                <div class="row">
                                    <div class="col-md-3 col-sm-12">
                                        <a class="btn btn-default btn-block" href="updateTo2-0-0.php">v1.7.0</a>
                                    </div>
                                    <div class="col-md-3 col-sm-12">
                                        <a class="btn btn-default btn-block" href="updateTo1-7-0.php">v1.6.1</a>
                                    </div>
                                    <div class="col-md-3 col-sm-12">
                                        <a class="btn btn-default btn-block" href="updateTo1-6-1.php">v1.6.0</a>
                                    </div>
                                    <div class="col-md-3 col-sm-12">
                                        <a class="btn btn-default btn-block" href="updateTo1-6-0.php">v1.5.0</a>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-3 col-sm-12">
                                        <a class="btn btn-default btn-block" href="updateTo1-5-0.php">v1.4.1</a>
                                    </div>
                                    <div class="col-md-3 col-sm-12">
                                        <a class="btn btn-default btn-block" href="updateTo1-4-1.php?ar=true">v1.4.0</a>
                                    </div>
                                    <div class="col-md-3 col-sm-12">
                                        <a class="btn btn-default btn-block" href="updateTo1-4-1.php">v1.3.0</a>
                                    </div>
                                    <div class="col-md-3 col-sm-12">
                                        <a class="btn btn-default btn-block" href="updateTo1-4-1.php">v1.2.4</a>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <a class="btn btn-default btn-block" href="freshInstall.php">I do not currently have Mods for HESK installed</a>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-sm-12">
                                        By proceeding, you agree to the terms of the <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank">Creative Commons Attribution-ShareAlike 4.0 International License.</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <p></p>
        </div>
    </body>
</html>
