<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../../');
define('API_PATH', '../../');
require_once(HESK_PATH . 'hesk_settings.inc.php');
require_once(HESK_PATH . 'inc/common.inc.php');
require_once(API_PATH . 'core/headers.php');
require_once(API_PATH . 'core/output.php');
require_once(API_PATH . 'dao/ticket_dao.php');
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
 * @apiSuccess {String} trackid The tracking id of the ticket
 * @apiSuccess {String} name The name of the contact
 * @apiSuccess {String} email The email address of the ticket (empty string if no email)
 * @apiSuccess {Integer} category The ID of the category the ticket is in
 * @apiSuccess {Integer} priority The ID of the priority the ticket is in
 * @apiSuccess {String} subject The subject of the ticket
 * @apiSuccess {String} message The original message of the ticket
 * @apiSuccess {String} dt The date and time the ticket was submitted, in `YYYY-MM-DD hh:mm:ss`
 * @apiSuccess {String} lastchange The date and time the ticket was last changed, in `YYYY-MM-DD hh:mm:ss`
 * @apiSuccess {String} firstreply The date and time the first remply was recorded, in `YYYY-MM-DD hh:mm:ss`
 * @apiSuccess {String} closedat The date and time the ticket was closed, in `YYYY-MM-DD hh:mm:ss`
 * @apiSuccess {Integer} articles The knowledgebase article IDs suggested when the user created the ticket
 * @apiSuccess {String} ip The IP address of the submitter
 * @apiSuccess {String} language The language the ticket was submitted in
 * @apiSuccess {Integer} status The ID of the status the ticket is set to
 * @apiSuccess {Integer} openedby `0` - Ticket opened by staff<br>`1` - Ticket opened by customer
 * @apiSuccess {Integer} firstreplyby `0` - First reply by staff<br>`1` - First reply by customer
 * @apiSuccess {Integer} closedby `0` - Ticket closed by staff<br>`1` - Ticket closed by customer
 * @apiSuccess {Integer} replies Total number of replies to ticket
 * @apiSuccess {Integer} staffreplies Total number of replies to ticket from staff
 * @apiSuccess {Integer} owner The user ID of the ticket owner
 * @apiSuccess {String} time_worked The total time worked on the ticket, in `hh:mm:ss`
 * @apiSuccess {Integer} lastreplier `0` - Last reply by staff<br>`1` - Last reply by customer
 * @apiSuccess {Integer} replierid The user ID of the staff that last replied to the ticket, or `0` if the last reply was made by the customer
 * @apiSuccess {Boolean} archive `true` if the ticket is tagged<br>`false` otherwise
 * @apiSuccess {Boolean} locked `true` if the ticket is locked<br>`false` otherwise
 * @apiSuccess {Binary[]} attachments Array of attachments, in base-64 encoded binary
 * @apiSuccess {Integer[]} merged Array of merged ticket IDs
 * @apiSuccess {String} history HTML markup of the entire "Audit Trail" section
 * @apiSuccess {String} custom1-20 Custom fields 1-20's values.
 * @apiSuccess {Integer} parent The ID of the ticket linked to this ticket
 * @apiSuccess {String} latitude The latitudinal coordinate of the user's location, or one of the corresponding error codes.
 * @apiSuccess {String} longitude The longitudinal coordinate of the user's location, or one of the corresponding error codes.
 * @apiSuccess {Boolean} html `true` if the ticket was created with HTML encoding<br>`false` otherwise
 * @apiSuccess {String} user_agent The user agent of the user who submitted the ticket
 * @apiSuccess {Integer} screen_resolution_width The width of the screen resolution of the user who submitted the ticket
 * @apiSuccess {Integer} screen_resolution_height The height of the screen resolution of the user who submitted the ticket
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *           "id": 22,
 *           "trackid": "EVL-RRL-DUBG",
 *           "name": "Test",
 *           "email": "",
 *           "category": 1,
 *           "priority": 3,
 *           "subject": "test",
 *           "message": "test",
 *           "dt": "2014-12-28 00:57:26",
 *           "lastchange": "2015-03-08 23:38:59",
 *           "firstreply": "2015-01-17 10:21:16",
 *           "closedat": "2015-01-17 15:39:12",
 *           "articles": null,
 *           "ip": "::1",
 *           "language": null,
 *           "status": 3,
 *           "openedby": 0,
 *           "firstreplyby": "1",
 *           "closedby": "1",
 *           "replies": "11",
 *           "staffreplies": "10",
 *           "owner": "1",
 *           "time_worked": "00:05:07",
 *           "lastreplier": 1,
 *           "replierid": 1,
 *           "archive": true,
 *           "locked": true,
 *           "attachments": "",
 *           "merged": "",
 *           "history": "<li class=\"smaller\">2014-12-28 06:57:28 | ticket created by Your name (mkoch)</li><li class=\"smaller\">2014-12-31 21:00:59 | closed by Your name (mkoch)</li><li class=\"smaller\">2014-12-31 21:01:05 | status changed to Waiting reply by Your name (mkoch)</li><li class=\"smaller\">2014-12-31 21:01:58 | closed by Your name (mkoch)</li><li class=\"smaller\">2015-01-17 16:21:18 | closed by Your name (mkoch)</li><li class=\"smaller\">2015-01-17 16:21:31 | closed by Your name (mkoch)</li><li class=\"smaller\">2015-01-17 16:22:05 | closed by Your name (mkoch)</li><li class=\"smaller\">2015-01-17 16:24:06 | status changed to  by Your name (mkoch)</li><li class=\"smaller\">2015-01-17 16:25:40 | status changed to On Hold by Your name (mkoch)</li><li class=\"smaller\">2015-01-17 16:25:53 | status changed to In Progress by Your name (mkoch)</li><li class=\"smaller\">2015-01-17 21:39:11 | locked by Your name (mkoch)</li>",
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
 *           "parent": null,
 *           "latitude": "E-0",
 *           "longitude": "E-0",
 *           "html": false,
 *           "user_agent": null,
 *           "screen_resolution_width": null,
 *           "screen_resolution_height": null
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