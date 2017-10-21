<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');

hesk_dbConnect();
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
            $all_good = true;

            output_header_row('1.0.0 - 1.3.x');
            $all_good = run_table_check('statuses');
            $all_good &= run_column_check('statuses', 'ID');
            $all_good &= run_column_check('statuses', 'TextColor');
            $all_good &= run_column_check('statuses', 'IsNewTicketStatus');
            $all_good &= run_column_check('statuses', 'IsClosed');
            $all_good &= run_column_check('statuses', 'IsClosedByClient');
            $all_good &= run_column_check('statuses', 'IsCustomerReplyStatus');
            $all_good &= run_column_check('statuses', 'IsStaffClosedOption');
            $all_good &= run_column_check('statuses', 'IsStaffReopenedStatus');
            $all_good &= run_column_check('statuses', 'IsDefaultStaffReplyStatus');
            $all_good &= run_column_check('statuses', 'LockedTicketStatus');
            output_header_row('1.5.0');
            $all_good &= run_column_check('users', 'active');
            output_header_row('1.6.0');
            $all_good &= run_column_check('users', 'notify_note_unassigned');
            $all_good &= run_table_check('settings');
            output_header_row('1.7.0');
            $all_good &= run_table_check('verified_emails');
            $all_good &= run_table_check('pending_verification_emails');
            $all_good &= run_table_check('stage_tickets');
            output_header_row('2.2.0');
            $all_good &= run_column_check('statuses', 'IsAutocloseOption');
            $all_good &= run_column_check('statuses', 'Closable');
            output_header_row('2.3.0');
            $all_good &= run_column_check('service_messages', 'icon');
            $all_good &= run_column_check('statuses', 'Key');
            $all_good &= run_column_check('tickets', 'latitude');
            $all_good &= run_column_check('tickets', 'longitude');
            $all_good &= run_column_check('stage_tickets', 'latitude');
            $all_good &= run_column_check('stage_tickets', 'longitude');
            $all_good &= run_column_check('categories', 'manager');
            $all_good &= run_column_check('users', 'permission_template');
            $all_good &= run_table_check('permission_templates');
            $all_good &= run_column_check('permission_templates', 'id');
            $all_good &= run_column_check('permission_templates', 'name');
            $all_good &= run_column_check('permission_templates', 'heskprivileges');
            $all_good &= run_column_check('permission_templates', 'categories');
            output_header_row('2.4.0');
            $all_good &= run_table_check('quick_help_sections');
            $all_good &= run_column_check('quick_help_sections', 'id');
            $all_good &= run_column_check('quick_help_sections', 'location');
            $all_good &= run_column_check('quick_help_sections', 'show');
            $all_good &= run_table_check('text_to_status_xref');
            $all_good &= run_column_check('text_to_status_xref', 'id');
            $all_good &= run_column_check('text_to_status_xref', 'language');
            $all_good &= run_column_check('text_to_status_xref', 'text');
            $all_good &= run_column_check('text_to_status_xref', 'status_id');
            $all_good &= run_column_check('statuses', 'sort');
            $all_good &= run_column_check('attachments', 'download_count');
            $all_good &= run_column_check('kb_attachments', 'download_count');
            $all_good &= run_column_check('tickets', 'html');
            $all_good &= run_column_check('stage_tickets', 'html');
            $all_good &= run_column_check('replies', 'html');
            output_header_row('2.5.0');
            $all_good &= run_column_check('tickets', 'user_agent');
            $all_good &= run_column_check('tickets', 'screen_resolution_width');
            $all_good &= run_column_check('tickets', 'screen_resolution_height');
            $all_good &= run_column_check('stage_tickets', 'user_agent');
            $all_good &= run_column_check('stage_tickets', 'screen_resolution_width');
            $all_good &= run_column_check('stage_tickets', 'screen_resolution_height');
            output_header_row('2.6.0');
            $all_good &= run_table_check('logging');
            $all_good &= run_column_check('logging', 'id');
            $all_good &= run_column_check('logging', 'username');
            $all_good &= run_column_check('logging', 'message');
            $all_good &= run_column_check('logging', 'severity');
            $all_good &= run_column_check('logging', 'location');
            $all_good &= run_column_check('logging', 'timestamp');
            $all_good &= run_table_check('user_api_tokens');
            $all_good &= run_column_check('user_api_tokens', 'id');
            $all_good &= run_column_check('user_api_tokens', 'user_id');
            $all_good &= run_column_check('user_api_tokens', 'token');
            $all_good &= run_table_check('temp_attachment');
            $all_good &= run_column_check('temp_attachment', 'id');
            $all_good &= run_column_check('temp_attachment', 'file_name');
            $all_good &= run_column_check('temp_attachment', 'saved_name');
            $all_good &= run_column_check('temp_attachment', 'size');
            $all_good &= run_column_check('temp_attachment', 'type');
            $all_good &= run_column_check('temp_attachment', 'date_uploaded');
            $all_good &= run_table_check('calendar_event');
            $all_good &= run_column_check('calendar_event', 'id');
            $all_good &= run_column_check('calendar_event', 'start');
            $all_good &= run_column_check('calendar_event', 'end');
            $all_good &= run_column_check('calendar_event', 'all_day');
            $all_good &= run_column_check('calendar_event', 'name');
            $all_good &= run_column_check('calendar_event', 'location');
            $all_good &= run_column_check('calendar_event', 'comments');
            $all_good &= run_column_check('calendar_event', 'category');
            $all_good &= run_table_check('calendar_event_reminder');
            $all_good &= run_column_check('calendar_event_reminder', 'id');
            $all_good &= run_column_check('calendar_event_reminder', 'user_id');
            $all_good &= run_column_check('calendar_event_reminder', 'event_id');
            $all_good &= run_column_check('calendar_event_reminder', 'amount');
            $all_good &= run_column_check('calendar_event_reminder', 'unit');
            $all_good &= run_column_check('calendar_event_reminder', 'email_sent');
            $all_good &= run_column_check('tickets', 'due_date');
            $all_good &= run_column_check('tickets', 'overdue_email_sent');
            $all_good &= run_column_check('categories', 'usage');
            $all_good &= run_column_check('users', 'notify_overdue_unassigned');
            $all_good &= run_column_check('users', 'default_calendar_view');
            output_header_row('2.6.2');
            $all_good &= run_column_check('stage_tickets', 'due_date');
            $all_good &= run_column_check('stage_tickets', 'overdue_email_sent');
            output_header_row('3.1.0');
            $all_good &= run_column_check('categories', 'background_color');
            $all_good &= run_column_check('categories', 'foreground_color');
            $all_good &= run_column_check('categories', 'display_border_outline');
            $all_good &= run_column_check('logging', 'stack_trace');
            $all_good &= run_table_check('custom_nav_element');
            $all_good &= run_column_check('custom_nav_element', 'id');
            $all_good &= run_column_check('custom_nav_element', 'image_url');
            $all_good &= run_column_check('custom_nav_element', 'font_icon');
            $all_good &= run_column_check('custom_nav_element', 'place');
            $all_good &= run_column_check('custom_nav_element', 'url');
            $all_good &= run_column_check('custom_nav_element', 'sort');
            $all_good &= run_table_check('custom_nav_element_to_text');
            $all_good &= run_column_check('custom_nav_element_to_text', 'id');
            $all_good &= run_column_check('custom_nav_element_to_text', 'nav_element_id');
            $all_good &= run_column_check('custom_nav_element_to_text', 'language');
            $all_good &= run_column_check('custom_nav_element_to_text', 'text');
            $all_good &= run_column_check('custom_nav_element_to_text', 'subtext');
            $all_good &= run_setting_check('admin_navbar_background');
            $all_good &= run_setting_check('admin_navbar_background_hover');
            $all_good &= run_setting_check('admin_navbar_text');
            $all_good &= run_setting_check('admin_navbar_text_hover');
            $all_good &= run_setting_check('admin_navbar_brand_background');
            $all_good &= run_setting_check('admin_navbar_brand_background_hover');
            $all_good &= run_setting_check('admin_navbar_brand_text');
            $all_good &= run_setting_check('admin_navbar_brand_text_hover');
            $all_good &= run_setting_check('admin_sidebar_background');
            $all_good &= run_setting_check('admin_sidebar_background_hover');
            $all_good &= run_setting_check('admin_sidebar_text');
            $all_good &= run_setting_check('admin_sidebar_text_hover');
            $all_good &= run_setting_check('admin_sidebar_font_weight');
            $all_good &= run_setting_check('admin_sidebar_header_background');
            $all_good &= run_setting_check('admin_sidebar_header_text');
            output_header_row('3.2.0');
            $all_good &= run_table_check('audit_trail');
            $all_good &= run_table_check('audit_trail_to_replacement_values');
            $all_good &= run_column_check('categories', 'mfh_description');
            $all_good &= run_column_check('custom_fields', 'mfh_description');
            $all_good &= run_setting_check('migrationNumber');

            if ($all_good) {
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
function run_setting_check($setting_name) {
    global $hesk_settings;

    $res = run_check("SELECT 1 FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` WHERE `Key` = '{$setting_name}'");
	$all_good = hesk_dbNumRows($res) > 0;

    output_result('<b>Setting Exists</b>: ' . $setting_name, $all_good);

    return $all_good !== false;
}

function run_table_check($table_name) {
    return run_column_check($table_name, '1');
}

function run_column_check($table_name, $column_name) {
    global $hesk_settings;

    if ($column_name == '1') {
        $all_good = run_check('SELECT ' . $column_name . ' FROM `' . $hesk_settings['db_pfix'] . $table_name . '` LIMIT 1');

        output_result('<b>Table Exists</b>: ' . $table_name,
            $all_good);
    } else {
        $all_good = run_check('SELECT `' . $column_name . '` FROM `' . $hesk_settings['db_pfix'] . $table_name . '` LIMIT 1');
        output_result('<b>Column Exists</b>: ' . $table_name . '.' . $column_name,
            $all_good);
    }

    return $all_good !== false;
}

function run_check($sql) {
    global $hesk_last_query;
    global $hesk_db_link;
    if (function_exists('mysqli_connect')) {
        if (!$hesk_db_link && !hesk_dbConnect()) {
            return false;
        }
        $hesk_last_query = $sql;

        return @mysqli_query($hesk_db_link, $sql);
    } else {
        if (!$hesk_db_link && !hesk_dbConnect()) {
            return false;
        }
        $hesk_last_query = $sql;

        return $res = @mysql_query($sql, $hesk_db_link);
    }
}

function output_result($change_title, $success) {
    $css_color = 'success';
    $text = '<span data-toggle="tooltip" title="This looks good!"><i class="fa fa-check-circle"></i> Success</span>';
    if (!$success) {
        $css_color = 'danger';
        $text = '<span data-toggle="tooltip" title="Oh no! Something isn\'t right."><i class="fa fa-times-circle"></i> Failure</span>';
    }

    $formatted_text = sprintf('<tr class="'.$css_color.'"><td>%s</td><td style="color: %s">%s</td></tr>', $change_title, $css_color, $text);

    echo $formatted_text;
}

function output_header_row($text) {
    echo '<tr><td colspan="2" style="font-size: 1.2em"><i class="fa fa-chevron-right"></i> ' . $text . '</td></tr>';
}