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
define('IN_SCRIPT', 1);
define('HESK_PATH', '../');
define('PAGE_TITLE', 'ADMIN_REPORTS');
define('MFH_PAGE_LAYOUT', 'TOP_ONLY');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/reporting_functions.inc.php');
require(HESK_PATH . 'inc/status_functions.inc.php');
require(HESK_PATH . 'inc/mail_functions.inc.php');
require(HESK_PATH . 'inc/custom_fields.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

// Check permissions for this feature
hesk_checkPermission('can_export');
$modsForHesk_settings = mfh_getSettings();

// Just a delete file action?
$delete = hesk_GET('delete');
if (strlen($delete) && preg_match('/^hesk_export_[0-9_\-]+$/', $delete)) {
    hesk_unlink(HESK_PATH.$hesk_settings['cache_dir'].'/'.$delete.'.zip');
    hesk_process_messages($hesklang['fd'], hesk_verifyGoto(),'SUCCESS');
}

// Set default values
define('CALENDAR', 1);
define('MAIN_PAGE', 1);
define('LOAD_TABS', 1);

$selected = array(
    'w' => array(0 => '', 1 => ''),
    'time' => array(1 => '', 2 => '', 3 => '', 4 => '', 5 => '', 6 => '', 7 => '', 8 => '', 9 => '', 10 => '', 11 => '', 12 => ''),
);
$is_all_time = 0;

// Default this month to date
$date_from = date('Y-m-d', mktime(0, 0, 0, date("m"), 1, date("Y")));
$date_to = date('Y-m-d');
$input_datefrom = date('Y-m-d', strtotime('last month'));
$input_dateto = date('Y-m-d');

/* Date */
if (!empty($_GET['w'])) {
    $df = preg_replace('/[^0-9]/', '', hesk_GET('datefrom'));
    if (strlen($df) == 8) {
        $date_from = substr($df, 0, 4) . '-' . substr($df, 4, 2) . '-' . substr($df, 6, 2);
        $input_datefrom = $date_from;
    } else {
        $date_from = date('Y-m-d', strtotime('last month'));
    }

    $dt = preg_replace('/[^0-9]/', '', hesk_GET('dateto'));
    if (strlen($dt) == 8) {
        $date_to = substr($dt, 0, 4) . '-' . substr($dt, 4, 2) . '-' . substr($dt, 6, 2);
        $input_dateto = $date_to;
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

}

unset($tmp);

// Start SQL statement for selecting tickets
$sql = "SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE ";

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
$possible_status = array();
foreach ($statuses as $row) {
    $possible_status[$row['ID']] = mfh_getDisplayTextForStatusId($row['ID']);
}

$status = $possible_status;

foreach ($status as $k => $v) {
    if (empty($_GET['s' . $k])) {
        unset($status[$k]);
    }
}

// How many statuses are we pulling out of the database?
$allStatusCount = count($statuses);
$tmp = count($status);

// Do we need to search by status?
if ($tmp < $allStatusCount) {
    // If no statuses selected, show all
    if ($tmp == 0) {
        $status = $possible_status;
    } else {
        // Add to the SQL
        $sql .= " AND `status` IN ('" . implode("','", array_keys($status)) . "') ";
    }
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

// Prepare variables used in search and forms
require_once(HESK_PATH . 'inc/prepare_ticket_export.inc.php');

////////////////////////////////////////////////////////////////////////////////

// Can view tickets that are unassigned or assigned to others?
$can_view_ass_others = hesk_checkPermission('can_view_ass_others', 0);
$can_view_unassigned = hesk_checkPermission('can_view_unassigned', 0);

// Category options
$category_options = '';
$my_cat = array();
$orderBy = $modsForHesk_settings['category_order_column'];
$res2 = hesk_dbQuery("SELECT `id`, `name` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` WHERE " . hesk_myCategories('id') . " ORDER BY `" . $orderBy . "` ASC");
while ($row = hesk_dbFetchAssoc($res2)) {
    $my_cat[$row['id']] = hesk_msgToPlain($row['name'], 1);
    $row['name'] = (hesk_mb_strlen($row['name']) > 50) ? hesk_mb_substr($row['name'],0,50) . '...' : $row['name'];
    $cat_selected = ($row['id'] == $category) ? 'selected="selected"' : '';
    $category_options .= '<option value="' . $row['id'] . '" ' . $cat_selected . '>' . $row['name'] . '</option>';
}

// Generate export file
if (isset($_GET['w'])) {
    require_once(HESK_PATH . 'inc/export_functions.inc.php');
    list($success_msg, $tickets_exported) = hesk_export_to_XML($sql);
}

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>
<div class="content-wrapper">
    <section class="content">
    <div class="box">
        <div class="box-header">
            <h1 class="box-title">
                <?php echo $hesklang['export']; ?>
            </h1>
            <?php
            if (hesk_checkPermission('can_run_reports', 0)) {
                echo '<br><small><a href="reports.php">' . $hesklang['reports_tab'] . '</a></small>';
            }
            ?>
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

            // If an export was generated, show the link to download
            if (isset($success_msg)) {
                if ($tickets_exported > 0) {
                    hesk_show_success($success_msg);
                } else {
                    hesk_show_notice($hesklang['n2ex']);
                }
            }
            ?>
            <form name="showt" action="export.php" method="get" role="form">
                <div class="form-group">
                    <label for="time" class="control-label col-sm-2"><?php echo $hesklang['dtrg']; ?>:</label>

                    <div class="col-sm-10 form-inline">
                        <!-- START DATE -->
                        <input type="radio" name="w" value="0" id="w0" <?php echo $selected['w'][0]; ?> />
                        <select name="time" onclick="document.getElementById('w0').checked = true"
                                class="form-control"
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
                                (<?php echo $hesklang['m' . date('n', mktime(0, 0, 0, date('m') - 1, 1, date('Y')))]; ?>
                                )
                            </option>
                            <option value="5" <?php echo $selected['time'][5]; ?>><?php echo $hesklang['r5']; ?></option>
                            <option value="6" <?php echo $selected['time'][6]; ?>><?php echo $hesklang['r6']; ?></option>
                            <option value="7" <?php echo $selected['time'][7]; ?>><?php echo $hesklang['r7']; ?></option>
                            <option value="8" <?php echo $selected['time'][8]; ?>><?php echo $hesklang['r8']; ?></option>
                            <option value="9" <?php echo $selected['time'][9]; ?>><?php echo $hesklang['r9']; ?></option>
                            <option value="10" <?php echo $selected['time'][10]; ?>><?php echo $hesklang['r10']; ?>
                                (<?php echo date('Y'); ?>)
                            </option>
                            <option value="11" <?php echo $selected['time'][11]; ?>><?php echo $hesklang['r11']; ?>
                                (<?php echo date('Y', mktime(0, 0, 0, date('m'), date('d'), date('Y') - 1)); ?>)
                            </option>
                            <option value="12" <?php echo $selected['time'][12]; ?>><?php echo $hesklang['r12']; ?></option>
                        </select>

                        <br>

                        <input type="radio" name="w" value="1" id="w1" <?php echo $selected['w'][1]; ?> />
                        <?php echo $hesklang['from']; ?> <input type="text" name="datefrom"
                                                                value="<?php echo $input_datefrom; ?>" id="datefrom"
                                                                class="datepicker form-control" size="10"
                                                                onclick="document.getElementById('w1').checked = true"
                                                                onfocus="document.getElementById('w1').checked = true;this.focus;"/>
                        <?php echo $hesklang['to']; ?> <input type="text" name="dateto" value="<?php echo $input_dateto; ?>"
                                                              id="dateto" class="datepicker form-control" size="10"
                                                              onclick="document.getElementById('w1').checked = true"
                                                              onfocus="document.getElementById('w1').checked = true; this.focus;"/>
                        <!-- END DATE -->
                    </div>
                </div>
                <div class="form-group">
                    <label for="status" class="control-label col-sm-2"><?php echo $hesklang['status']; ?>:</label>

                    <div class="col-sm-10">
                        <?php
                        $statuses = mfh_getAllStatuses();
                        foreach ($statuses as $row) {
                            ?>
                            <div class="col-xs-4">
                                <div class="checkbox">
                                    <label><input type="checkbox" name="s<?php echo $row['ID']; ?>"
                                                  value="1" <?php if (isset($status[$row['ID']])) {
                                            echo 'checked="checked"';
                                        } ?> /> <span
                                            style="color: <?php echo $row['TextColor']; ?>"><?php echo $row['text']; ?></span></label>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="priority" class="col-sm-2 control-label"><?php echo $hesklang['priority']; ?>:</label>

                    <div class="col-sm-10">
                        <div class="col-xs-4">
                            <div class="checkbox">
                                <label><input type="checkbox" name="p0" value="1" <?php if (isset($priority[0])) {
                                        echo 'checked="checked"';
                                    } ?> /> <span class="critical"><?php echo $hesklang['critical']; ?></span></label>
                            </div>
                            <div class="checkbox">
                                <label><input type="checkbox" name="p1" value="1" <?php if (isset($priority[1])) {
                                        echo 'checked="checked"';
                                    } ?> /> <span class="important"><?php echo $hesklang['high']; ?></span></label>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <div class="checkbox">
                                <label><input type="checkbox" name="p2" value="1" <?php if (isset($priority[2])) {
                                        echo 'checked="checked"';
                                    } ?> /> <span class="medium"><?php echo $hesklang['medium']; ?></span></label>
                            </div>
                            <div class="checkbox">
                                <label><input type="checkbox" name="p3" value="1" <?php if (isset($priority[3])) {
                                        echo 'checked="checked"';
                                    } ?> /> <span class="normal"><?php echo $hesklang['low']; ?></span></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="assign" class="col-sm-2 control-label"><?php echo $hesklang['show']; ?>:</label>

                    <div class="col-sm-10">
                        <div class="col-xs-4">
                            <div class="checkbox">
                                <label><input type="checkbox" name="s_my"
                                              value="1" <?php if ($s_my[1]) echo 'checked="checked"'; ?> /> <?php echo $hesklang['s_my']; ?>
                                </label>
                            </div>
                            <?php
                            if ($can_view_unassigned) {
                                ?>
                                <div class="checkbox">
                                    <label><input type="checkbox" name="s_un"
                                                  value="1" <?php if ($s_un[1]) echo 'checked="checked"'; ?> /> <?php echo $hesklang['s_un']; ?>
                                    </label>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="col-xs-4">
                            <?php
                            if ($can_view_ass_others) {
                                ?>
                                <div class="checkbox">
                                    <label><input type="checkbox" name="s_ot"
                                                  value="1" <?php if ($s_ot[1]) echo 'checked="checked"'; ?> /> <?php echo $hesklang['s_ot']; ?>
                                    </label>
                                </div>
                                <?php
                            }
                            ?>
                            <div class="checkbox">
                                <label><input type="checkbox" name="archive"
                                              value="1" <?php if ($archive[1]) echo 'checked="checked"'; ?> /> <?php echo $hesklang['disp_only_archived']; ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="sort" class="col-sm-2 control-label"><?php echo $hesklang['sort_by']; ?>:</label>

                    <div class="col-sm-10">
                        <div class="col-xs-4">
                            <div class="radio">
                                <label><input type="radio" name="sort" value="priority" <?php if ($sort == 'priority') {
                                        echo 'checked="checked"';
                                    } ?> /> <?php echo $hesklang['priority']; ?></label>
                            </div>
                            <div class="radio">
                                <label><input type="radio" name="sort" value="lastchange" <?php if ($sort == 'lastchange') {
                                        echo 'checked="checked"';
                                    } ?> /> <?php echo $hesklang['last_update']; ?></label>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <div class="radio">
                                <label><input type="radio" name="sort" value="name" <?php if ($sort == 'name') {
                                        echo 'checked="checked"';
                                    } ?> /> <?php echo $hesklang['name']; ?></label>
                            </div>
                            <div class="radio">
                                <label><input type="radio" name="sort" value="subject" <?php if ($sort == 'subject') {
                                        echo 'checked="checked"';
                                    } ?> /> <?php echo $hesklang['subject']; ?></label>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <div class="radio">
                                <label><input type="radio" name="sort" value="status" <?php if ($sort == 'status') {
                                        echo 'checked="checked"';
                                    } ?> /> <?php echo $hesklang['status']; ?></label>
                            </div>
                            <div class="radio">
                                <label><input type="radio" name="sort" value="id" <?php if ($sort == 'id') {
                                        echo 'checked="checked"';
                                    } ?> /> <?php echo $hesklang['sequentially']; ?></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="asc" class="col-sm-2 control-label"><?php echo $hesklang['category']; ?>:</label>

                    <div class="col-sm-10">
                        <select name="category" class="form-control">
                            <option value="0"><?php echo $hesklang['any_cat']; ?></option>
                            <?php echo $category_options; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="asc" class="col-sm-2 control-label"><?php echo $hesklang['order']; ?>:</label>

                    <div class="col-sm-10">
                        <div class="col-xs-4">
                            <div class="radio">
                                <label><input type="radio" name="asc" value="1" <?php if ($asc) {
                                        echo 'checked="checked"';
                                    } ?> /> <?php echo $hesklang['ascending']; ?></label>
                            </div>
                            <div class="radio">
                                <label><input type="radio" name="asc" value="0" <?php if (!$asc) {
                                        echo 'checked="checked"';
                                    } ?> /> <?php echo $hesklang['descending']; ?></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <input type="submit" id="export" value="<?php echo $hesklang['export_btn']; ?>" class="btn btn-default"/>
                    <input type="hidden" name="cot" value="1"/>
                </div>
            </form>
        </div>
    </div>
</section>
</div>
<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();