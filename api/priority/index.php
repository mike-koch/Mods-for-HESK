<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../');
define('API_PATH', '../');
require_once(API_PATH . 'core/output.php');

// Routing
$request_method = $_SERVER['REQUEST_METHOD'];

if ($request_method == 'GET') {
    $results = [];
    $critical['id'] = 0;
    $critical['key'] = 'critical';
    $results[] = $critical;
    $high['id'] = 1;
    $high['key'] = 'high';
    $results[] = $high;
    $medium['id'] = 2;
    $medium['key'] = 'medium';
    $results[] = $medium;
    $low['id'] = 3;
    $low['key'] = 'low';
    $results[] = $low;
    return output($results);
}

return http_response_code(405);