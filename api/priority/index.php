<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../');
define('API_PATH', '../');
require_once(API_PATH . 'core/output.php');

// Routing
$request_method = $_SERVER['REQUEST_METHOD'];

/**
 * @api {get} /priority Retrieve a ticket priority
 * @apiVersion 0.0.0
 * @apiName GetPriority
 * @apiGroup Priority
 * @apiPermission public
 *
 * @apiParam {Number} [id] The ID of the priority. Omit for all priorities.
 *
 * @apiSuccess {Number} id ID of the priority
 * @apiSuccess {String} key The language file key of the priority
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *          "id": 0,
 *          "key": "critical"
 *     }
 */
if ($request_method == 'GET') {
    $results = array();
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