<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../../');
define('INTERNAL_API_PATH', '../../');
require_once(HESK_PATH . 'hesk_settings.inc.php');
require_once(HESK_PATH . 'inc/common.inc.php');
require_once(HESK_PATH . 'inc/admin_functions.inc.php');
require_once(INTERNAL_API_PATH . 'core/output.php');
require_once(INTERNAL_API_PATH . 'dao/settings_dao.php');
require_once(INTERNAL_API_PATH . 'core/cors.php');

hesk_session_start();
hesk_load_internal_api_database_functions();
hesk_dbConnect();

if (!isset($_SESSION['heskprivileges']) || !hesk_checkPermission('can_man_settings', 0)) {
    print_error('Access Denied', 'Access Denied!');
    return http_response_code(401);
}

// Routing
$request_method = $_SERVER['REQUEST_METHOD'];
if ($request_method == 'POST') {
    $key = $_POST['key'];
    $value = $_POST['value'];

    if ($key == NULL || $value == NULL) {
        return http_response_code(400);
    }

    update_setting($key, $value, $hesk_settings);

    return http_response_code(204);
}

return http_response_code(405);