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

define('IN_SCRIPT',1);
define('HESK_PATH','../');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/privacy_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

// Check permissions for this feature
hesk_checkPermission('can_export');

// A security check
hesk_token_check();

// Tracking ID
$trackingID = hesk_cleanID() or die($hesklang['int_error'].': '.$hesklang['no_trackID']);

// Generate SQL for the ticket, make sure the user has access to it
$sql = "SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `trackid`='".hesk_dbEscape($trackingID)."' AND ";
$sql .= hesk_myCategories();
$sql .= " AND " . hesk_myOwnership();
$sql .= " LIMIT 1";

require_once(HESK_PATH . 'inc/custom_fields.inc.php');
require_once(HESK_PATH . 'inc/statuses.inc.php');
require(HESK_PATH . 'inc/export_functions.inc.php');

list($success_msg, $tickets_exported) = hesk_export_to_XML($sql, true);

if ($tickets_exported == 1)
{
    hesk_process_messages($success_msg,'admin_ticket.php?track='.$trackingID.'&Refresh='.mt_rand(10000,99999),'SUCCESS');
}

hesk_error($hesklang['n2ex']);
