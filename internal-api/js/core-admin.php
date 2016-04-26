<?php
define('IN_SCRIPT', 1);
require_once('../../hesk_settings.inc.php');
header('Content-Type: application/javascript');
echo "
var g_isInAdmin = true;

function getHelpdeskUrl() {
    return '".$hesk_settings['hesk_url']."';
}

function getAdminDirectory() {
    return '".$hesk_settings['admin_dir']."';
}
";