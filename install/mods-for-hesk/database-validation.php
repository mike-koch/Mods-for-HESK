<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');

hesk_dbConnect();
?>
<html>
<head>
    <title>Mods For HESK Database Validation</title>
    <link href="../../hesk_style.css?<?php echo HESK_NEW_VERSION; ?>" type="text/css" rel="stylesheet"/>
    <link href="<?php echo HESK_PATH; ?>css/bootstrap.css?v=<?php echo $hesk_settings['hesk_version']; ?>"
          type="text/css" rel="stylesheet"/>
    <link href="<?php echo HESK_PATH; ?>css/bootstrap-theme.css?v=<?php echo $hesk_settings['hesk_version']; ?>"
          type="text/css" rel="stylesheet"/>
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="../../css/hesk_newStyle.css" type="text/css" rel="stylesheet"/>
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
        As of this time, the database validator assumes you are running the latest version of Mods for HESK (2.6.4)</p>
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
            output_header_row('1.0.0 - 1.3.x');
            run_table_check('statuses');
            run_column_check('statuses', '`ID`');
            run_column_check('statuses', '`TextColor`');
            run_column_check('statuses', '`IsNewTicketStatus`');
            run_column_check('statuses', '`IsClosed`');
            run_column_check('statuses', '`IsClosedByClient`');
            run_column_check('statuses', '`IsCustomerReplyStatus`');
            run_column_check('statuses', '`IsStaffClosedOption`');
            run_column_check('statuses', '`IsStaffReopenedStatus`');
            run_column_check('statuses', '`IsDefaultStaffReplyStatus`');
            run_column_check('statuses', '`LockedTicketStatus`');
            run_column_check('statuses', '`IsAutocloseOption`');
            run_column_check('statuses', '`Closable`');
            output_header_row('1.5.0');
            run_column_check('users', '`active`');
            run_column_check('users', '`notify_note_unassigned`');
            output_header_row('1.6.0');
            run_table_check('settings');
            output_header_row('1.7.0');
            run_table_check('verified_emails');
            run_table_check('pending_verification_emails');
            run_table_check('stage_tickets');
            output_header_row('2.3.0');
            run_column_check('service_messages', '`icon`');
            run_column_check('statuses', '`Key`');
            run_column_check('tickets', '`latitude`');
            run_column_check('tickets', '`longitude`');
            run_column_check('stage_tickets', '`latitude`');
            run_column_check('stage_tickets', '`longitude`');
            run_column_check('categories', '`manager`');
            run_column_check('users', '`permission_template`');
            run_table_check('permission_templates');
            run_column_check('permission_templates', '`id`');
            run_column_check('permission_templates', '`name`');
            run_column_check('permission_templates', '`heskprivileges`');
            run_column_check('permission_templates', '`categories`');
            output_header_row('2.4.0');
            run_table_check('quick_help_sections');
            run_column_check('quick_help_sections', '`id`');
            run_column_check('quick_help_sections', '`location`');
            run_column_check('quick_help_sections', '`show`');
            run_table_check('text_to_status_xref');
            run_column_check('text_to_status_xref', '`id`');
            run_column_check('text_to_status_xref', '`language`');
            run_column_check('text_to_status_xref', '`text`');
            run_column_check('text_to_status_xref', '`status_id`');
            run_column_check('statuses', '`sort`');
            run_column_check('attachments', '`download_count`');
            run_column_check('kb_attachments', '`download_count`');
            run_column_check('tickets', '`html`');
            run_column_check('stage_tickets', '`html`');
            run_column_check('replies', '`html`');
            output_header_row('2.5.0');
            run_column_check('tickets', '`user_agent`');
            run_column_check('tickets', '`screen_resolution_width`');
            run_column_check('tickets', '`screen_resolution_height`');
            run_column_check('stage_tickets', '`user_agent`');
            run_column_check('stage_tickets', '`screen_resolution_width`');
            run_column_check('stage_tickets', '`screen_resolution_height`');
            output_header_row('2.6.0');
            run_table_check('logging');
            run_column_check('logging', '`id`');
            run_column_check('logging', '`username`');
            run_column_check('logging', '`message`');
            run_column_check('logging', '`severity`');
            run_column_check('logging', '`location`');
            run_column_check('logging', '`timestamp`');
            run_table_check('user_api_tokens');
            run_column_check('user_api_tokens', '`id`');
            run_column_check('user_api_tokens', '`user_id`');
            run_column_check('user_api_tokens', '`token`');
            run_table_check('temp_attachment');
            run_column_check('temp_attachment', '`id`');
            run_column_check('temp_attachment', '`file_name`');
            run_column_check('temp_attachment', '`saved_name`');
            run_column_check('temp_attachment', '`size`');
            run_column_check('temp_attachment', '`type`');
            run_column_check('temp_attachment', '`date_uploaded`');
            run_table_check('calendar_event');
            run_column_check('calendar_event', '`id`');
            run_column_check('calendar_event', '`start`');
            run_column_check('calendar_event', '`end`');
            run_column_check('calendar_event', '`all_day`');
            run_column_check('calendar_event', '`name`');
            run_column_check('calendar_event', '`location`');
            run_column_check('calendar_event', '`comments`');
            run_column_check('calendar_event', '`category`');
            run_table_check('calendar_event_reminder');
            run_column_check('calendar_event_reminder', '`id`');
            run_column_check('calendar_event_reminder', '`user_id`');
            run_column_check('calendar_event_reminder', '`event_id`');
            run_column_check('calendar_event_reminder', '`amount`');
            run_column_check('calendar_event_reminder', '`unit`');
            run_column_check('calendar_event_reminder', '`email_sent`');
            run_column_check('tickets', '`due_date`');
            run_column_check('tickets', '`overdue_email_sent`');
            run_column_check('categories', '`color`');
            run_column_check('categories', '`usage`');
            run_column_check('users', '`notify_overdue_unassigned`');
            run_column_check('users', '`default_calendar_view`');
            output_header_row('2.6.2');
            run_column_check('stage_tickets', '`due_date`');
            run_column_check('stage_tickets', '`overdue_email_sent`');
            ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<?php
function run_table_check($table_name) {
    run_column_check($table_name, '1');
}

function run_column_check($table_name, $column_name) {
    global $hesk_settings;

    if ($column_name == '1') {
        output_result('<b>Table Exists</b>: ' . $table_name,
            run_check('SELECT ' . $column_name . ' FROM `' . $hesk_settings['db_pfix'] . $table_name . '` LIMIT 1'));
    } else {
        output_result('<b>Column Exists</b>: ' . $table_name . '.' . $column_name,
            run_check('SELECT ' . $column_name . ' FROM `' . $hesk_settings['db_pfix'] . $table_name . '` LIMIT 1'));
    }


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
    $css_color = 'green';
    $text = '<span data-toggle="tooltip" title="This looks good!"><i class="fa fa-check-circle"></i> Success</span>';
    if (!$success) {
        $css_color = 'red';
        $text = '<span data-toggle="tooltip" title="Oh no! Something isn\'t right."><i class="fa fa-times-circle"></i> Failure</span>';
    }

    $formatted_text = sprintf('<tr><td>%s</td><td style="color: %s">%s</td></tr>', $change_title, $css_color, $text);

    echo $formatted_text;
}

function output_header_row($text) {
    echo '<tr><td colspan="2" style="font-size: 1.2em"><i class="fa fa-chevron-right"></i> ' . $text . '</td></tr>';
}