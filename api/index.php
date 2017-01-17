<?php
define('IN_SCRIPT', 1);

// Just call the controller
require_once(__DIR__ . '/controllers/CategoryController.php');
require_once(__DIR__ . '/../hesk_settings.inc.php');

\Controllers\Category\CategoryController::getAllCategories($hesk_settings);