<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../../../');
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
hesk_load_database_functions();
require('../sql/uninstallSql.php');

$task = $_POST['task'];
if ($task == 1) {
    executePre140Scripts();
} else {
    print 'The task "'.$task.'" was not recognized. Check the value submitted and try again.';
    http_response_code(400);
}
return;