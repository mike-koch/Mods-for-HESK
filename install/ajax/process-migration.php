<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../');
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
hesk_load_database_functions();


require(HESK_PATH . 'install/migrations/core.php');

$allMigrations = getAllMigrations();
$json = file_get_contents('php://input');
$request = json_decode($json, true);
var_dump($request);

/* @var $migration AbstractMigration */
$migration = $allMigrations[$request['migrationNumber']];

hesk_dbConnect();
if ($request['direction'] === 'up') {
    //$migration->up($hesk_settings);
} elseif ($request['direction'] === 'down') {
    //$migration->down($hesk_settings);
} else {
    output(array("message" => "Invalid direction provided"), 400);
}

function output($data, $response = 200, $header = "Content-Type: application/json") {
    http_response_code($response);
    header($header);
    print json_encode($data);
}