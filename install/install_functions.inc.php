<?php
/**
 *
 * This file is part of HESK - PHP Help Desk Software.
 *
 * (c) Copyright Klemen Stirn. All rights reserved.
 * https://www.hesk.com
 *
 * For the full copyright and license agreement information visit
 * https://www.hesk.com/eula.php
 *
 */

/* Check if this is a valid include */
if (!defined('IN_SCRIPT')) {die('Invalid attempt');}

// We will be installing this HESK version:
define('HESK_NEW_VERSION','2.7.3');
define('MODS_FOR_HESK_NEW_VERSION','3.1.0');
define('REQUIRE_PHP_VERSION','5.3.0');
define('REQUIRE_MYSQL_VERSION','5.0.7');

// Other required files and settings
define('INSTALL',1);
define('HIDE_ONLINE',1);

require(HESK_PATH . 'hesk_settings.inc.php');

$hesk_settings['debug_mode'] = 1;
$hesk_settings['language']='English';
$hesk_settings['languages']=array('English' => array('folder'=>'en','hr'=>'------ Reply above this line ------'));

error_reporting(E_ALL);

require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/setup_functions.inc.php');
hesk_load_database_functions();

// Start the session
hesk_session_start();