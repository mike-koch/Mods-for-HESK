<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');
require('modsForHeskSql.php');

$version = $_POST['version'];
$type = $_POST['type'];
return;