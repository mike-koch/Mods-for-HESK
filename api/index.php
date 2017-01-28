<?php
// Properly handle error logging, as well as a fatal error workaround
require_once(__DIR__ . '/autoload.php');
error_reporting(0);
set_error_handler('errorHandler');
register_shutdown_function('fatalErrorShutdownHandler');

function handle404() {
    http_response_code(404);
    print json_encode('404 found');
}

function assertApiIsEnabled() {

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

    // Any URL that doesn't match goes to the 404 handler
    '404' => 'handle404'
));