<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');

function echoTaskRows()
{
    printUninstallRow('Remove custom statuses', 'status-change');
    printUninstallRow('Reset other tables', 'drop-columns');
}

function printUninstallRow($text, $id)
{
    echo '<tr id="row-' . $id . '">';
    echo '<td>' . $text . '</td>';
    echo '<td><i id="spinner-' . $id . '" class="fa fa-spinner"></i> <span id="span-' . $id . '">Waiting...</span></td>';
    echo '</tr>';
}

?>
<html>
<head>
    <title>Uninstalling Mods for HESK</title>
    <link href="../../hesk_style.css?<?php echo HESK_NEW_VERSION; ?>" type="text/css" rel="stylesheet"/>
    <link href="<?php echo HESK_PATH; ?>css/bootstrap.css?v=<?php echo $hesk_settings['hesk_version']; ?>"
          type="text/css" rel="stylesheet"/>
    <link href="<?php echo HESK_PATH; ?>css/bootstrap-theme.css?v=<?php echo $hesk_settings['hesk_version']; ?>"
          type="text/css" rel="stylesheet"/>
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="../../css/hesk_newStyle.php" type="text/css" rel="stylesheet"/>
    <script src="<?php echo HESK_PATH; ?>js/jquery-1.10.2.min.js"></script>
    <script language="Javascript" type="text/javascript" src="<?php echo HESK_PATH; ?>js/bootstrap.min.js"></script>
    <script language="Javascript" type="text/javascript"
            src="<?php echo HESK_PATH; ?>js/modsForHesk-javascript.js"></script>
    <script language="JavaScript" type="text/javascript"
            src="<?php echo HESK_PATH; ?>install/mods-for-hesk/js/ui-scripts.js"></script>
    <script language="JavaScript" type="text/javascript"
            src="<?php echo HESK_PATH; ?>install/mods-for-hesk/js/uninstall-scripts.js"></script>
    <script language="JavaScript" type="text/javascript"
            src="<?php echo HESK_PATH; ?>js/bootstrap-datepicker.js"></script>
</head>
<body>
<div class="headersm">Uninstalling Mods for HESK</div>
<div class="container">
    <div class="page-header">
        <h1>Uninstalling Mods for HESK</h1>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">Uninstallation Progress</div>
                <div id="uninstall-information">
                    <table class="table table-striped" style="table-layout:fixed;">
                        <thead>
                        <?php echoTaskRows(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">Console</div>
                <div style="max-height: 400px; overflow: auto;">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Severity</th>
                            <th>Message</th>
                        </tr>
                        </thead>
                        <tbody id="consoleBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    processUninstallation();
</script>
</body>
</html>