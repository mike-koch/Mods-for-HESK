<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../../');
define('INTERNAL_API_PATH', '../../');
require_once(HESK_PATH . 'hesk_settings.inc.php');
require_once(HESK_PATH . 'inc/common.inc.php');
require_once(INTERNAL_API_PATH . 'core/output.php');
require_once(INTERNAL_API_PATH . 'dao/message_log_dao.php');
require_once(INTERNAL_API_PATH . 'core/cors.php');

hesk_load_internal_api_database_functions();
hesk_dbConnect();

// Routing
$request_method = $_SERVER['REQUEST_METHOD'];
if ($request_method == 'POST') {
    $location = $_POST['location'];
    $from_date = $_POST['fromDate'];
    $to_date = $_POST['toDate'];
    $severity_id = $_POST['severityId'];

    $results = search_log($hesk_settings, $location, $from_date, $to_date, $severity_id);
    print json_encode($results);
    return http_response_code(200);
}