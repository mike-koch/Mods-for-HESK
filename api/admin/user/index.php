<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../../');
define('API_PATH', '../../');
require_once(HESK_PATH . 'hesk_settings.inc.php');
require_once(HESK_PATH . 'inc/common.inc.php');
require_once(API_PATH . 'core/headers.php');
require_once(API_PATH . 'core/output.php');
require_once(API_PATH . 'businesslogic/user_retriever.php');
require_once(API_PATH . 'businesslogic/security_retriever.php');

hesk_load_api_database_functions();
hesk_dbConnect();

// Routing
$request_method = $_SERVER['REQUEST_METHOD'];

/**
 * @api {get} /admin/user Retrieve a helpdesk user
 * @apiVersion 0.0.0
 * @apiName GetUser
 * @apiGroup User
 * @apiPermission canManUsers
 *
 * @apiParam {Number} [id] The ID of the user. Omit for all users.
 *
 * @apiSuccess {Number} id ID of the user
 * @apiSuccess {String} username The user's username
 * @apiSuccess {Boolean} admin `true` if the user is under a permission template that is market as an administrative group<br>`false` otherwise
 * @apiSuccess {String} name The user's name
 * @apiSuccess {String} email The user's email address
 * @apiSuccess {String} signature The user's signature, in plaintext
 * @apiSuccess {String[]} categories Ticket categories the user has access to. If the user is an admin, this list has one element: ""
 * @apiSuccess {Integer} afterReply Action to perform after replying to a ticket:<br>
 *     `0` - Show the ticket I just replied to<br>
 *     `1` - Return to the main administration page<br>
 *     `2` - Open next ticket that needs my reply
 * @apiSuccess {Boolean} autoStart Automatically start timer when the user opens a ticket
 * @apiSuccess {Boolean} notifyCustomerNew Select notify customer option in the new ticket form
 * @apiSuccess {Boolean} notifyCustomerReply Select notify customer option in the ticket reply form
 * @apiSuccess {Boolean} showSuggested Show what knowledgebase articles were suggested to customers
 * @apiSuccess {Boolean} notifyNewUnassigned Notify the user when a new ticket is submitted with owner: Unassigned
 * @apiSuccess {Boolean} notifyNewMy Notify the user when a new ticket is submitted and is assigned to the user
 * @apiSuccess {Boolean} notifyAssigned Notify the user when a ticket is assigned to the user
 * @apiSuccess {Boolean} notifyReplyUnassigned Notify the user when the client responds to a ticket with owner: Unassigned
 * @apiSuccess {Boolean} notifyReplyMy Notify the user when the client responds to a ticket assigned to the user
 * @apiSuccess {Boolean} notifyPm Notify the user when a private message is sent to the user
 * @apiSuccess {Boolean} notifyNoteUnassigned Notify the user when someone adds a note to a ticket not assigned to the user
 * @apiSuccess {Unknown} defaultList ??? (Currently unknown)
 * @apiSuccess {Boolean} autoassign Tickets are auto-assigned to this user
 * @apiSuccess {String[]} heskPrivileges Helpdesk features the user has access to. If the user is an admin, this list has one element: ""
 * @apiSuccess {Integer} ratingNeg Total number of negative feedback to "Was this reply helpful?" on replies by this user
 * @apiSuccess {Integer} ratingPos Total number of positive feedback to "Was this reply helpful?" on replies by this user
 * @apiSuccess {String} rating The overall rating of the user, as a floating point decimal
 * @apiSuccess {Integer} autorefresh The ticket table autorefresh time for the user, in milliseconds
 * @apiSuccess {Boolean} active `true` if the user is active<br>`false` otherwise
 * @apiSuccess {Integer} defaultCalendarView The default view displayed on the calendar screen:<br>
 *     `0` - Month<br>
 *     `1` - Week<br>
 *     `2` - Day<br>
 * @apiSuccess {Boolean} notifyOverdueUnassigned Notify user of overdue tickets assigned to others / not assigned
 *
 * @apiSuccessExample {json} Success-Response:
 *      HTTP/1.1 200 OK
 *      {
 *          "id": 1,
 *          "username": "mkoch",
 *          "admin": true,
 *          "name": "Your name",
 *          "email": "mkoch227@gmail.com",
 *          "signature": "Sincerely,\r\n\r\nYour name\r\nYour website\r\nhttp://www.yourwebsite.com\r\n& < > ^ &",
 *          "categories": [
 *              ""
 *          ],
 *          "afterReply": 0,
 *          "autoStart": true,
 *          "notifyCustomerNew": true,
 *          "notifyCustomerReply": true,
 *          "showSuggested": true,
 *          "notifyNewUnassigned": true,
 *          "notifyNewMy": true,
 *          "notifyReplyUnassigned": true,
 *          "notifyReplyMy": true,
 *          "notifyAssigned": true,
 *          "notifyPm": false,
 *          "notifyNote": true,
 *          "notifyNoteUnassigned": false,
 *          "defaultList": "",
 *          "autoassign": true,
 *          "heskPrivileges": [
 *              ""
 *          ],
 *          "ratingNeg": 0,
 *          "ratingPos": 0,
 *          "rating": "0",
 *          "autorefresh": 0,
 *          "active": true,
 *          "defaultCalendarView": 0,
 *          "notifyOverdueUnassigned": true
 *      }
 *
 * @apiError (noTokenProvided) 400 No `X-Auth-Token` was provided where it is required
 * @apiError (invalidXAuthToken) 401 The `X-Auth-Token` provided was invalid, or the user does not have the 'can_man_users' permission
 */
if ($request_method == 'GET') {
    $token = get_header('X-Auth-Token');
    $user = NULL;

    try {
        $user = get_user_for_token($token, $hesk_settings);
    } catch (AccessException $e) {
        return http_response_code($e->getCode());
    }

    if (!$user['isadmin'] && strpos($user['heskprivileges'], 'can_man_users') === false) {
        return http_response_code(401);
    }

    if (isset($_GET['id'])) {
        $results = retrieve_user($hesk_settings, $_GET['id']);
    } else {
        $results = retrieve_user($hesk_settings);
    }

    if ($results == NULL) {
        return http_response_code(404);
    }
    return output($results);
}

return http_response_code(405);