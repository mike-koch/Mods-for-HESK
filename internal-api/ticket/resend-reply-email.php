<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../');
define('INTERNAL_API_PATH', '../');
define('PUBLIC_API_PATH', '../../api/');
require_once(HESK_PATH . 'hesk_settings.inc.php');
require_once(HESK_PATH . 'inc/common.inc.php');
require_once(HESK_PATH . 'inc/attachments.inc.php');
require_once(HESK_PATH . 'inc/posting_functions.inc.php');
require_once(HESK_PATH . 'inc/email_functions.inc.php');
require_once(INTERNAL_API_PATH . 'core/output.php');
require_once(INTERNAL_API_PATH . 'core/cors.php');

hesk_session_start();
hesk_load_internal_api_database_functions();
hesk_dbConnect();

// Routing
$request_method = $_SERVER['REQUEST_METHOD'];

//-- Get the ticket by ID
if ($request_method == 'GET') {
    $ticket_id = $_GET['id'];
    $ticket = get_ticket_for_id($hesk_settings, $_SESSION, $ticket_id);

    hesk_notifyCustomer($modsForHesk_settings, 'new_reply_by_staff');

    http_response_code(204);
    return;
}

http_response_code(405);