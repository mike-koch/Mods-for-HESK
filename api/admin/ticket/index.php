<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../../');
define('API_PATH', '../../');
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
 * @api {get} /admin/ticket Retrieve a ticket (staff-side)
 * @apiVersion 0.0.0
 * @apiName GetTicketStaff
 * @apiGroup Ticket
 * @apiPermission protected
 *
 * @apiParam {Number} [id] The ID of the ticket. Omit for all tickets.
 *
 * @apiSuccess {Number} id ID of the ticket
 * @apiSuccess {String} trackingId The tracking id of the ticket
 * @apiSuccess {String} name The name of the contact
 * @apiSuccess {String} email The email address of the ticket (empty string if no email)
 * @apiSuccess {Integer} category The ID of the category the ticket is in
 * @apiSuccess {Integer} priority The ID of the priority the ticket is in
 * @apiSuccess {String} subject The subject of the ticket
 * @apiSuccess {String} message The original message of the ticket
 * @apiSuccess {Date} dateCreated The date and time the ticket was submitted
 * @apiSuccess {Integer} articles The knowledgebase article IDs suggested when the user created the ticket
 * @apiSuccess {String} ip The IP address of the submitter
 * @apiSuccess {String} language The language the ticket was submitted in
 * @apiSuccess {Integer} status The ID of the status the ticket is set to
 * @apiSuccess {Integer} owner The user ID of the ticket owner
 * @apiSuccess {String} timeWorked The total time worked on the ticket, in `hh:mm:ss`
 * @apiSuccess {Boolean} archive `true` if the ticket is tagged<br>`false` otherwise
 * @apiSuccess {Boolean} locked `true` if the ticket is locked<br>`false` otherwise
 * @apiSuccess {Integer[]} merged Array of merged ticket IDs
 * @apiSuccess {String} legacyAuditTrail HTML markup of the entire "Audit Trail" section
 * @apiSuccess {String} custom1-20 Custom fields 1-20's values.
 * @apiSuccess {Integer} linkedTo The ID of the ticket linked to this ticket
 * @apiSuccess {String} latitude The latitudinal coordinate of the user's location, or one of the corresponding error codes.
 * @apiSuccess {String} longitude The longitudinal coordinate of the user's location, or one of the corresponding error codes.
 * @apiSuccess {Boolean} html `true` if the ticket was created with HTML encoding<br>`false` otherwise
 * @apiSuccess {String} userAgent The user agent of the user who submitted the ticket
 * @apiSuccess {Integer} screenResolutionWidth The width of the screen resolution of the user who submitted the ticket
 * @apiSuccess {Integer} screenResolutionHeight The height of the screen resolution of the user who submitted the ticket
 * @apiSuccess {Date} dueDate The ticket's due date, if there is one
 * @apiSuccess {Boolean} overdueEmailSent Set to `true` if an overdue email has been sent.<br>`false` otherwise
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
 *           "articles": null,
 *           "ip": "127.0.0.1",
 *           "language": null,
 *           "status": 3,
 *           "owner": 1,
 *           "timeWorked": "00:05:07",
 *           "archive": true,
 *           "locked": true,
 *           "attachments": "",
 *           "merged": "",
 *           "legacyAuditTrail": "<li class=\"smaller\">2014-12-28 06:57:28 | ticket created by Your name (username)</li><li class=\"smaller\">2014-12-31 21:00:59 | closed by Your name (username)</li><li class=\"smaller\">2014-12-31 21:01:05 | status changed to Waiting reply by Your name (username)</li><li class=\"smaller\">2014-12-31 21:01:58 | closed by Your name (username)</li><li class=\"smaller\">2015-01-17 16:21:18 | closed by Your name (username)</li>",
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
 *           "parent": 139,
 *           "latitude": "E-0",
 *           "longitude": "E-0",
 *           "html": false,
 *           "userAgent": null,
 *           "screenResolutionWidth": null,
 *           "screenResolutionHeight": null,
 *           "dueDate": "2016-01-01 00:00:00",
 *           "overdueEmailSent": "true"
 *      }
 *
 * @apiError (noTokenProvided) 400 No `X-Auth-Token` was provided where it is required
 * @apiError (invalidXAuthToken) 401 The `X-Auth-Token` provided was invalid
 */
if ($request_method == 'GET') {
    $token = get_header('X-Auth-Token');
    $user = NULL;

    try {
        $user = get_user_for_token($token, $hesk_settings);
    } catch (AccessException $e) {
        return http_response_code($e->getCode());
    }

    if (isset($_GET['id'])) {
        $results = get_ticket_for_staff($hesk_settings, $user, $_GET['id']);
    } else {
        $results = get_ticket_for_staff($hesk_settings, $user);
    }

    if ($results == NULL) {
        return http_response_code(404);
    }
    return output($results);
}

return http_response_code(405);