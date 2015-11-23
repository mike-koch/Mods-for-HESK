<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../../');
define('API_PATH', '../../');
require_once(HESK_PATH . 'hesk_settings.inc.php');
require_once(HESK_PATH . 'inc/common.inc.php');
require_once(API_PATH . 'core/headers.php');
require_once(API_PATH . 'core/output.php');
require_once(API_PATH . 'dao/ticket_template_dao.php');
require_once(API_PATH . 'businesslogic/security_retriever.php');

hesk_load_api_database_functions();
hesk_dbConnect();

// Routing
$request_method = $_SERVER['REQUEST_METHOD'];

if ($request_method == 'GET') {
    $token = get_header('X-Auth-Token');

    try {
        get_user_for_token($token, $hesk_settings);
    } catch (AccessException $e) {
        if ($e->getCode() == 422) {
            print_error($e->getMessage(), $e->getMessage());
        }
        return http_response_code($e->getCode());
    }

    if (isset($_GET['id'])) {
        $results = get_ticket_template($hesk_settings, $_GET['id']);
    } else {
        $results = get_ticket_template($hesk_settings);
    }

    if ($results == NULL) {
        return http_response_code(404);
    }
    return output($results);
}

return http_response_code(405);