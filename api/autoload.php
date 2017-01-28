<?php

// Responsible for loading in all necessary scripts and kicking off the DependencyManager
// Core requirements
define('IN_SCRIPT', 1);
define('HESK_PATH', '../');
require_once(__DIR__ . '/../hesk_settings.inc.php');
require_once(__DIR__ . '/../inc/common.inc.php');
require_once(__DIR__ . '/core/output.php');
require_once(__DIR__ . '/Link.php');
require_once(__DIR__ . '/../hesk_settings.inc.php');

// Mods for HESK API Files
require_once(__DIR__ . '/http_response_code.php');

// Categories
require_once(__DIR__ . '/dao/category/CategoryGateway.php');
require_once(__DIR__ . '/businesslogic/category/CategoryRetriever.php');
require_once(__DIR__ . '/businesslogic/category/Category.php');
require_once(__DIR__ . '/controllers/CategoryController.php');

// Banned Emails / IP Addresses
require_once(__DIR__ . '/dao/security/BanGateway.php');
require_once(__DIR__ . '/businesslogic/security/BanRetriever.php');
require_once(__DIR__ . '/businesslogic/security/BannedEmail.php');
require_once(__DIR__ . '/businesslogic/security/BannedIp.php');

hesk_load_api_database_functions();

// HESK files that require database access
require_once(__DIR__ . '/../inc/custom_fields.inc.php');

// Load the ApplicationContext
require_once(__DIR__ . '/ApplicationContext.php');
$applicationContext = new \Core\ApplicationContext();