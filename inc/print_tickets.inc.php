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
if (!defined('IN_SCRIPT')) {
    die('Invalid attempt');
}

// Load custom fields
require_once(HESK_PATH . 'inc/custom_fields.inc.php');

// This SQL code will be used to retrieve results
$sql_final = "SELECT
`id`,
`trackid`,
`name`,
`email`,
`category`,
`priority`,
`subject`,
LEFT(`message`, 400) AS `message`,
`dt`,
`lastchange`,
`firstreply`,
`closedat`,
`status`,
`openedby`,
`firstreplyby`,
`closedby`,
`replies`,
`staffreplies`,
`owner`,
`time_worked`,
`lastreplier`,
`replierid`,
`archive`,
`locked`,
`merged`
";

foreach ($hesk_settings['custom_fields'] as $k => $v) {
    if ($v['use']) {
        $sql_final .= ", `" . $k . "`";
    }
}

$sql_final .= " FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE ";

// This code will be used to count number of results
$sql_count = "SELECT COUNT(*) FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE ";

// This is common SQL for both queries
$sql = "";

// Some default settings
$archive = array(1 => 0, 2 => 0);
$s_my = array(1 => 1, 2 => 1);
$s_ot = array(1 => 1, 2 => 1);
$s_un = array(1 => 1, 2 => 1);

// --> TICKET CATEGORY
$category = intval(hesk_GET('category', 0));

// Make sure user has access to this category
if ($category && hesk_okCategory($category, 0)) {
    $sql .= " `category`='{$category}' ";
} // No category selected, show only allowed categories
else {
    $sql .= hesk_myCategories();
}

// Show only tagged tickets?
if (!empty($_GET['archive'])) {
    $archive[1] = 1;
    $sql .= " AND `archive`='1' ";
}

// Ticket owner preferences
$fid = 1;
require(HESK_PATH . 'inc/assignment_search.inc.php');

// --> TICKET STATUS
$statuses = mfh_getAllStatuses();
$totalStatuses = 0;
$possible_status = array();
foreach ($statuses as $row) {
    $possible_status[$row['ID']] = $row['ID'];
    $totalStatuses++;
}
$status = $possible_status;
// Process statuses unless overridden with "s_all" variable
if (!hesk_GET('s_all')) {
    foreach ($status as $k => $v) {
        if (empty($_GET['s' . $v])) {
            unset($status[$k]);
        }
    }
}

// How many statuses are we pulling out of the database?\
$tmp = count($status);

// Do we need to search by status?
if ($tmp < $totalStatuses) {
    // If no statuses selected, show default (all except RESOLVED)
    if ($tmp == 0) {
        $status = $possible_status;
        foreach ($statuses as $row) {
            if ($row['IsClosed'] == 0) {
                continue;
            }

            unset($status[$row['ID']]);
        }
    }

    // Add to the SQL
    $sql .= " AND `status` IN ('" . implode("','", array_keys($status)) . "') ";
}

// --> TICKET PRIORITY
$possible_priority = array(
    0 => 'CRITICAL',
    1 => 'HIGH',
    2 => 'MEDIUM',
    3 => 'LOW',
);

$priority = $possible_priority;

foreach ($priority as $k => $v) {
    if (empty($_GET['p' . $k])) {
        unset($priority[$k]);
    }
}

// How many priorities are we pulling out of the database?
$tmp = count($priority);

// Create the SQL based on the number of priorities we need
if ($tmp == 0 || $tmp == 4) {
    // Nothing or all selected, no need to modify the SQL code
    $priority = $possible_priority;
} else {
    // A custom selection of priorities
    $sql .= " AND `priority` IN ('" . implode("','", array_keys($priority)) . "') ";
}

// That's all the SQL we need for count
$sql_count .= $sql;
$sql = $sql_final . $sql;

// Prepare variables used in search and forms
require(HESK_PATH . 'inc/prepare_ticket_search.inc.php');

// List tickets?
if (!isset($_SESSION['hide']['ticket_list'])) {
    $href = 'show_tickets.php';
    require(HESK_PATH . 'inc/ticket_list.inc.php');
}
