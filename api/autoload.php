<?php

// Responsible for loading in all necessary scripts and kicking off the DependencyManager

define('IN_SCRIPT', 1);
define('HESK_PATH', '../');
require_once(__DIR__ . '/core/common.php');
require_once(__DIR__ . '/Link.php');
require_once(__DIR__ . '/../hesk_settings.inc.php');

// FILES
require_once(__DIR__ . '/http_response_code.php');
require_once(__DIR__ . '/dao/category/CategoryGateway.php');
require_once(__DIR__ . '/businesslogic/category/CategoryRetriever.php');
require_once(__DIR__ . '/businesslogic/category/Category.php');
require_once(__DIR__ . '/controllers/CategoryController.php');

hesk_load_api_database_functions();

// HESK files that require database access
require_once(__DIR__ . '/../inc/custom_fields.inc.php');

require_once(__DIR__ . '/DependencyManager.php');

$applicationContext = new \Core\DependencyManager();