<?php
// Router: handles all REST requests to go to their proper place. Common dependency loading also happens here
define('IN_SCRIPT', 1);
define('HESK_PATH', '../');
require_once(__DIR__ . '/core/common.php');
require(__DIR__ . '/../Link/src/Link.php');
require(__DIR__ . '/../hesk_settings.inc.php');

// Controllers
require(__DIR__ . '/controllers/CategoryController.php');
hesk_load_api_database_functions();

function handle404() {
    http_response_code(404);
    print json_encode('404 found');
}

function assertApiIsEnabled() {
    //-- TODO
}

// Must use fully-qualified namespace to controllers
Link::before('assertApiIsEnabled');

Link::all(array(
    // Categories
    '/v1/categories' => '\Controllers\Category\CategoryController::printAllCategories',
    '/v1/categories/{i}' => '\Controllers\Category\CategoryController',
    '404' => 'handle404'
));