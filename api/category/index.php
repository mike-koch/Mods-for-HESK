<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../');
define('API_PATH', '../');
require_once(HESK_PATH . 'hesk_settings.inc.php');
require_once(HESK_PATH . 'inc/common.inc.php');
require_once(API_PATH . 'core/output.php');
require_once(API_PATH . 'dao/category_dao.php');

hesk_load_api_database_functions();
hesk_dbConnect();

// Routing
$request_method = $_SERVER['REQUEST_METHOD'];

/**
 * @api {get} /category Retrieve a ticket category
 * @apiVersion 0.0.0
 * @apiName GetCategory
 * @apiGroup Category
 * @apiPermission public
 *
 * @apiParam {Number} [id] The ID of the category. Omit for all categories.
 *
 * @apiSuccess {Number} id ID of the category
 * @apiSuccess {String} name The name of the category
 * @apiSuccess {Integer} displayOrder The order of the category (in multiples of 10)
 * @apiSuccess {Boolean} autoassign `true` if tickets set to this category are automatically assigned.<br>`false` otherwise
 * @apiSuccess {Integer} type `0` - Public<br>`1` - Private
 * @apiSuccess {Integer} priority Default priority of tickets created in this category
 * @apiSuccess {Integer} manager User ID of the category manager, or `null` if there is no manager.
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *          "id": 1,
 *          "name": "General",
 *          "displayOrder": 10,
 *          "autoassign": true,
 *          "type": 0,
 *          "priority": 2,
 *          "manager": null
 *     }
 */
if ($request_method == 'GET') {
    if (isset($_GET['id'])) {
        $results = get_category($hesk_settings, $_GET['id']);
    } else {
        $results = get_category($hesk_settings);
    }

    if ($results == NULL) {
        return http_response_code(404);
    }
    return output($results);
}

return http_response_code(405);
