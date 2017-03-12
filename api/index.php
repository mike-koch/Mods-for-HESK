<?php
// Properly handle error logging, as well as a fatal error workaround
require_once(__DIR__ . '/autoload.php');
error_reporting(0);
set_error_handler('errorHandler');
set_exception_handler('exceptionHandler');
register_shutdown_function('fatalErrorShutdownHandler');

$userContext = null;

function handle404() {
    print output(array(
        'message' => "The endpoint '{$_SERVER['REQUEST_URI']}' was not found. Double-check your request and submit again.",
        'uri' => $_SERVER['REQUEST_URI']
    ), 404);
}

function before() {
    assertApiIsEnabled();

    $token = \BusinessLogic\Helpers::getHeader('X-AUTH-TOKEN');
    buildUserContext($token);
}

function assertApiIsEnabled() {
    global $applicationContext, $hesk_settings;

    /* @var $apiChecker \BusinessLogic\Settings\ApiChecker */
    $apiChecker = $applicationContext->get[\BusinessLogic\Settings\ApiChecker::class];

    if (!$apiChecker->isApiEnabled($hesk_settings)) {
        http_response_code(404);
        die();
    }

    return;
}

function buildUserContext($xAuthToken) {
    global $applicationContext, $userContext, $hesk_settings;

    /* @var $userContextBuilder \BusinessLogic\Security\UserContextBuilder */
    $userContextBuilder = $applicationContext->get[\BusinessLogic\Security\UserContextBuilder::class];

    $userContext = $userContextBuilder->buildUserContext($xAuthToken, $hesk_settings);
}

function errorHandler($errorNumber, $errorMessage, $errorFile, $errorLine) {
    exceptionHandler(new Exception(sprintf("%s:%d\n\n%s", $errorFile, $errorLine, $errorMessage)));
}

/**
 * @param $exception Exception
 */
function exceptionHandler($exception) {
    //-- TODO Log an error
    if (exceptionIsOfType($exception, \BusinessLogic\Exceptions\ApiFriendlyException::class)) {
        /* @var $castedException \BusinessLogic\Exceptions\ApiFriendlyException */
        $castedException = $exception;

        print_error($castedException->title, $castedException->getMessage(), $castedException->httpResponseCode);
    } elseif (exceptionIsOfType($exception, \Core\Exceptions\SQLException::class)) {
        /* @var $castedException \Core\Exceptions\SQLException */
        $castedException = $exception;
        print_error("Fought an uncaught SQL exception", sprintf("%s\n\n%s", $castedException->failingQuery, $exception->getTraceAsString()));
    } else {
        print_error("Fought an uncaught exception of type " . get_class($exception), sprintf("%s\n\n%s", $exception->getMessage(), $exception->getTraceAsString()));
    }
    // Log more stuff to logging table if possible; we'll catch any exceptions from this
    die();
}

/**
 * @param $exception Exception thrown exception
 * @param $class string The name of the expected exception type
 * @return bool
 */
function exceptionIsOfType($exception, $class) {
    return is_a($exception, $class);
}

function fatalErrorShutdownHandler() {
    $last_error = error_get_last();
    if ($last_error['type'] === E_ERROR) {
        // fatal error
        errorHandler(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
    }
}

Link::before('before');

Link::all(array(
    // Categories
    '/v1/categories' => '\Controllers\Categories\CategoryController::printAllCategories',
    '/v1/categories/{i}' => '\Controllers\Categories\CategoryController',
    // Tickets
    '/v1/tickets/{i}' => '\Controllers\Tickets\TicketController',
    '/v1/tickets' => '\Controllers\Tickets\TicketController',

    // Any URL that doesn't match goes to the 404 handler
    '404' => 'handle404'
));