<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.6.8 from 10th August 2016
*  Author: Klemen Stirn
*  Website: https://www.hesk.com
********************************************************************************
*  COPYRIGHT AND TRADEMARK NOTICE
*  Copyright 2005-2015 Klemen Stirn. All Rights Reserved.
*  HESK is a registered trademark of Klemen Stirn.

*  The HESK may be used and modified free of charge by anyone
*  AS LONG AS COPYRIGHT NOTICES AND ALL THE COMMENTS REMAIN INTACT.
*  By using this code you agree to indemnify Klemen Stirn from any
*  liability that might arise from it's use.

*  Selling the code for this program, in part or full, without prior
*  written consent is expressly forbidden.

*  Using this code, in part or full, to create derivate work,
*  new scripts or products is expressly forbidden. Obtain permission
*  before redistributing this software over the Internet or in
*  any other medium. In all cases copyright and header must remain intact.
*  This Copyright is in full effect in any country that has International
*  Trade Agreements with the United States of America or
*  with the European Union.

*  Removing any of the copyright notices without purchasing a license
*  is expressly forbidden. To remove HESK copyright notice you must purchase
*  a license for this script. For more information on how to obtain
*  a license please visit the page below:
*  https://www.hesk.com/buy.php
*******************************************************************************/

/* Check if this is a valid include */
if (!defined('IN_SCRIPT')) {die('Invalid attempt');}

// We will be installing this HESK version:
define('HESK_NEW_VERSION','2.7.3');
define('MODS_FOR_HESK_NEW_VERSION','3.0.5');
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