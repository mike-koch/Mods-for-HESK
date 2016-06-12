<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../../');
define('INTERNAL_API_PATH', '../../');
require_once(HESK_PATH . 'hesk_settings.inc.php');
require_once(HESK_PATH . 'inc/common.inc.php');
require_once(HESK_PATH . 'inc/admin_functions.inc.php');
require_once(INTERNAL_API_PATH . 'core/output.php');
require_once(INTERNAL_API_PATH . 'dao/api_authentication_dao.php');
require_once(INTERNAL_API_PATH . 'core/cors.php');

hesk_session_start();
hesk_load_internal_api_database_functions();
hesk_dbConnect();

// Routing
$request_method = $_SERVER['REQUEST_METHOD'];
if ($request_method == 'POST') {

    if (!isset($_SESSION['heskprivileges']) || !hesk_checkPermission('can_man_settings', 0)) {
        print_error('Access Denied', 'Access Denied!');
        return http_response_code(401);
    }

    $user_id = $_POST['userId'];
    $action = $_POST['action'];

    if ($user_id == NULL || $action == NULL) {
        return http_response_code(400);
    }

    if ($action == 'generate') {
        $token = '';
        $letter_array = ['0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f'];
        // Pick 32 random characters. That will be the hash
        for ($i = 0; $i < 32; $i++) {
            $letter = $letter_array[rand(0, 15)];
            $token .= $letter;
        }
        $hash = hash("sha512", $token);
        store_token($user_id, $hash, $hesk_settings);

        output($token);
        return http_response_code(200);
    } elseif ($action == 'reset') {
        reset_tokens($user_id, $hesk_settings);
        return http_response_code(204);
    } else {
        return http_response_code(400);
    }
}

return http_response_code(405);