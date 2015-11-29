<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../../');
define('API_PATH', '../../');
require_once(HESK_PATH . 'hesk_settings.inc.php');
require_once(HESK_PATH . 'inc/common.inc.php');
require_once(API_PATH . 'core/output.php');
require_once(API_PATH . 'core/headers.php');
require_once(API_PATH . 'dao/canned_dao.php');
require_once(API_PATH . 'businesslogic/security_retriever.php');

hesk_load_api_database_functions();
hesk_dbConnect();

// Routing
$request_method = $_SERVER['REQUEST_METHOD'];

/**
 * @api {get} /admin/canned Retrieve a canned response
 * @apiVersion 0.0.0
 * @apiName GetCanned
 * @apiGroup Canned Response
 * @apiPermission protected
 *
 * @apiParam {Number} [id] The ID of the canned response. Omit for all canned responses.
 *
 * @apiSuccess {Number} id ID of the canned response
 * @apiSuccess {String} title The title of the canned response.
 * @apiSuccess {String} message The contents of the canned response, including HTML markup.
 * @apiSuccess {Integer} replyOrder The position of the canned response in the list of canned responses (in multiples of 10).
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *          "id": 2,
 *          "title": "html",
 *          "message": "<p><strong>My<em> canned response&nbsp;</em></strong></p>\r\n<p>%%HESK_ID%%</p>",
 *          "replyOrder": 10
 *     }
 *
 * @apiError (noTokenProvided) 400 No `X-Auth-Token` was provided where it is required
 * @apiError (invalidXAuthToken) 401 The `X-Auth-Token` provided was invalid
 */
if ($request_method == 'GET') {
    $token = get_header('X-Auth-Token');

    try {
        get_user_for_token($token, $hesk_settings);
    } catch (AccessException $e) {
        return http_response_code($e->getCode());
    }

    if (isset($_GET['id'])) {
        $results = get_canned_response($hesk_settings, $_GET['id']);
    } else {
        $results = get_canned_response($hesk_settings);
    }

    if ($results == NULL) {
        return http_response_code(404);
    }
    return output($results);
}

return http_response_code(405);