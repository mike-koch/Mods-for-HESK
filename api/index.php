<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../');

require_once(__DIR__ . '/core/common.php');
require_once(__DIR__ . '/controllers/CategoryController.php');
hesk_load_api_database_functions();

$categories = \Controllers\Category\CategoryController::getAllCategories($hesk_settings);

output($categories);