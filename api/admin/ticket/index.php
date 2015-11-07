<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../../');
define('API_PATH', '../../');
require_once(HESK_PATH . 'hesk_settings.inc.php');
require_once(HESK_PATH . 'inc/common.inc.php');
require_once(API_PATH . 'core/output.php');
require_once(API_PATH . 'dao/ticket_dao.php');

hesk_load_api_database_functions();
hesk_dbConnect();

// Routing
$request_method = $_SERVER['REQUEST_METHOD'];
if ($request_method == 'GET') {
    if (isset($_GET['id'])) {
        $results = get_ticket_for_id($hesk_settings, $_GET['id']);
    } elseif (isset($_GET['trackid'])) {
        $results = get_ticket_by_tracking_id($hesk_settings, $_GET['trackid']);
    } else {
        $results = get_ticket_for_id($hesk_settings);
    }

    if ($results == NULL) {
        return http_response_code(404);
    }
    return output($results);
}

return http_response_code(405);