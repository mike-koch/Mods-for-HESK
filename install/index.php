<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');

hesk_dbConnect();
?>
<html>
<head>
    <title>Mods For HESK <?php echo MODS_FOR_HESK_NEW_VERSION; ?> Install / Upgrade</title>
    <link href="<?php echo HESK_PATH; ?>../hesk_style.css?<?php echo HESK_NEW_VERSION; ?>" type="text/css" rel="stylesheet"/>
    <link href="<?php echo HESK_PATH; ?>css/bootstrap.css?v=<?php echo $hesk_settings['hesk_version']; ?>"
          type="text/css" rel="stylesheet"/>
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="<?php echo HESK_PATH; ?>css/hesk_newStyle.css" type="text/css" rel="stylesheet"/>
    <link href="<?php echo HESK_PATH; ?>css/AdminLTE.min.css" type="text/css" rel="stylesheet">
    <link href="<?php echo HESK_PATH; ?>css/mods-for-hesk-new.css" type="text/css" rel="stylesheet">
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
    <style>
        body {
            background: url('install/background.jpg') no-repeat center center fixed;
            background-size: cover;
        }
    </style>
</head>
<body>

<div class="login-box installer-login-box">
    <div class="login-box-container">
        <div class="login-box-background"></div>
        <div class="login-box-body">
            <div class="login-logo">
                Thanks for choosing Mods for HESK.
            </div>
            <h4 class="login-box-msg">
                Let's get started.
            </h4>
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
</body>
</html>
