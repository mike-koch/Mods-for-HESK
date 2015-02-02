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
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>Version</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="success">
                                <td>v1.4.0</td>
                                <td>Success</td>
                            </tr>
                            <tr>
                                <td>v1.4.0</td>
                                <td>...</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>

<?php

if ($startingVersion < 140) {
    // Process 140 scripts
}
if ($startingVersion < 141) {
    // Process 141 scripts
}
if ($startingVersion < 150) {
    // Process 150 scripts
}
if ($startingVersion < 160) {
    // Process 160 scripts
}
if ($startingVersion < 161) {
    //Process 161 scripts
}
if ($startingVersion < 170) {
    // Process 170 scripts
}
if ($startingVersion < 200) {
    // Process 200 scripts
}
?>