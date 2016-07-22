<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../');
define('API_PATH', '../');
require_once(HESK_PATH . 'hesk_settings.inc.php');
require_once(HESK_PATH . 'inc/common.inc.php');
require_once(API_PATH . 'core/headers.php');
require_once(API_PATH . 'core/output.php');
require_once(API_PATH . 'businesslogic/ticket_retriever.php');
require_once(API_PATH . 'businesslogic/security_retriever.php');

hesk_load_api_database_functions();
hesk_dbConnect();

// Routing
$request_method = $_SERVER['REQUEST_METHOD'];

/**
 * @api {get} /ticket Retrieve a ticket (customer-side)
 * @apiVersion 0.0.0
 * @apiName GetTicket
 * @apiGroup Ticket
 * @apiPermission protected
 *
 * @apiParam {Number} [id] The ID of the ticket.
 *
 * @apiSuccess {Number} id ID of the ticket
 * @apiSuccess {String} trackid The tracking id of the ticket
 * @apiSuccess {String} name The name of the contact
 * @apiSuccess {String} email The email address of the ticket (empty string if no email)
 * @apiSuccess {Integer} category The ID of the category the ticket is in
 * @apiSuccess {Integer} priority The ID of the priority the ticket is in
 * @apiSuccess {String} subject The subject of the ticket
 * @apiSuccess {String} message The original message of the ticket
 * @apiSuccess {String} dt The date and time the ticket was submitted, in `YYYY-MM-DD hh:mm:ss`
 * @apiSuccess {Integer} status The ID of the status the ticket is set to
 * @apiSuccess {Boolean} archive `true` if the ticket is tagged<br>`false` otherwise
 * @apiSuccess {Boolean} locked `true` if the ticket is locked<br>`false` otherwise
 * @apiSuccess {Binary[]} attachments Array of attachments, in base-64 encoded binary
 * @apiSuccess {Integer[]} merged Array of merged ticket IDs
 * @apiSuccess {String} custom1-20 Custom fields 1-20's values.
 * @apiSuccess {Boolean} html `true` if the ticket was created with HTML encoding<br>`false` otherwise
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *           "id": 22,
 *           "trackingId": "EVL-RRL-DUBG",
 *           "name": "Test",
 *           "email": "",
 *           "category": 1,
 *           "priority": 3,
 *           "subject": "test",
 *           "message": "test",
 *           "dateCreated": "2014-12-28 00:57:26",
 *           "status": 3,
 *           "archive": true,
 *           "locked": true,
 *           "attachments": "",
 *           "merged": "",
 *           "custom1": "1420671600",
 *           "custom2": "",
 *           "custom3": "",
 *           "custom4": "",
 *           "custom5": "",
 *           "custom6": "",
 *           "custom7": "",
 *           "custom8": "",
 *           "custom9": "",
 *           "custom10": "",
 *           "custom11": "",
 *           "custom12": "",
 *           "custom13": "",
 *           "custom14": "",
 *           "custom15": "",
 *           "custom16": "",
 *           "custom17": "",
 *           "custom18": "",
 *           "custom19": "",
 *           "custom20": "",
 *           "html": false
 *      }
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
        $results = get_ticket($hesk_settings, $_GET['id']);
    } else {
        return http_response_code(400);
    }

    if ($results == NULL) {
        return http_response_code(404);
    }
    return output($results);
}

return http_response_code(405);