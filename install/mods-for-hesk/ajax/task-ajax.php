<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../../../');
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
hesk_load_database_functions();
require('../sql/installSql.php');

$task = $_POST['task'];
if ($task == 'ip-email-bans') {
    $numberOfBans = checkForIpOrEmailBans();
    $jsonToSend = array();
    if ($numberOfBans > 0) {
        $jsonToSend['status'] = 'ATTENTION';
        $jsonToSend['users'] = array();
        $users = getUsers();
        foreach ($users as $user) {
            array_push($jsonToSend['users'], $user);
        }
    } else {
        $jsonToSend['status'] = 'SUCCESS';
    }
    print json_encode($jsonToSend);
} elseif ($task == 'migrate-bans') {
    migrateBans($_POST['user']);
} else {
    $response = 'The task "'.$task.'" was not recognized. Check your spelling and try again.';
    print $response;
    http_response_code(400);
}