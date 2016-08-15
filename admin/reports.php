<?php
/*******************************************************************************
 *  Title: Help Desk Software HESK
 *  Version: 2.6.8 from 10th August 2016
 *  Author: Klemen Stirn
 *  Website: http://www.hesk.com
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

define('IN_SCRIPT', 1);
define('HESK_PATH', '../');
define('PAGE_TITLE', 'ADMIN_REPORTS');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/reporting_functions.inc.php');
require(HESK_PATH . 'inc/mail_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

// Check permissions for this feature
hesk_checkPermission('can_run_reports');

// Should reports be full or limited to own tickets?
$can_run_reports_full = hesk_checkPermission('can_run_reports_full', 0);

// Set default values
define('CALENDAR', 1);
define('MAIN_PAGE', 1);
define('LOAD_TABS', 1);

$selected = array(
    'w' => array(0 => '', 1 => ''),
    'time' => array(1 => '', 2 => '', 3 => '', 4 => '', 5 => '', 6 => '', 7 => '', 8 => '', 9 => '', 10 => '', 11 => '', 12 => ''),
    'type' => array(1 => '', 2 => '', 3 => '', 4 => ''),
);
$is_all_time = 0;

/* Default this month to date */
$date_from = date('Y-m-d', mktime(0, 0, 0, date("m"), 1, date("Y")));
$date_to = date('Y-m-d');
$input_datefrom = date('m/d/Y', strtotime('last month'));
$input_dateto = date('m/d/Y');

/* Date */
if (!empty($_GET['w'])) {
    $df = preg_replace('/[^0-9]/', '', hesk_GET('datefrom'));
    if (strlen($df) == 8) {
        $date_from = substr($df, 4, 4) . '-' . substr($df, 0, 2) . '-' . substr($df, 2, 2);
        $input_datefrom = substr($df, 0, 2) . '/' . substr($df, 2, 2) . '/' . substr($df, 4, 4);
    } else {
        $date_from = date('Y-m-d', strtotime('last month'));
    }

    $dt = preg_replace('/[^0-9]/', '', hesk_GET('dateto'));
    if (strlen($dt) == 8) {
        $date_to = substr($dt, 4, 4) . '-' . substr($dt, 0, 2) . '-' . substr($dt, 2, 2);
        $input_dateto = substr($dt, 0, 2) . '/' . substr($dt, 2, 2) . '/' . substr($dt, 4, 4);
    } else {
        $date_to = date('Y-m-d');
    }

    if ($date_from > $date_to) {
        $tmp = $date_from;
        $tmp2 = $input_datefrom;

        $date_from = $date_to;
        $input_datefrom = $input_dateto;

        $date_to = $tmp;
        $input_dateto = $tmp2;

        $note_buffer = $hesklang['datetofrom'];
    }

    if ($date_to > date('Y-m-d')) {
        $date_to = date('Y-m-d');
        $input_dateto = date('m/d/Y');
    }

    $query_string = 'reports.php?w=1&amp;datefrom=' . urlencode($input_datefrom) . '&amp;dateto=' . urlencode($input_dateto);
    $selected['w'][1] = 'checked="checked"';
    $selected['time'][3] = 'selected="selected"';
} else {
    $selected['w'][0] = 'checked="checked"';
    $_GET['time'] = intval(hesk_GET('time', 3));

    switch ($_GET['time']) {
        case 1:
            /* Today */
            $date_from = date('Y-m-d');
            $date_to = $date_from;
            $selected['time'][1] = 'selected="selected"';
            $is_all_time = 1;
            break;

        case 2:
            /* Yesterday */
            $date_from = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
            $date_to = $date_from;
            $selected['time'][2] = 'selected="selected"';
            $is_all_time = 1;
            break;

        case 4:
            /* Last month */
            $date_from = date('Y-m-d', mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
            $date_to = date('Y-m-d', mktime(0, 0, 0, date("m"), 0, date("Y")));
            $selected['time'][4] = 'selected="selected"';
            break;

        case 5:
            /* Last 30 days */
            $date_from = date('Y-m-d', mktime(0, 0, 0, date("m") - 1, date("d"), date("Y")));
            $date_to = date('Y-m-d');
            $selected['time'][5] = 'selected="selected"';
            break;

        case 6:
            /* This week */
            list($date_from, $date_to) = dateweek(0);
            $date_to = date('Y-m-d');
            $selected['time'][6] = 'selected="selected"';
            break;

        case 7:
            /* Last week */
            list($date_from, $date_to) = dateweek(-1);
            $selected['time'][7] = 'selected="selected"';
            break;

        case 8:
            /* This business week */
            list($date_from, $date_to) = dateweek(0, 1);
            $date_to = date('Y-m-d');
            $selected['time'][8] = 'selected="selected"';
            break;

        case 9:
            /* Last business week */
            list($date_from, $date_to) = dateweek(-1, 1);
            $selected['time'][9] = 'selected="selected"';
            break;

        case 10:
            /* This year */
            $date_from = date('Y') . '-01-01';
            $date_to = date('Y-m-d');
            $selected['time'][10] = 'selected="selected"';
            break;

        case 11:
            /* Last year */
            $date_from = date('Y') - 1 . '-01-01';
            $date_to = date('Y') - 1 . '-12-31';
            $selected['time'][11] = 'selected="selected"';
            break;

        case 12:
            /* All time */
            $date_from = hesk_getOldestDate();
            $date_to = date('Y-m-d');
            $selected['time'][12] = 'selected="selected"';
            $is_all_time = 1;
            break;

        default:
            $_GET['time'] = 3;
            $selected['time'][3] = 'selected="selected"';
    }

    $query_string = 'reports.php?w=0&amp;time=' . $_GET['time'];
}

unset($tmp);

/* Type */
$type = intval(hesk_GET('type', 1));
if (isset($selected['type'][$type])) {
    $selected['type'][$type] = 'selected="selected"';
}

// Setup date SQL so we don't have to call functions several times
$hesk_settings['dt_sql'] = " `dt` BETWEEN '" . hesk_dbEscape($date_from) . " 00:00:00' AND '" . hesk_dbEscape($date_to) . " 23:59:59' ";

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>
<section class="content">
    <div class="box">
        <?php if (hesk_checkPermission('can_export', 0)) {
            $canExport = true;
            $panelMargin = '-15px';
        } else {
            $canExport = false;
        }
        ?>
        <div class="box-header">
            <h1 class="box-title">
                <?php echo $hesklang['reports_tab']; ?> <a href="#"
                                                           onclick="javascript:alert('<?php echo hesk_makeJsString($hesklang['reports_intro']); ?>')"><i
                        class="fa fa-question-circle settingsquestionmark"></i></a>
            </h1><br>
            <?php
            // Show a link to export.php if user has permission to do so
            if ($canExport) {
                echo '<small><a title="' . $hesklang['export'] . '" href="export.php">' . $hesklang['export'] . '</a></small><div class="blankSpace"></div>';
            }
            ?>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <form action="reports.php" method="get" name="form1" role="form">
                <div class="form-group">
                    <label for="dtrg" class="control-label"><?php echo $hesklang['dtrg']; ?>:</label>

                    <div class="radio move-right-20">
                        <input type="radio" name="w" value="0" id="w0" <?php echo $selected['w'][0]; ?> />
                        <select name="time" onclick="document.getElementById('w0').checked = true"
                                onfocus="document.getElementById('w0').checked = true"
                                style="margin-top:5px;margin-bottom:5px;">
                            <option value="1" <?php echo $selected['time'][1]; ?>><?php echo $hesklang['r1']; ?>
                                (<?php echo $hesklang['d' . date('w')]; ?>)
                            </option>
                            <option value="2" <?php echo $selected['time'][2]; ?>><?php echo $hesklang['r2']; ?>
                                (<?php echo $hesklang['d' . date('w', mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')))]; ?>
                                )
                            </option>
                            <option value="3" <?php echo $selected['time'][3]; ?>><?php echo $hesklang['r3']; ?>
                                (<?php echo $hesklang['m' . date('n')]; ?>)
                            </option>
                            <option value="4" <?php echo $selected['time'][4]; ?>><?php echo $hesklang['r4']; ?>
                                (<?php echo $hesklang['m' . date('n', mktime(0, 0, 0, date('m') - 1, date('d'), date('Y')))]; ?>
                                )
                            </option>
                            <option
                                value="5" <?php echo $selected['time'][5]; ?>><?php echo $hesklang['r5']; ?></option>
                            <option
                                value="6" <?php echo $selected['time'][6]; ?>><?php echo $hesklang['r6']; ?></option>
                            <option
                                value="7" <?php echo $selected['time'][7]; ?>><?php echo $hesklang['r7']; ?></option>
                            <option
                                value="8" <?php echo $selected['time'][8]; ?>><?php echo $hesklang['r8']; ?></option>
                            <option
                                value="9" <?php echo $selected['time'][9]; ?>><?php echo $hesklang['r9']; ?></option>
                            <option
                                value="10" <?php echo $selected['time'][10]; ?>><?php echo $hesklang['r10']; ?>
                                (<?php echo date('Y'); ?>)
                            </option>
                            <option
                                value="11" <?php echo $selected['time'][11]; ?>><?php echo $hesklang['r11']; ?>
                                (<?php echo date('Y', mktime(0, 0, 0, date('m'), date('d'), date('Y') - 1)); ?>)
                            </option>
                            <option
                                value="12" <?php echo $selected['time'][12]; ?>><?php echo $hesklang['r12']; ?></option>
                        </select>
                    </div>
                    <div class="radio move-right-20">
                        <input type="radio" name="w" value="1" id="w1" <?php echo $selected['w'][1]; ?> />
                        <?php echo $hesklang['from']; ?> <input type="text" name="datefrom"
                                                                value="<?php echo $input_datefrom; ?>"
                                                                id="datefrom" class="tcal" size="10"
                                                                onclick="document.getElementById('w1').checked = true"
                                                                onfocus="document.getElementById('w1').checked = true;this.focus;"/>
                        <?php echo $hesklang['to']; ?> <input type="text" name="dateto"
                                                              value="<?php echo $input_dateto; ?>" id="dateto"
                                                              class="tcal" size="10"
                                                              onclick="document.getElementById('w1').checked = true"
                                                              onfocus="document.getElementById('w1').checked = true; this.focus;"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="type" class="control-label"><?php echo $hesklang['crt']; ?></b>:</label>
                    <select name="type" class="form-control">
                        <option
                            value="1" <?php echo $selected['type'][1]; ?>><?php echo $hesklang['t1']; ?></option>
                        <option
                            value="2" <?php echo $selected['type'][2]; ?>><?php echo $hesklang['t2']; ?></option>
                        <option
                            value="3" <?php echo $selected['type'][3]; ?>><?php echo $hesklang['t3']; ?></option>
                        <option
                            value="4" <?php echo $selected['type'][4]; ?>><?php echo $hesklang['t4']; ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="submit" value="<?php echo $hesklang['dire']; ?>" class="btn btn-default"/>
                    <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>"/>
                </div>
            </form>
        </div>
    </div>
    <div class="box">
        <div class="box-header">
            <h1 class="box-title">
                <?php
                if ($date_from == $date_to) {
                    echo hesk_dateToString($date_from, 0);
                } else {
                    echo hesk_dateToString($date_from, 0) . ' - ' . hesk_dateToString($date_to, 0);
                }
                ?>
            </h1>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <?php
            /* This will handle error, success and notice messages */
            hesk_handle_messages();
            ?>

            <?php

            // Show a note if reports are limited
            if (!$can_run_reports_full) {
                echo "<p>{$hesklang['roo']}</p>";
            }

            /* Report type */
            switch ($type) {
                case 2:
                    hesk_ticketsByMonth();
                    break;
                case 3:
                    hesk_ticketsByUser();
                    break;
                case 4:
                    hesk_ticketsByCategory();
                    break;
                default:
                    hesk_ticketsByDay();
            }


            /*** START FUNCTIONS ***/


            function hesk_ticketsByCategory()
            {
                global $hesk_settings, $hesklang, $date_from, $date_to, $can_run_reports_full;

                /* List of categories */
                $cat = array();
                $res = hesk_dbQuery("SELECT `id`,`name` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` WHERE " . ($can_run_reports_full ? '1' : hesk_myCategories('id')) . " ORDER BY `id` ASC");
                while ($row = hesk_dbFetchAssoc($res)) {
                    $cat[$row['id']] = $row['name'];
                }

                $tickets = array();
                $totals = array('num_tickets' => 0, 'resolved' => 0, 'all_replies' => 0, 'staff_replies' => 0, 'worked' => 0);

                /* Populate category counts */
                foreach ($cat as $id => $name) {
                    $tickets[$id] = array(
                        'num_tickets' => 0,
                        'resolved' => 0,
                        'all_replies' => 0,
                        'staff_replies' => 0,
                        'worked' => '',
                    );
                }

                /* SQL query for category stats */
                $res = hesk_dbQuery("SELECT `category`, COUNT(*) AS `num_tickets`, " . ($hesk_settings['time_worked'] ? "SUM( TIME_TO_SEC(`time_worked`) ) AS `seconds_worked`," : '') . " SUM(`replies`) AS `all_replies`, SUM(staffreplies) AS `staff_replies` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE {$hesk_settings['dt_sql']} " . ($can_run_reports_full ? "" : " AND `t1`.`owner` = '" . intval($_SESSION['id']) . "'") . " GROUP BY `category`");

                /* Update ticket values */
                while ($row = hesk_dbFetchAssoc($res)) {
                    if (!$hesk_settings['time_worked']) {
                        $row['seconds_worked'] = 0;
                    }

                    if (isset($cat[$row['category']])) {
                        $tickets[$row['category']]['num_tickets'] += $row['num_tickets'];
                        $tickets[$row['category']]['all_replies'] += $row['all_replies'];
                        $tickets[$row['category']]['staff_replies'] += $row['staff_replies'];
                        $tickets[$row['category']]['worked'] = $hesk_settings['time_worked'] ? hesk_SecondsToHHMMSS($row['seconds_worked']) : 0;
                    } else {
                        /* Category deleted */
                        if (!isset($tickets[9999])) {
                            $cat[9999] = $hesklang['catd'];
                            $tickets[9999] = array('num_tickets' => $row['num_tickets'], 'resolved' => 0, 'all_replies' => $row['all_replies'], 'staff_replies' => $row['staff_replies'], 'worked' => $row['seconds_worked']);
                        } else {
                            $tickets[9999]['num_tickets'] += $row['num_tickets'];
                            $tickets[9999]['all_replies'] += $row['all_replies'];
                            $tickets[9999]['staff_replies'] += $row['staff_replies'];
                            $tickets[9999]['worked'] += $row['seconds_worked'];
                        }
                    }

                    $totals['num_tickets'] += $row['num_tickets'];
                    $totals['all_replies'] += $row['all_replies'];
                    $totals['staff_replies'] += $row['staff_replies'];
                    $totals['worked'] += $row['seconds_worked'];
                }

                // Get number of resolved tickets
                $res = hesk_dbQuery("SELECT COUNT(*) AS `num_tickets` , `category` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `status` IN (SELECT `ID` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` WHERE `IsClosed` = 1) " . ($can_run_reports_full ? "" : " AND `owner` = '" . intval($_SESSION['id']) . "'") . " AND {$hesk_settings['dt_sql']} GROUP BY `category`");

                // Update number of open and resolved tickets
                while ($row = hesk_dbFetchAssoc($res)) {
                    if (isset($cat[$row['category']])) {
                        $tickets[$row['category']]['resolved'] += $row['num_tickets'];
                    } else {
                        // Category deleted
                        $tickets[9999]['resolved'] += $row['num_tickets'];
                    }

                    $totals['resolved'] += $row['num_tickets'];
                }

                // Convert total seconds worked to HH:MM:SS
                $totals['worked'] = $hesk_settings['time_worked'] ? hesk_SecondsToHHMMSS($totals['worked']) : 0;
                if (isset($tickets[9999])) {
                    $tickets[9999]['worked'] = $hesk_settings['time_worked'] ? hesk_SecondsToHHMMSS($tickets[9999]['worked']) : 0;
                }

                ?>
                <table class="table table-striped table-condensed">
                    <tr>
                        <th><?php echo $hesklang['category']; ?></th>
                        <th><?php echo $hesklang['tickets']; ?></th>
                        <th><?php echo $hesklang['topen']; ?></th>
                        <th><?php echo $hesklang['closed_title']; ?></th>
                        <th><?php echo $hesklang['replies'] . ' (' . $hesklang['all'] . ')'; ?></th>
                        <th><?php echo $hesklang['replies'] . ' (' . $hesklang['staff'] . ')'; ?></th>
                        <?php
                        if ($hesk_settings['time_worked']) {
                            echo '<th>' . $hesklang['ts'] . '</th>';
                        }
                        ?>
                    </tr>

                    <?php
                    $num_tickets = count($tickets);
                    if ($num_tickets > 10) {
                        ?>
                        <tr>
                            <td><b><?php echo $hesklang['totals']; ?></b></td>
                            <td><b><?php echo $totals['num_tickets']; ?></b></td>
                            <td><b><?php echo $totals['num_tickets'] - $totals['resolved']; ?></b></td>
                            <td><b><?php echo $totals['resolved']; ?></b></td>
                            <td><b><?php echo $totals['all_replies']; ?></b></td>
                            <td><b><?php echo $totals['staff_replies']; ?></b></td>
                            <?php
                            if ($hesk_settings['time_worked']) {
                                echo '<td><b>' . $totals['worked'] . '</b></td>';
                            }
                            ?>
                        </tr>
                        <?php
                    }


                    foreach ($tickets as $k => $d) {

                        ?>
                        <tr>
                            <td><?php echo $cat[$k]; ?></td>
                            <td><?php echo $d['num_tickets']; ?></td>
                            <td><?php echo $d['num_tickets'] - $d['resolved']; ?></td>
                            <td><?php echo $d['resolved']; ?></td>
                            <td><?php echo $d['all_replies']; ?></td>
                            <td><?php echo $d['staff_replies']; ?></td>
                            <?php
                            if ($hesk_settings['time_worked']) {
                                echo '<td>' . $d['worked'] . '</td>';
                            }
                            ?>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td><b><?php echo $hesklang['totals']; ?></b></td>
                        <td><b><?php echo $totals['num_tickets']; ?></b></td>
                        <td><b><?php echo $totals['num_tickets'] - $totals['resolved']; ?></b></td>
                        <td><b><?php echo $totals['resolved']; ?></b></td>
                        <td><b><?php echo $totals['all_replies']; ?></b></td>
                        <td><b><?php echo $totals['staff_replies']; ?></b></td>
                        <?php
                        if ($hesk_settings['time_worked']) {
                            echo '<td><b>' . $totals['worked'] . '</b></td>';
                        }
                        ?>
                    </tr>
                </table>
                <?php
            } // END hesk_ticketsByCategory


            function hesk_ticketsByUser()
            {
                global $hesk_settings, $hesklang, $date_from, $date_to;

                // Some variables we will need
                $tickets = array();
                $totals = array('asstickets' => 0, 'resolved' => 0, 'tickets' => 0, 'replies' => 0, 'worked' => 0);

                // Get list of users
                $admins = array();

                // I. ADMINISTRATORS can view all users
                if ($_SESSION['isadmin'] || hesk_checkPermission('can_run_reports_full', 0)) {
                    // -> get list of users
                    $res = hesk_dbQuery("SELECT `id`,`name` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ORDER BY `name` ASC");

                    // -> populate $admins and $tickets arrays
                    while ($row = hesk_dbFetchAssoc($res)) {
                        $admins[$row['id']] = $row['name'];

                        $tickets[$row['id']] = array(
                            'asstickets' => 0,
                            'resolved' => 0,
                            'tickets' => 0,
                            'replies' => 0,
                            'worked' => '',
                        );
                    }

                    // -> get list of tickets
                    $res = hesk_dbQuery("SELECT `owner`, COUNT(*) AS `cnt`" . ($hesk_settings['time_worked'] ? ", SUM( TIME_TO_SEC(`time_worked`) ) AS `seconds_worked`" : '') . " FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `owner` IN ('" . implode("','", array_keys($admins)) . "') AND {$hesk_settings['dt_sql']} GROUP BY `owner`");

                    // -> update ticket list values
                    while ($row = hesk_dbFetchAssoc($res)) {
                        if (!$hesk_settings['time_worked']) {
                            $row['seconds_worked'] = 0;
                        }

                        $tickets[$row['owner']]['asstickets'] += $row['cnt'];
                        $totals['asstickets'] += $row['cnt'];
                        $tickets[$row['owner']]['worked'] = $hesk_settings['time_worked'] ? hesk_SecondsToHHMMSS($row['seconds_worked']) : 0;
                        $totals['worked'] += $row['seconds_worked'];
                    }

                    // -> get list of resolved tickets
                    $res = hesk_dbQuery("SELECT `owner`, COUNT(*) AS `cnt` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `owner` IN ('" . implode("','", array_keys($admins)) . "') AND `status` IN (SELECT `ID` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` WHERE `IsClosed` = 1) AND {$hesk_settings['dt_sql']} GROUP BY `owner`");

                    // -> update resolved ticket list values
                    while ($row = hesk_dbFetchAssoc($res)) {
                        $tickets[$row['owner']]['resolved'] += $row['cnt'];
                        $totals['resolved'] += $row['cnt'];
                    }

                    // -> get number of replies
                    $res = hesk_dbQuery("SELECT `staffid`, COUNT(*) AS `cnt`, COUNT(DISTINCT `replyto`) AS `tcnt` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` WHERE `staffid` IN ('" . implode("','", array_keys($admins)) . "') AND {$hesk_settings['dt_sql']} GROUP BY `staffid`");

                    // -> update number of replies values
                    while ($row = hesk_dbFetchAssoc($res)) {
                        $tickets[$row['staffid']]['tickets'] += $row['tcnt'];
                        $tickets[$row['staffid']]['replies'] += $row['cnt'];

                        $totals['tickets'] += $row['tcnt'];
                        $totals['replies'] += $row['cnt'];
                    }
                } // II. OTHER STAFF may only see their own stats
                else {
                    $admins[$_SESSION['id']] = $_SESSION['name'];

                    // -> get list of tickets
                    $res = hesk_dbQuery("SELECT COUNT(*) AS `cnt`" . ($hesk_settings['time_worked'] ? ", SUM( TIME_TO_SEC(`time_worked`) ) AS `seconds_worked`" : '') . " FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `owner` = '" . intval($_SESSION['id']) . "' AND {$hesk_settings['dt_sql']}");
                    $row = hesk_dbFetchAssoc($res);

                    // -> update ticket values
                    $tickets[$_SESSION['id']]['asstickets'] = $row['cnt'];
                    $totals['asstickets'] = $row['cnt'];
                    $tickets[$_SESSION['id']]['worked'] = $hesk_settings['time_worked'] ? hesk_SecondsToHHMMSS($row['seconds_worked']) : 0;
                    $totals['worked'] += $row['seconds_worked'];

                    // -> get list of resolved tickets
                    $res = hesk_dbQuery("SELECT COUNT(*) AS `cnt` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `owner` = '" . intval($_SESSION['id']) . "' AND `status` IN (SELECT `ID` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` WHERE `IsClosed` = 1) AND {$hesk_settings['dt_sql']}");
                    $row = hesk_dbFetchAssoc($res);

                    // -> update resolved ticket values
                    $tickets[$_SESSION['id']]['resolved'] = $row['cnt'];
                    $totals['resolved'] = $row['cnt'];

                    // -> get number of replies
                    $res = hesk_dbQuery("SELECT COUNT(*) AS `cnt`, COUNT(DISTINCT `replyto`) AS `tcnt` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` WHERE `staffid` = '" . intval($_SESSION['id']) . "' AND {$hesk_settings['dt_sql']}");
                    $row = hesk_dbFetchAssoc($res);

                    $tickets[$_SESSION['id']]['tickets'] = $row['tcnt'];
                    $tickets[$_SESSION['id']]['replies'] = $row['cnt'];

                    $totals['tickets'] = $row['tcnt'];
                    $totals['replies'] = $row['cnt'];

                }

                // Convert total seconds worked to HH:MM:SS
                $totals['worked'] = $hesk_settings['time_worked'] ? hesk_SecondsToHHMMSS($totals['worked']) : 0;

                ?>
                <table class="table table-striped table-condensed">
                    <tr>
                        <th><?php echo $hesklang['user']; ?></th>
                        <th><?php echo $hesklang['ticass']; ?></th>
                        <th><?php echo $hesklang['topen']; ?></th>
                        <th><?php echo $hesklang['closed_title']; ?></th>
                        <th><?php echo $hesklang['ticall']; ?></th>
                        <th><?php echo $hesklang['replies']; ?></th>
                        <?php
                        if ($hesk_settings['time_worked']) {
                            echo '<th>' . $hesklang['ts'] . '</th>';
                        }
                        ?>
                    </tr>

                    <?php
                    $num_tickets = count($tickets);
                    if ($num_tickets > 10) {
                        ?>
                        <tr>
                            <td><b><?php echo $hesklang['totals']; ?></b></td>
                            <td><b><?php echo $totals['asstickets']; ?></b></td>
                            <td><b><?php echo $totals['asstickets'] - $totals['resolved']; ?></b></td>
                            <td><b><?php echo $totals['resolved']; ?></b></td>
                            <td><b><?php echo $totals['tickets']; ?></b></td>
                            <td><b><?php echo $totals['replies']; ?></b></td>
                            <?php
                            if ($hesk_settings['time_worked']) {
                                echo '<td><b>' . $totals['worked'] . '</b></td>';
                            }
                            ?>
                        </tr>
                        <?php
                    }

                    foreach ($tickets as $k => $d) {

                        ?>
                        <tr>
                            <td><?php echo $admins[$k]; ?></td>
                            <td><?php echo $d['asstickets']; ?></td>
                            <td><?php echo $d['asstickets'] - $d['resolved']; ?></td>
                            <td><?php echo $d['resolved']; ?></td>
                            <td><?php echo $d['tickets']; ?></td>
                            <td><?php echo $d['replies']; ?></td>
                            <?php
                            if ($hesk_settings['time_worked']) {
                                echo '<td>' . $d['worked'] . '</td>';
                            }
                            ?>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td><b><?php echo $hesklang['totals']; ?></b></td>
                        <td><b><?php echo $totals['asstickets']; ?></b></td>
                        <td><b><?php echo $totals['asstickets'] - $totals['resolved']; ?></b></td>
                        <td><b><?php echo $totals['resolved']; ?></b></td>
                        <td><b><?php echo $totals['tickets']; ?></b></td>
                        <td><b><?php echo $totals['replies']; ?></b></td>
                        <?php
                        if ($hesk_settings['time_worked']) {
                            echo '<td><b>' . $totals['worked'] . '</b></td>';
                        }
                        ?>
                    </tr>
                </table>
                <?php
            } // END hesk_ticketsByUser


            function hesk_ticketsByMonth()
            {
                global $hesk_settings, $hesklang, $date_from, $date_to, $can_run_reports_full;

                $tickets = array();
                $totals = array('all' => 0, 'resolved' => 0, 'worked' => 0);
                $dt = MonthsArray($date_from, $date_to);

                // Pre-populate date values
                foreach ($dt as $month) {
                    $tickets[$month] = array(
                        'all' => 0,
                        'resolved' => 0,
                        'worked' => '',
                    );
                }

                // SQL query for all
                $res = hesk_dbQuery("SELECT YEAR(`dt`) AS `myyear`, MONTH(`dt`) AS `mymonth`, COUNT(*) AS `cnt`" . ($hesk_settings['time_worked'] ? ", SUM( TIME_TO_SEC(`time_worked`) ) AS `seconds_worked`" : '') . " FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE " . ($can_run_reports_full ? '1' : "`owner` = '" . intval($_SESSION['id']) . "'") . " AND {$hesk_settings['dt_sql']} GROUP BY `myyear`,`mymonth`");

                // Update ticket values
                while ($row = hesk_dbFetchAssoc($res)) {
                    if (!$hesk_settings['time_worked']) {
                        $row['seconds_worked'] = 0;
                    }

                    $row['mymonth'] = sprintf('%02d', $row['mymonth']);
                    $tickets[$row['myyear'] . '-' . $row['mymonth'] . '-01']['all'] += $row['cnt'];
                    $tickets[$row['myyear'] . '-' . $row['mymonth'] . '-01']['worked'] = $hesk_settings['time_worked'] ? hesk_SecondsToHHMMSS($row['seconds_worked']) : 0;
                    $totals['all'] += $row['cnt'];
                    $totals['worked'] += $row['seconds_worked'];
                }

                // SQL query for resolved
                $res = hesk_dbQuery("SELECT YEAR(`dt`) AS `myyear`, MONTH(`dt`) AS `mymonth`, COUNT(*) AS `cnt` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE " . ($can_run_reports_full ? '1' : "`owner` = '" . intval($_SESSION['id']) . "'") . " AND `status` IN (SELECT `ID` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` WHERE `IsClosed` = 1) AND {$hesk_settings['dt_sql']} GROUP BY `myyear`,`mymonth`");

                // Update ticket values
                while ($row = hesk_dbFetchAssoc($res)) {
                    $row['mymonth'] = sprintf('%02d', $row['mymonth']);
                    $tickets[$row['myyear'] . '-' . $row['mymonth'] . '-01']['resolved'] += $row['cnt'];
                    $totals['resolved'] += $row['cnt'];
                }

                // Convert total seconds worked to HH:MM:SS
                $totals['worked'] = $hesk_settings['time_worked'] ? hesk_SecondsToHHMMSS($totals['worked']) : 0;

                ?>
                <table class="table table-striped table-condensed">
                    <tr>
                        <th><?php echo $hesklang['month']; ?></th>
                        <th><?php echo $hesklang['atik']; ?></th>
                        <th><?php echo $hesklang['topen']; ?></th>
                        <th><?php echo $hesklang['closed_title']; ?></th>
                        <?php
                        if ($hesk_settings['time_worked']) {
                            echo '<th>' . $hesklang['ts'] . '</th>';
                        }
                        ?>
                    </tr>

                    <?php
                    $num_tickets = count($tickets);
                    if ($num_tickets > 10) {
                        ?>
                        <tr>
                            <th><b><?php echo $hesklang['totals']; ?></b></th>
                            <th><b><?php echo $totals['all']; ?></b></th>
                            <th><b><?php echo $totals['all'] - $totals['resolved']; ?></b></th>
                            <th><b><?php echo $totals['resolved']; ?></b></th>
                            <?php
                            if ($hesk_settings['time_worked']) {
                                echo '<th><b>' . $totals['worked'] . '</b></th>';
                            }
                            ?>
                        </tr>
                        <?php
                    }

                    foreach ($tickets as $k => $d) {

                        ?>
                        <tr>
                            <td><?php echo hesk_dateToString($k, 0, 0, 1); ?></td>
                            <td><?php echo $d['all']; ?></td>
                            <td><?php echo $d['all'] - $d['resolved']; ?></td>
                            <td><?php echo $d['resolved']; ?></td>
                            <?php
                            if ($hesk_settings['time_worked']) {
                                echo '<td>' . $d['worked'] . '</td>';
                            }
                            ?>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td><b><?php echo $hesklang['totals']; ?></b></td>
                        <td><b><?php echo $totals['all']; ?></b></td>
                        <td><b><?php echo $totals['all'] - $totals['resolved']; ?></b></td>
                        <td><b><?php echo $totals['resolved']; ?></b></td>
                        <?php
                        if ($hesk_settings['time_worked']) {
                            echo '<td><b>' . $d['worked'] . '</b></td>';
                        }
                        ?>
                    </tr>
                </table>

                <?php
            } // END hesk_ticketsByMonth


            function hesk_ticketsByDay()
            {
                global $hesk_settings, $hesklang, $date_from, $date_to, $can_run_reports_full;

                $tickets = array();
                $totals = array('all' => 0, 'resolved' => 0, 'worked' => 0);
                $dt = DateArray($date_from, $date_to);

                // Pre-populate date values
                foreach ($dt as $day) {
                    $tickets[$day] = array(
                        'all' => 0,
                        'resolved' => 0,
                        'worked' => '',
                    );
                }

                // SQL query for all
                $res = hesk_dbQuery("SELECT DATE(`dt`) AS `mydt`, COUNT(*) AS `cnt`" . ($hesk_settings['time_worked'] ? ", SUM( TIME_TO_SEC(`time_worked`) ) AS `seconds_worked`" : '') . " FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE " . ($can_run_reports_full ? '1' : "`owner` = '" . intval($_SESSION['id']) . "'") . " AND {$hesk_settings['dt_sql']} GROUP BY `mydt`");

                // Update ticket values
                while ($row = hesk_dbFetchAssoc($res)) {
                    if (!$hesk_settings['time_worked']) {
                        $row['seconds_worked'] = 0;
                    }

                    $tickets[$row['mydt']]['all'] += $row['cnt'];
                    $tickets[$row['mydt']]['worked'] = $hesk_settings['time_worked'] ? hesk_SecondsToHHMMSS($row['seconds_worked']) : 0;
                    $totals['all'] += $row['cnt'];
                    $totals['worked'] += $row['seconds_worked'];
                }

                // SQL query for resolved
                $res = hesk_dbQuery("SELECT DATE(`dt`) AS `mydt`, COUNT(*) AS `cnt` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE " . ($can_run_reports_full ? '1' : "`owner` = '" . intval($_SESSION['id']) . "'") . " AND `status` IN (SELECT `ID` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` WHERE `IsClosed` = 1) AND {$hesk_settings['dt_sql']} GROUP BY `mydt`");

                // Update ticket values
                while ($row = hesk_dbFetchAssoc($res)) {
                    $tickets[$row['mydt']]['resolved'] += $row['cnt'];
                    $totals['resolved'] += $row['cnt'];
                }

                // Convert total seconds worked to HH:MM:SS
                $totals['worked'] = $hesk_settings['time_worked'] ? hesk_SecondsToHHMMSS($totals['worked']) : 0;

                ?>
                <table class="table table-striped table-condensed">
                    <tr>
                        <th><?php echo $hesklang['date']; ?></th>
                        <th><?php echo $hesklang['atik']; ?></th>
                        <th><?php echo $hesklang['topen']; ?></th>
                        <th><?php echo $hesklang['closed_title']; ?></th>
                        <?php
                        if ($hesk_settings['time_worked']) {
                            echo '<th>' . $hesklang['ts'] . '</th>';
                        }
                        ?>
                    </tr>

                    <?php
                    $num_tickets = count($tickets);
                    if ($num_tickets > 10) {
                        ?>
                        <tr>
                            <td><b><?php echo $hesklang['totals']; ?></b></td>
                            <td><b><?php echo $totals['all']; ?></b></td>
                            <td><b><?php echo $totals['all'] - $totals['resolved']; ?></b></td>
                            <td><b><?php echo $totals['resolved']; ?></b></td>
                            <?php
                            if ($hesk_settings['time_worked']) {
                                echo '<td><b>' . $totals['worked'] . '</b></td>';
                            }
                            ?>
                        </tr>
                        <?php
                    }

                    foreach ($tickets as $k => $d) {

                        ?>
                        <tr>
                            <td><?php echo hesk_dateToString($k); ?></td>
                            <td><?php echo $d['all']; ?></td>
                            <td><?php echo $d['all'] - $d['resolved']; ?></td>
                            <td><?php echo $d['resolved']; ?></td>
                            <?php
                            if ($hesk_settings['time_worked']) {
                                echo '<td>' . $d['worked'] . '</td>';
                            }
                            ?>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td><b><?php echo $hesklang['totals']; ?></b></td>
                        <td><b><?php echo $totals['all']; ?></b></td>
                        <td><b><?php echo $totals['all'] - $totals['resolved']; ?></b></td>
                        <td><b><?php echo $totals['resolved']; ?></b></td>
                        <?php
                        if ($hesk_settings['time_worked']) {
                            echo '<td><b>' . $totals['worked'] . '</b></td>';
                        }
                        ?>
                    </tr>
                </table>
                <?php
            } // END hesk_ticketsByDay
            ?>
        </div>
    </div>
</section>
<?php

require_once(HESK_PATH . 'inc/footer.inc.php');
exit();