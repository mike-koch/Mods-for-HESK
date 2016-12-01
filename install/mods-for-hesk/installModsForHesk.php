<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');

if (!isset($_GET['v'])) {
    die('Starting version not set!');
}
$startingVersion = intval($_GET['v']);

$buildToVersionMap = array(
    2 => 'Pre-1.4.0',
    3 => '1.4.0',
    4 => '1.4.1',
    5 => '1.5.0',
    6 => '1.6.0',
    7 => '1.6.1',
    8 => '1.7.0',
    9 => '2.0.0',
    10 => '2.0.1',
    11 => '2.1.0',
    12 => '2.1.1',
    13 => '2.2.0',
    14 => '2.2.1',
    15 => '2.3.0',
    16 => '2.3.1',
    17 => '2.3.2',
    18 => '2.4.0',
    19 => '2.4.1',
    20 => '2.4.2',
    21 => '2.5.0',
    22 => '2.5.1',
    23 => '2.5.2',
    24 => '2.5.3',
    25 => '2.5.4',
    26 => '2.5.5',
    27 => '2.6.0',
    28 => '2.6.1',
    29 => '2.6.2',
    30 => '2.6.3',
    31 => '2.6.4',
    32 => '3.0.0 beta 1',
    33 => '3.0.0 RC 1',
);

function echoInitialVersionRows($version, $build_to_version_map)
{
    foreach ($build_to_version_map as $build => $display_text) {
        if ($version < $build) {
            printRow($display_text);
        }
    }
}

function printRow($version)
{
    $versionId = str_replace('.', '', $version);
    $versionId = str_replace('Pre-', 'p', $versionId);
    echo '<tr id="row-' . $versionId . '">';
    echo '<td>' . $version . '</td>';
    echo '<td><i id="spinner-' . $versionId . '" class="fa fa-spinner"></i> <span id="span-' . $versionId . '">Waiting...</span></td>';
    echo '</tr>';
}

?>
<html>
<head>
    <title>Installing / Updating Mods for HESK</title>
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
<div class="headersm">Installing / Updating Mods for HESK</div>
<div class="container">
    <div class="page-header">
        <h1>Installing / Updating Mods for HESK</h1>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">Installation Progress</div>
                <div id="install-information">
                    <table class="table table-striped" style="table-layout:fixed;">
                        <thead>
                        <tr>
                            <th>Version</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php echoInitialVersionRows($startingVersion, $buildToVersionMap); ?>
                        </tbody>
                    </table>
                    <?php if ($startingVersion < 18) { ?>
                        <table class="table table-striped" style="table-layout: fixed">
                            <thead>
                            <tr>
                                <th>Task</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($startingVersion < 9): ?>
                                <tr id="row-banmigrate">
                                    <td>Migrate IP / Email Bans</td>
                                    <td><i id="spinner-banmigrate" class="fa fa-spinner"></i> <span
                                            id="span-banmigrate">Waiting...</span></td>
                                </tr>
                            <?php endif; ?>
                            <tr id="row-initialize-statuses">
                                <td>Initialize Statuses</td>
                                <td><i id="spinner-initialize-statuses" class="fa fa-spinner"></i> <span
                                        id="span-initialize-statuses">Waiting...</span></td>
                            </tr>
                            </tbody>
                        </table>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row" id="attention-row" style="display:none">
        <div class="col-sm-12">
            <div class="panel panel-warning">
                <div class="panel-heading">Your Attention is Needed!</div>
                <div class="panel-body" id="attention-body">
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
    processUpdates(<?php echo intval($startingVersion); ?>);
</script>
</body>
</html>