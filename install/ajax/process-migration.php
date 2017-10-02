<?php
xdebug_disable();
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../');
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
hesk_load_database_functions();

set_error_handler(function($errorNumber, $errorMessage, $errorFile, $errorLine) {
    output("An error occurred: {$errorMessage} in {$errorFile} on {$errorLine}",
        500,
        "Content-Type: text/plain");
});

spl_autoload_register(function ($class) {
    // USED FOR MIGRATIONS
    $file = HESK_PATH . 'install/migrations/' . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require($file);
    } else {
        output(array("message" => "{$file} not found!", 500));
    }
});

require(HESK_PATH . 'install/migrations/core.php');

$allMigrations = getAllMigrations();
$json = file_get_contents('php://input');
$request = json_decode($json, true);

/* @var $migration AbstractMigration */
$migration = $allMigrations[$request['migrationNumber']];

hesk_dbConnect();
if ($request['direction'] === 'up') {
    $migration->up($hesk_settings);
} elseif ($request['direction'] === 'down') {
    $migration->down($hesk_settings);
} else {
    output(array("message" => "Invalid direction provided"), 400);
}

function output($data, $response = 200, $header = "Content-Type: application/json") {
    http_response_code($response);
    header($header);
    print json_encode($data);
}