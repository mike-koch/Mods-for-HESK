<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../../../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');
require('../modsForHeskSql.php');

$task = $_POST['task'];
if ($task == 'ip-email-bans') {
    $numberOfBans = checkForIpOrEmailBans();
    $jsonToSend = array();
    if ($numberOfBans > 0) {
        $users = getUsers();
        $jsonToSend['status'] = 'ATTENTION';
        $jsonToSend['users'] = array();
        while ($row = hesk_dbFetchAssoc($users)) {
            $jsonToSend['users'][$row['id']] = $row['name'];
        }
    } else {
        $jsonToSend['status'] = 'SUCCESS';
    }
    return json_encode($jsonToSend);
}