<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../');
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');

hesk_load_api_database_functions();
hesk_dbConnect();

// Routing
if (isset($_GET['id'])) {
    $response = hesk_dbQuery("SELECT `id`, `message`, `title`, `reply_order` FROM `"
        . hesk_dbEscape($hesk_settings['db_pfix']) . "std_replies` WHERE `id` = ".intval($_GET['id']));
    $result = hesk_dbFetchAssoc($response);
    $result['message'] = html_entity_decode($result['message']);

    header('Content-Type: application/json');
    print json_encode($result);
    return http_response_code(200);
}