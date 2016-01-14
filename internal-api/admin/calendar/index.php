<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../../');
define('INTERNAL_API_PATH', '../../');
require_once(HESK_PATH . 'hesk_settings.inc.php');
require_once(HESK_PATH . 'inc/common.inc.php');
require_once(HESK_PATH . 'inc/attachments.inc.php');
require_once(HESK_PATH . 'inc/posting_functions.inc.php');
require_once(INTERNAL_API_PATH . 'core/output.php');
require_once(INTERNAL_API_PATH . 'dao/calendar_dao.php');

hesk_load_internal_api_database_functions();
hesk_dbConnect();

$modsForHesk_settings = mfh_getSettings();

// Routing
$request_method = $_SERVER['REQUEST_METHOD'];
if ($request_method === 'GET') {
    $start = $_GET['start'];
    $end = $_GET['end'];
    $events = get_events($start, $end, $hesk_settings);

    return output($events);
}

return http_response_code(400);