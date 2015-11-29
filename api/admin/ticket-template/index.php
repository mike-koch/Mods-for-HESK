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

/**
 * @api {get} /admin/ticket-template Retrieve a ticket templates
 * @apiVersion 0.0.0
 * @apiName GetTicketTemplate
 * @apiGroup Ticket Template
 * @apiPermission protected
 *
 * @apiParam {Number} [id] The ID of the ticket template. Omit for all templates.
 *
 * @apiSuccess {Number} id ID of the template
 * @apiSuccess {String} title The title of the template.
 * @apiSuccess {String} message The contents of the template, including HTML markup.
 * @apiSuccess {Integer} displayOrder The position of the template in the list of templates (in multiples of 10).
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *          "id": 2,
 *          "title": "html",
 *          "message": "<p><strong>My<em> ticket template&nbsp;</em></strong></p>",
 *          "displayOrder": 10
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