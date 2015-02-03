<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');
require('modsForHeskSql.php');

if (!isset($_GET['v'])) {
    die('Starting version not set!');
}
$startingVersion = intval($_GET['v']);

function echoInitialVersionRows($version) {
    if ($version < 1) {
        printRow('Pre-v1.4.0');
    }
    if ($version < 140) {
        printRow('v1.4.0');
    }
    if ($version < 141) {
        printRow('v1.4.1');
    }
    if ($version < 150) {
        printRow('v1.5.0');
    }
    if ($version < 160) {
        printRow('v1.6.0');
    }
    if ($version < 161) {
        printRow('v1.6.1');
    }
    if ($version < 170) {
        printRow('v1.7.0');
    }
    if ($version < 200) {
        printRow('v2.0.0');
    }
}

function printRow($version) {
    $versionId = str_replace('.','',$version);
    $versionId = str_replace('v','',$versionId);
    $versionId = str_replace('Pre-','p',$versionId);
    echo '<tr id="row-'.$versionId.'">';
    echo '<td>'.$version.'</td>';
    echo '<td><i id="spinner-'.$versionId.'" class="fa fa-spinner"></i> <span id="span-'.$versionId.'">Waiting...</span></td>';
    echo '</tr>';
}
?>
<html>
    <head>
        <title>Installing / Updating Mods for HESK</title>
        <link href="../hesk_style.css?<?php echo HESK_NEW_VERSION; ?>" type="text/css" rel="stylesheet" />
        <link href="<?php echo HESK_PATH; ?>css/bootstrap.css?v=<?php echo $hesk_settings['hesk_version']; ?>" type="text/css" rel="stylesheet" />
        <link href="<?php echo HESK_PATH; ?>css/bootstrap-theme.css?v=<?php echo $hesk_settings['hesk_version']; ?>" type="text/css" rel="stylesheet" />
        <link href="//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
        <link href="../css/hesk_newStyle.php" type="text/css" rel="stylesheet" />
        <script src="<?php echo HESK_PATH; ?>js/jquery-1.10.2.min.js"></script>
        <script language="Javascript" type="text/javascript" src="<?php echo HESK_PATH; ?>js/bootstrap.min.js"></script>
        <script language="Javascript" type="text/javascript" src="<?php echo HESK_PATH; ?>js/modsForHesk-javascript.js"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo HESK_PATH; ?>js/bootstrap-datepicker.js"></script>
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
                        <table class="table table-striped" style="table-layout:fixed;">
                            <thead>
                            <tr>
                                <th>Version</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                                <?php echoInitialVersionRows($startingVersion); ?>
                            </tbody>
                        </table>
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
                        <div class="panel-body" style="min-height: 400px;max-height: 400px; overflow: auto;">
                            <p id="console-text" style="font-family: 'Courier New',monospace;"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>

<?php
if ($startingVersion < 1) {
    echo '<script>startVersionUpgrade(\'p140\')</script>';
    //executePre140Scripts();
    echo '<script>markUpdateAsSuccess(\'p140\')</script>';
}
if ($startingVersion < 140) {
    echo '<script>startVersionUpgrade(\'140\')</script>';
    //executePre140Scripts();
    echo '<script>markUpdateAsSuccess(\'140\')</script>';
}
if ($startingVersion < 141) {
    echo '<script>startVersionUpgrade(\'141\')</script>';
    //execute141Scripts();
    echo '<script>markUpdateAsSuccess(\'141\')</script>';
}
if ($startingVersion < 150) {
    echo '<script>startVersionUpgrade(\'150\')</script>';
    //execute150Scripts();
    echo '<script>markUpdateAsSuccess(\'150\')</script>';
}
if ($startingVersion < 160) {
    echo '<script>startVersionUpgrade(\'160\')</script>';
    //execute160Scripts();
    echo '<script>markUpdateAsSuccess(\'160\')</script>';
}
if ($startingVersion < 161) {
    echo '<script>startVersionUpgrade(\'161\')</script>';
    //execute161Scripts();
    echo '<script>markUpdateAsSuccess(\'161\')</script>';
}
if ($startingVersion < 170) {
    echo '<script>startVersionUpgrade(\'170\')</script>';
    //execute170Scripts();
    //execute170FileUpdate();
    echo '<script>markUpdateAsSuccess(\'170\')</script>';
}
if ($startingVersion < 200) {
    echo '<script>startVersionUpgrade(\'200\')</script>';
    //execute200Scripts();
    //execute200FileUpdate();
    echo '<script>markUpdateAsSuccess(\'200\')</script>';
    //Echo completion message where the warning panel is.
}
?>