<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../../');
define('INTERNAL_API_PATH', '../../');
require_once(HESK_PATH . 'hesk_settings.inc.php');
require_once(HESK_PATH . 'inc/common.inc.php');
require_once(INTERNAL_API_PATH . 'core/output.php');
require_once(INTERNAL_API_PATH . 'dao/api_authentication_dao.php');

hesk_load_internal_api_database_functions();
hesk_dbConnect();

// Routing
$request_method = $_SERVER['REQUEST_METHOD'];
if ($request_method == 'POST') {
    $user_id = $_POST['userId'];
    $action = $_POST['action'];

    if ($user_id == NULL || $action == NULL) {
        return http_response_code(400);
    }

    if ($action == 'generate') {
        $hash = hash("sha512", time());
        store_token($user_id, $hash, $hesk_settings);

        output($hash);
        return http_response_code(200);
    } elseif ($action == 'reset') {
        //TODO
    } else {
        return http_response_code(400);
    }
}

return http_response_code(405);