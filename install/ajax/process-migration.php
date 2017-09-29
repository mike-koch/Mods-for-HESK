<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../');

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

if ($request['direction'] === 'up') {
    $migration->up();
} elseif ($request['direction'] === 'down') {
    $migration->down();
} else {
    output(array("message" => "Invalid direction provided"), 400);
}

function output($data, $response = 200) {
    http_response_code($response);
    header('Content-Type: application/json');
    print json_encode($data);
}