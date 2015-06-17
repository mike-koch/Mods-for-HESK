<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../../../');
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
hesk_load_database_functions();
require('../sql/uninstallSql.php');

$task = $_POST['task'];
if ($task == 'status-change') {
    replaceStatusColumn();
} elseif ($task == 'drop-columns') {
    removeOtherColumns();
} else {
    http_response_code(400);
}
return;