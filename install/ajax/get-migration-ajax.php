<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../');
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'install/migrations/core.php');
hesk_load_database_functions();

$allMigrations = getAllMigrations();
end($allMigrations);

print json_encode(array("lastMigrationNumber" => key($allMigrations)));