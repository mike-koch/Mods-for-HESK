<?php
// Router: handles all REST requests to go to their proper place. Common dependency loading also happens here
define('IN_SCRIPT', 1);
define('HESK_PATH', '../');
require_once(__DIR__ . '/core/common.php');
require(__DIR__ . '/Link.php');
require(__DIR__ . '/../hesk_settings.inc.php');

// Controllers
require(__DIR__ . '/controllers/CategoryController.php');
hesk_load_api_database_functions();

// Properly handle error logging, as well as a fatal error workaround
error_reporting(0); // Override hesk_settings. We're smarter than it
set_error_handler('errorHandler');
register_shutdown_function('fatalErrorShutdownHandler');

function handle404() {
    http_response_code(404);
    print json_encode('404 found');
}

function assertApiIsEnabled() {
    throw new Exception("Some exception here!", 33);
}

function errorHandler($errorNumber, $errorMessage, $errorFile, $errorLine) {
    print_error(sprintf("Uncaught error in %s", $errorFile), $errorMessage);
    die();
}

function fatalErrorShutdownHandler() {
    $last_error = error_get_last();
    if ($last_error['type'] === E_ERROR) {
        // fatal error
        errorHandler(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
    }
}

// Must use fully-qualified namespace to controllers
Link::before('assertApiIsEnabled');

Link::all(array(
    // Categories
    '/v1/categories' => '\Controllers\Category\CategoryController::printAllCategories',
    '/v1/categories/{i}' => '\Controllers\Category\CategoryController',
    '404' => 'handle404'
));