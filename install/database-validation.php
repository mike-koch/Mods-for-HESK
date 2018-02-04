<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');

hesk_dbConnect();

/*
We have four possible validation scenarios:

1. Fresh install - the user has never installed Mods for HESK before. Nothing to validate.
2. Installed a really old version - we don't have a previous version to start from.
3. Installed a recent version, but before migrations began - just pull the version # and use the dictionary below.
4. Migration number present in the settings table. Take that number and run with it.
 */

$tableSql = hesk_dbQuery("SHOW TABLES LIKE '" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings'");
$startingValidationNumber = 1;
if (hesk_dbNumRows($tableSql) > 0) {
    // They have installed at LEAST to version 1.6.0. Just pull the version number OR migration number
    $migrationNumberSql = hesk_dbQuery("SELECT `Value` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` WHERE `Key` = 'migrationNumber'");
    if ($migrationRow = hesk_dbFetchAssoc($migrationNumberSql)) {
        $startingValidationNumber = intval($migrationRow['Value']) + 1;
    } else {
        $versionSql = hesk_dbQuery("SELECT `Value` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` WHERE `Key` = 'modsForHeskVersion'");
        $versionRow = hesk_dbFetchAssoc($versionSql);

        $migration_map = array(
            // Pre-1.4.0 to 1.5.0 did not have a settings table
            '1.6.0' =>  22, '1.6.1' =>  23, '1.7.0' =>  27, '2.0.0' =>  37, '2.0.1' =>  38, '2.1.0' =>  39, '2.1.1' =>  42,
            '2.2.0' =>  47, '2.2.1' =>  48, '2.3.0' =>  68, '2.3.1' =>  69, '2.3.2' =>  70, '2.4.0' =>  86, '2.4.1' =>  87,
            '2.4.2' =>  88, '2.5.0' =>  98, '2.5.1' =>  99, '2.5.2' => 100, '2.5.3' => 101, '2.5.4' => 102, '2.5.5' => 103,
            '2.6.0' => 121, '2.6.1' => 122, '2.6.2' => 125, '2.6.3' => 126, '2.6.4' => 127, '3.0.0 beta 1' => 130,
            '3.0.0 RC 1' => 131, '3.0.0' => 132, '3.0.1' => 133, '3.0.2' => 135, '3.0.3' => 136, '3.0.4' => 137,
            '3.0.5' => 138, '3.0.6' => 139, '3.0.7' => 140, '3.1.0' => 153, '3.1.1' => 154
        );
        $startingValidationNumber = $migration_map[$versionRow['Value']];
    }
} else {
    // migration # => sql for checking
    $versionChecks = array(
        // 1.5.0 -> users.active
        14 => "SHOW COLUMNS FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` LIKE 'active'",
        // 1.4.1 -> denied_emails
        11 => "SHOW TABLES LIKE '" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_emails'",
        // 1.4.0 -> denied ips
        9 => "SHOW TABLES LIKE '" . hesk_dbEscape($hesk_settings['db_pfix']) . "denied_ips'",
        // Pre-1.4.0 but still something -> statuses
        7 => "SHOW TABLES LIKE '" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses'"
    );

    foreach ($versionChecks as $migrationNumber => $sql) {
        $rs = hesk_dbQuery($sql);
        if (hesk_dbNumRows($rs) > 0) {
            $startingValidationNumber = $migrationNumber;
            break;
        }
    }
}
?>
<html>
<head>
    <title>Mods For HESK Database Validation</title>
    <link href="<?php echo HESK_PATH; ?>hesk_style.css?<?php echo HESK_NEW_VERSION; ?>" type="text/css" rel="stylesheet"/>
    <link href="<?php echo HESK_PATH; ?>css/bootstrap.css?v=<?php echo $hesk_settings['hesk_version']; ?>"
          type="text/css" rel="stylesheet"/>
    <link href="<?php echo HESK_PATH; ?>css/bootstrap-theme.css?v=<?php echo $hesk_settings['hesk_version']; ?>"
          type="text/css" rel="stylesheet"/>
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="<?php echo HESK_PATH; ?>css/hesk_newStyle.css" type="text/css" rel="stylesheet"/>
    <script src="<?php echo HESK_PATH; ?>js/jquery-1.10.2.min.js"></script>
    <script language="Javascript" type="text/javascript" src="<?php echo HESK_PATH; ?>js/bootstrap.min.js"></script>
    <script language="Javascript" type="text/javascript"
            src="<?php echo HESK_PATH; ?>js/modsForHesk-javascript.js"></script>
</head>
<body>
<div class="headersm">Mods for HESK Database Validation</div>
<div class="container">
    <div class="page-header">
        <h1>Mods for HESK Database Validation</h1>
        <p>The database validation tool will check your database setup to ensure that everything is set up correctly.
        As of this time, the database validator assumes you are running the latest version of Mods for HESK (<?php echo MODS_FOR_HESK_NEW_VERSION; ?>)</p>
    </div>
    <div class="panel panel-success" id="all-good" style="display: none">
        <div class="panel-heading">
            <h4>Success</h4>
        </div>
        <div class="panel-body text-center">
            <i class="fa fa-check-circle fa-4x" style="color: green"></i><br>
            <h4>Your database is valid</h4>
        </div>
    </div>
    <div class="panel panel-danger" id="not-good" style="display: none">
        <div class="panel-heading">
            <h4>Failure</h4>
        </div>
        <div class="panel-body text-center">
            <i class="fa fa-times-circle fa-4x" style="color: red"></i><br>
            <h4>One or more columns / tables are not properly configured in your database. Please open a topic at the
            <a href="https://developers.phpjunkyard.com/viewforum.php?f=19" target="_blank">PHP Junkyard Forums</a> with this information for assistance.</h4>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Results</h4>
        </div>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Database Change</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $validations = array();
            
            output_header_row('1.0.0 - 1.3.x');
            $validations[] = run_table_check('statuses', 7);
            $validations[] = run_column_check('statuses', 'ID', 7);
            $validations[] = run_column_check('statuses', 'TextColor', 7);
            $validations[] = run_column_check('statuses', 'IsNewTicketStatus', 7);
            $validations[] = run_column_check('statuses', 'IsClosed', 7);
            $validations[] = run_column_check('statuses', 'IsClosedByClient', 7);
            $validations[] = run_column_check('statuses', 'IsCustomerReplyStatus', 7);
            $validations[] = run_column_check('statuses', 'IsStaffClosedOption', 7);
            $validations[] = run_column_check('statuses', 'IsStaffReopenedStatus', 7);
            $validations[] = run_column_check('statuses', 'IsDefaultStaffReplyStatus', 7);
            $validations[] = run_column_check('statuses', 'LockedTicketStatus', 7);
            output_header_row('1.5.0');
            $validations[] = run_column_check('users', 'active', 11);
            output_header_row('1.6.0');
            $validations[] = run_column_check('users', 'notify_note_unassigned', 14);
            $validations[] = run_table_check('settings', 20);
            output_header_row('1.7.0');
            $validations[] = run_table_check('verified_emails', 23);
            $validations[] = run_table_check('pending_verification_emails', 24);
            $validations[] = run_table_check('stage_tickets', 25);
            output_header_row('2.2.0');
            $validations[] = run_column_check('statuses', 'IsAutocloseOption', 42);
            $validations[] = run_column_check('statuses', 'Closable', 44);
            output_header_row('2.3.0');
            $validations[] = run_column_check('service_messages', 'icon', 48);
            $validations[] = run_column_check('statuses', 'Key', 49);
            $validations[] = run_column_check('tickets', 'latitude', 53);
            $validations[] = run_column_check('tickets', 'longitude', 54);
            $validations[] = run_column_check('stage_tickets', 'latitude', 55);
            $validations[] = run_column_check('stage_tickets', 'longitude', 56);
            $validations[] = run_column_check('categories', 'manager', 57);
            $validations[] = run_column_check('users', 'permission_template', 62);
            $validations[] = run_table_check('permission_templates', 63);
            $validations[] = run_column_check('permission_templates', 'id', 63);
            $validations[] = run_column_check('permission_templates', 'name', 63);
            $validations[] = run_column_check('permission_templates', 'heskprivileges', 63);
            $validations[] = run_column_check('permission_templates', 'categories', 63);
            output_header_row('2.4.0');
            $validations[] = run_table_check('quick_help_sections', 70);
            $validations[] = run_column_check('quick_help_sections', 'id', 70);
            $validations[] = run_column_check('quick_help_sections', 'location', 70);
            $validations[] = run_column_check('quick_help_sections', 'show', 70);
            $validations[] = run_table_check('text_to_status_xref', 76);
            $validations[] = run_column_check('text_to_status_xref', 'id', 76);
            $validations[] = run_column_check('text_to_status_xref', 'language', 76);
            $validations[] = run_column_check('text_to_status_xref', 'text', 76);
            $validations[] = run_column_check('text_to_status_xref', 'status_id', 76);
            $validations[] = run_column_check('statuses', 'sort', 77);
            $validations[] = run_column_check('attachments', 'download_count', 80);
            $validations[] = run_column_check('kb_attachments', 'download_count', 81);
            $validations[] = run_column_check('tickets', 'html', 82);
            $validations[] = run_column_check('stage_tickets', 'html', 83);
            $validations[] = run_column_check('replies', 'html', 84);
            output_header_row('2.5.0');
            $validations[] = run_column_check('tickets', 'user_agent', 89);
            $validations[] = run_column_check('tickets', 'screen_resolution_width', 91);
            $validations[] = run_column_check('tickets', 'screen_resolution_height', 92);
            $validations[] = run_column_check('stage_tickets', 'user_agent', 90);
            $validations[] = run_column_check('stage_tickets', 'screen_resolution_width', 93);
            $validations[] = run_column_check('stage_tickets', 'screen_resolution_height', 94);
            $validations[] = run_setting_check('navbar_title_url', 96);
            output_header_row('2.6.0');
            $validations[] = run_table_check('logging', 105);
            $validations[] = run_column_check('logging', 'id', 105);
            $validations[] = run_column_check('logging', 'username', 105);
            $validations[] = run_column_check('logging', 'message', 105);
            $validations[] = run_column_check('logging', 'severity', 105);
            $validations[] = run_column_check('logging', 'location', 105);
            $validations[] = run_column_check('logging', 'timestamp', 105);
            $validations[] = run_table_check('user_api_tokens', 103);
            $validations[] = run_column_check('user_api_tokens', 'id', 103);
            $validations[] = run_column_check('user_api_tokens', 'user_id', 103);
            $validations[] = run_column_check('user_api_tokens', 'token', 103);
            $validations[] = run_setting_check('public_api', 104);
            $validations[] = run_table_check('temp_attachment', 106);
            $validations[] = run_column_check('temp_attachment', 'id', 106);
            $validations[] = run_column_check('temp_attachment', 'file_name', 106);
            $validations[] = run_column_check('temp_attachment', 'saved_name', 106);
            $validations[] = run_column_check('temp_attachment', 'size', 106);
            $validations[] = run_column_check('temp_attachment', 'type', 106);
            $validations[] = run_column_check('temp_attachment', 'date_uploaded', 106);
            $validations[] = run_table_check('calendar_event', 107);
            $validations[] = run_column_check('calendar_event', 'id', 107);
            $validations[] = run_column_check('calendar_event', 'start', 107);
            $validations[] = run_column_check('calendar_event', 'end', 107);
            $validations[] = run_column_check('calendar_event', 'all_day', 107);
            $validations[] = run_column_check('calendar_event', 'name', 107);
            $validations[] = run_column_check('calendar_event', 'location', 107);
            $validations[] = run_column_check('calendar_event', 'comments', 107);
            $validations[] = run_column_check('calendar_event', 'category', 107);
            $validations[] = run_table_check('calendar_event_reminder', 108);
            $validations[] = run_column_check('calendar_event_reminder', 'id', 108);
            $validations[] = run_column_check('calendar_event_reminder', 'user_id', 108);
            $validations[] = run_column_check('calendar_event_reminder', 'event_id', 108);
            $validations[] = run_column_check('calendar_event_reminder', 'amount', 108);
            $validations[] = run_column_check('calendar_event_reminder', 'unit', 108);
            $validations[] = run_column_check('calendar_event_reminder', 'email_sent', 108);
            $validations[] = run_column_check('tickets', 'due_date', 109);
            $validations[] = run_column_check('tickets', 'overdue_email_sent', 110);
            $validations[] = run_column_check('categories', 'usage', 112);
            $validations[] = run_column_check('users', 'notify_overdue_unassigned', 113);
            $validations[] = run_column_check('users', 'default_calendar_view', 114);
            output_header_row('2.6.2');
            $validations[] = run_column_check('stage_tickets', 'due_date');
            $validations[] = run_column_check('stage_tickets', 'overdue_email_sent');
            output_header_row('3.1.0');
            $validations[] = run_column_check('categories', 'background_color');
            $validations[] = run_column_check('categories', 'foreground_color');
            $validations[] = run_column_check('categories', 'display_border_outline');
            $validations[] = run_column_check('logging', 'stack_trace');
            $validations[] = run_table_check('custom_nav_element');
            $validations[] = run_column_check('custom_nav_element', 'id');
            $validations[] = run_column_check('custom_nav_element', 'image_url');
            $validations[] = run_column_check('custom_nav_element', 'font_icon');
            $validations[] = run_column_check('custom_nav_element', 'place');
            $validations[] = run_column_check('custom_nav_element', 'url');
            $validations[] = run_column_check('custom_nav_element', 'sort');
            $validations[] = run_table_check('custom_nav_element_to_text');
            $validations[] = run_column_check('custom_nav_element_to_text', 'id');
            $validations[] = run_column_check('custom_nav_element_to_text', 'nav_element_id');
            $validations[] = run_column_check('custom_nav_element_to_text', 'language');
            $validations[] = run_column_check('custom_nav_element_to_text', 'text');
            $validations[] = run_column_check('custom_nav_element_to_text', 'subtext');
            $validations[] = run_setting_check('admin_navbar_background');
            $validations[] = run_setting_check('admin_navbar_background_hover');
            $validations[] = run_setting_check('admin_navbar_text');
            $validations[] = run_setting_check('admin_navbar_text_hover');
            $validations[] = run_setting_check('admin_navbar_brand_background');
            $validations[] = run_setting_check('admin_navbar_brand_background_hover');
            $validations[] = run_setting_check('admin_navbar_brand_text');
            $validations[] = run_setting_check('admin_navbar_brand_text_hover');
            $validations[] = run_setting_check('admin_sidebar_background');
            $validations[] = run_setting_check('admin_sidebar_background_hover');
            $validations[] = run_setting_check('admin_sidebar_text');
            $validations[] = run_setting_check('admin_sidebar_text_hover');
            $validations[] = run_setting_check('admin_sidebar_font_weight');
            $validations[] = run_setting_check('admin_sidebar_header_background');
            $validations[] = run_setting_check('admin_sidebar_header_text');
            output_header_row('3.2.0');
            $validations[] = run_table_check('audit_trail');
            $validations[] = run_table_check('audit_trail_to_replacement_values');
            $validations[] = run_column_check('categories', 'mfh_description');
            $validations[] = run_column_check('custom_fields', 'mfh_description');
            $validations[] = run_setting_check('migrationNumber');
            output_header_row('3.3.0');
            $validations[] = run_table_check('mfh_calendar_business_hours');

            if ($checks) {
                echo "<script>$('#all-good').show()</script>";
            } else {
                echo "<script>$('#not-good').show()</script>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<?php
function run_setting_check($setting_name, $minimumValidationNumber) {
    global $hesk_settings, $startingValidationNumber;

    if ($startingValidationNumber < $minimumValidationNumber) {
        $checks = 'SKIPPED';
    } else {
        $res = run_check("SELECT 1 FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` WHERE `Key` = '{$setting_name}'");
        $checks = hesk_dbNumRows($res) > 0;
    }

    output_result('<b>Setting Exists</b>: ' . $setting_name, $checks);

    return $checks !== false;
}

function run_table_check($table_name, $minimumValidationNumber) {

    return run_column_check($table_name, '1', $minimumValidationNumber);
}

function run_column_check($table_name, $column_name, $minimumValidationNumber) {
    global $hesk_settings, $startingValidationNumber;

    if ($column_name == '1') {
        if ($startingValidationNumber < $minimumValidationNumber) {
            $checks = 'SKIPPED';
        } else {
            $checks = run_check('SELECT ' . $column_name . ' FROM `' . $hesk_settings['db_pfix'] . $table_name . '` LIMIT 1');
        }

        output_result('<b>Table Exists</b>: ' . $table_name,
            $checks);
    } else {
        if ($startingValidationNumber < $minimumValidationNumber) {
            $checks = 'SKIPPED';
        } else {
            $checks = run_check('SELECT `' . $column_name . '` FROM `' . $hesk_settings['db_pfix'] . $table_name . '` LIMIT 1');
        }

        output_result('<b>Column Exists</b>: ' . $table_name . '.' . $column_name,
            $checks);
    }

    return $checks !== false;
}

function run_check($sql) {
    global $hesk_last_query;
    global $hesk_db_link;
    if (function_exists('mysqli_connect')) {
        if (!$hesk_db_link && !hesk_dbConnect()) {
            return false;
        }
        $hesk_last_query = $sql;

        return @mysqli_query($hesk_db_link, $sql) ? 'PASS' : 'FAIL';
    } else {
        if (!$hesk_db_link && !hesk_dbConnect()) {
            return false;
        }
        $hesk_last_query = $sql;

        return $res = @mysql_query($sql, $hesk_db_link) ? 'PASS' : 'FAIL';
    }
}

function output_result($change_title, $status) {
    switch ($status) {
        case 'PASS':
            $css_color = 'success';
            $text = '<span data-toggle="tooltip" title="This looks good!"><i class="fa fa-check-circle"></i> Success</span>';
            break;
        case 'FAIL':
            $css_color = 'danger';
            $text = '<span data-toggle="tooltip" title="Oh no! Something isn\'t right."><i class="fa fa-times-circle"></i> Failure</span>';
            break;
        case 'SKIPPED':
            $css_color = 'default';
            $text = '<span data-toggle="tooltip" title="Skipped - You are not running a new enough version of Mods for HESK."><i class="fa-minus-circle"></i> Skipped</span>';
            break;
    }

    $formatted_text = sprintf('<tr class="'.$css_color.'"><td>%s</td><td style="color: %s">%s</td></tr>', $change_title, $css_color, $text);

    echo $formatted_text;
}

function output_header_row($text) {
    echo '<tr><td colspan="2" style="font-size: 1.2em"><i class="fa fa-chevron-right"></i> ' . $text . '</td></tr>';
}