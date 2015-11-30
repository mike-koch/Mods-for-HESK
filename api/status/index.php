<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../');
define('API_PATH', '../');
require_once(HESK_PATH . 'hesk_settings.inc.php');
require_once(HESK_PATH . 'inc/common.inc.php');
require_once(API_PATH . 'core/output.php');
require_once(API_PATH . 'dao/status_dao.php');

hesk_load_api_database_functions();
hesk_dbConnect();

// Routing
$request_method = $_SERVER['REQUEST_METHOD'];

/**
 * @api {get} /status Retrieve a ticket status
 * @apiVersion 0.0.0
 * @apiName GetStatus
 * @apiGroup Status
 * @apiPermission public
 *
 * @apiParam {Number} [id] The ID of the status. Omit for all statuses.
 *
 * @apiSuccess {Number} id ID of the status
 * @apiSuccess {String} textColor The text color used for the status on the web interface
 * @apiSuccess {Boolean} isNewTicketStatus This status is set when a new ticket is created
 * @apiSuccess {Boolean} isClosed This status closes a ticket
 * @apiSuccess {Boolean} isClosedByClient This status is set when a customer closes a ticket
 * @apiSuccess {Boolean} isCustomerReplyStatus This status is set when a customer responds to a ticket
 * @apiSuccess {Boolean} isStaffClosedOption This status is set when staff clicks the "close ticket" button
 * @apiSuccess {Boolean} isStaffReopenedStatus This status is set when staff clicks the "open ticket" button
 * @apiSuccess {Boolean} isDefaultStaffReplyStatus This status is used when staff responds to a ticket
 * @apiSuccess {Boolean} lockedTicketStatus This status is set when staff clicks the "lock ticket" button
 * @apiSuccess {Boolean} isAutocloseOption This status is set when a ticket is automatically closed
 * @apiSuccess {Boolean} closable Tickets can be closed by the following:<br>
 *     `yes`: Both customers/staff,<br>
 *     `conly`: Only customers,<br>
 *     `sonly`: Only staff,<br>
 *     `no`: No one
 * @apiSuccess {String} key The language key. This is deprecated and should not be used.
 * @apiSuccess {Object[]} keys The language strings for each language
 * @apiSuccess {String} keys.language The language for the status name
 * @apiSuccess {String} keys.text The translated string of the status
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *          "id": 0,
 *          "textColor": "#FF0000",
 *          "isNewTicketStatus": true,
 *          "isClosed": false,
 *          "isClosedByClient": false,
 *          "isCustomerReplyStatus": false,
 *          "isStaffClosedOption": false,
 *          "isStaffReopenedStatus": false,
 *          "isDefaultStaffReplyStatus": false,
 *          "lockedTicketStatus": false,
 *          "isAutocloseOption": false,
 *          "closable": "yes",
 *          "key": null,
 *          "keys": [
 *              {
 *                  "language": "English",
 *                  "text": "New"
 *              },
 *              {
 *                  "language": "Español",
 *                  "text": "Nuevo"
 *              }
 *          ]
 *     }
 */
if ($request_method == 'GET') {
    if (isset($_GET['id'])) {
        $results = get_status($hesk_settings, $_GET['id']);
    } else {
        $results = get_status($hesk_settings);
    }

    if ($results == NULL) {
        return http_response_code(404);
    }
    return output($results);
}

return http_response_code(405);