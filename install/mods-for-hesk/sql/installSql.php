<?php
require(HESK_PATH . 'hesk_settings.inc.php');

function executeQuery($sql)
{
    global $hesk_last_query;
    global $hesk_db_link;
    if (function_exists('mysqli_connect')) {

        if (!$hesk_db_link && !hesk_dbConnect()) {
            return false;
        }

        $hesk_last_query = $sql;

        if ($res = @mysqli_query($hesk_db_link, $sql)) {
            return $res;
        } else {
            http_response_code(500);
            print "Could not execute query: $sql. MySQL said: " . mysqli_error($hesk_db_link);
            die();
        }
    } else {
        if (!$hesk_db_link && !hesk_dbConnect()) {
            return false;
        }

        $hesk_last_query = $sql;

        if ($res = @mysql_query($sql, $hesk_db_link)) {
            return $res;
        } else {
            http_response_code(500);
            print "Could not execute query: $sql. MySQL said: " . mysql_error();
            die();
        }
    }
}

// END Version 2.3.2

// BEGIN Version 2.4.0
function execute240Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();


    // Setup status improvement tables
    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "text_to_status_xref` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `language` VARCHAR(200) NOT NULL,
      `text` VARCHAR(200) NOT NULL,
      `status_id` INT NOT NULL) ENGINE = MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ADD COLUMN `sort` INT");
    $statusesRs = executeQuery("SELECT `ID` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ORDER BY `ID` ASC");
    $i = 10;
    while ($myStatus = hesk_dbFetchAssoc($statusesRs)) {
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` SET `sort`=" . intval($i) . "
            WHERE `id`='" . intval($myStatus['ID']) . "' LIMIT 1");
        $i += 10;
    }

    // Process attachment improvement tables
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` ADD COLUMN `download_count` INT NOT NULL DEFAULT 0");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_attachments` ADD COLUMN `download_count` INT NOT NULL DEFAULT 0");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `html` ENUM('0','1') NOT NULL DEFAULT '0'");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` ADD COLUMN `html` ENUM('0','1') NOT NULL DEFAULT '0'");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` ADD COLUMN `html` ENUM('0','1') NOT NULL DEFAULT '0'");

    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.4.0' WHERE `Key` = 'modsForHeskVersion'");
}

function initializeXrefTable()
{
    global $hesk_settings, $hesklang;

    hesk_dbConnect();
    $languages = array();
    foreach ($hesk_settings['languages'] as $key => $value) {
        $languages[$key] = $hesk_settings['languages'][$key]['folder'];
    }

    $statusesRs = executeQuery("SELECT `ID`, `Key` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses`");
    $oldSetting = $hesk_settings['can_sel_lang'];
    $hesk_settings['can_sel_lang'] = 1;
    while ($row = hesk_dbFetchAssoc($statusesRs)) {
        foreach ($languages as $language => $languageCode) {
            hesk_setLanguage($language);
            $sql = "INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "text_to_status_xref` (`language`, `text`, `status_id`)
                VALUES ('" . hesk_dbEscape($language) . "', '" . hesk_dbEscape($hesklang[$row['Key']]) . "', " . intval($row['ID']) . ")";
            executeQuery($sql);
        }
    }
    $hesk_settings['can_sel_lang'] = $oldSetting;
    hesk_resetLanguage();
}
// END Version 2.4.0

// BEGIN Version 2.4.1
function execute241Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();

    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.4.1' WHERE `Key` = 'modsForHeskVersion'");
}
// END Version 2.4.1

// Version 2.4.2
function execute242Scripts()
{
    global $hesk_settings;

    hesk_dbConnect();

    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.4.2' WHERE `Key` = 'modsForHeskVersion'");
}

// BEGIN Version 2.5.0
function migrateSettings()
{
    global $hesk_settings;

    hesk_dbConnect();

    $modsForHesk_settings = array();
    if (file_exists(HESK_PATH . 'modsForHesk_settings.inc.php')) {
        require_once(HESK_PATH . 'modsForHesk_settings.inc.php');
    }

    $rtl = getSettingValue($modsForHesk_settings, 'rtl', 0);
    $show_icons = getSettingValue($modsForHesk_settings, 'show_icons', 0);
    $custom_field_setting = getSettingValue($modsForHesk_settings, 'custom_field_setting', 0);
    $customer_email_verification_required = getSettingValue($modsForHesk_settings, 'customer_email_verification_required', 0);
    $html_emails = getSettingValue($modsForHesk_settings, 'html_emails', 1);
    $use_mailgun = getSettingValue($modsForHesk_settings, 'use_mailgun', 0);
    $mailgun_api_key = getSettingValue($modsForHesk_settings, 'mailgun_api_key', '');
    $mailgun_domain = getSettingValue($modsForHesk_settings, 'mailgun_domain', '');
    $use_bootstrap_theme = getSettingValue($modsForHesk_settings, 'use_bootstrap_theme', 1);
    $new_kb_article_visibility = getSettingValue($modsForHesk_settings, 'new_kb_article_visibility', 0);
    $attachments = getSettingValue($modsForHesk_settings, 'attachments', 0);
    $show_number_merged = getSettingValue($modsForHesk_settings, 'show_number_merged', 1);
    $request_location = getSettingValue($modsForHesk_settings, 'request_location', 0);
    $category_order_column = getSettingValue($modsForHesk_settings, 'category_order_column', 'cat_order');
    $rich_text_for_tickets = getSettingValue($modsForHesk_settings, 'rich_text_for_tickets', 0);
    $statuses_order_column = getSettingValue($modsForHesk_settings, 'statuses_order_column', 'sort');
    $kb_attach_dir = getSettingValue($modsForHesk_settings, 'kb_attach_dir', 'attachments');
    $rich_text_for_tickets_for_customers = getSettingValue($modsForHesk_settings, 'rich_text_for_tickets_for_customers', 0);

    $navbar_background_color = getSettingValue($modsForHesk_settings, 'navbarBackgroundColor', '#414a5c');
    $navbar_brand_color = getSettingValue($modsForHesk_settings, 'navbarBrandColor', '#d4dee7');
    $navbar_brand_hover_color = getSettingValue($modsForHesk_settings, 'navbarBrandHoverColor', '#ffffff');
    $navbar_item_text_color = getSettingValue($modsForHesk_settings, 'navbarItemTextColor', '#d4dee7');
    $navbar_item_text_hover_color = getSettingValue($modsForHesk_settings, 'navbarItemTextHoverColor', '#ffffff');
    $navbar_item_text_selected_color = getSettingValue($modsForHesk_settings, 'navbarItemTextSelectedColor', '#ffffff');
    $navbar_item_selected_background_color = getSettingValue($modsForHesk_settings, 'navbarItemSelectedBackgroundColor', '#2d3646');
    $dropdown_item_text_color = getSettingValue($modsForHesk_settings, 'dropdownItemTextColor', '#333333');
    $dropdown_item_text_hover_color = getSettingValue($modsForHesk_settings, 'dropdownItemTextHoverColor', '#262626');
    $dropdown_item_text_hover_background_color = getSettingValue($modsForHesk_settings, 'dropdownItemTextHoverBackgroundColor', '#f5f5f5');
    $question_mark_color = getSettingValue($modsForHesk_settings, 'questionMarkColor', '#000000');


    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('rtl', " . intval($rtl) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('show_icons', " . intval($show_icons) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('custom_field_setting', " . intval($custom_field_setting) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('customer_email_verification_required', " . intval($customer_email_verification_required) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('html_emails', " . intval($html_emails) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('use_mailgun', " . intval($use_mailgun) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('mailgun_api_key', '" . hesk_dbEscape($mailgun_api_key) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('mailgun_domain', '" . hesk_dbEscape($mailgun_domain) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('use_bootstrap_theme', " . intval($use_bootstrap_theme) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('new_kb_article_visibility', " . intval($new_kb_article_visibility) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('attachments', " . intval($attachments) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('show_number_merged', " . intval($show_number_merged) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('request_location', " . intval($request_location) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('category_order_column', '" . hesk_dbEscape($category_order_column) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('rich_text_for_tickets', " . intval($rich_text_for_tickets) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('statuses_order_column', '" . hesk_dbEscape($statuses_order_column) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('kb_attach_dir', '" . hesk_dbEscape($kb_attach_dir) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('rich_text_for_tickets_for_customers', " . intval($rich_text_for_tickets_for_customers) . ")");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarBackgroundColor', '" . hesk_dbEscape($navbar_background_color) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarBrandColor', '" . hesk_dbEscape($navbar_brand_color) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarBrandHoverColor', '" . hesk_dbEscape($navbar_brand_hover_color) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarItemTextColor', '" . hesk_dbEscape($navbar_item_text_color) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarItemTextHoverColor', '" . hesk_dbEscape($navbar_item_text_hover_color) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarItemTextSelectedColor', '" . hesk_dbEscape($navbar_item_text_selected_color) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarItemSelectedBackgroundColor', '" . hesk_dbEscape($navbar_item_selected_background_color) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('dropdownItemTextColor', '" . hesk_dbEscape($dropdown_item_text_color) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('dropdownItemTextHoverColor', '" . hesk_dbEscape($dropdown_item_text_hover_color) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('dropdownItemTextHoverBackgroundColor', '" . hesk_dbEscape($dropdown_item_text_hover_background_color) . "')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('questionMarkColor', '" . hesk_dbEscape($question_mark_color) . "')");
}

function getSettingValue($settings, $setting, $default)
{
    return isset($settings[$setting]) ? $settings[$setting] : $default;
}

function execute250Scripts()
{
    global $hesk_settings;

    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `user_agent` TEXT");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `screen_resolution_width` INT");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `screen_resolution_height` INT");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` ADD COLUMN `user_agent` TEXT");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` ADD COLUMN `screen_resolution_width` INT");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` ADD COLUMN `screen_resolution_height` INT");

    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('display_user_agent_information', '0')");

    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('navbar_title_url', '" . hesk_dbEscape($hesk_settings['hesk_url']) . "')");

    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.5.0' WHERE `Key` = 'modsForHeskVersion'");
}
// END Version 2.5.0

// Version 2.5.1
function execute251Scripts()
{
    global $hesk_settings;
    hesk_dbConnect();

    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.5.1' WHERE `Key` = 'modsForHeskVersion'");
}

// Version 2.5.2
function execute252Scripts()
{
    global $hesk_settings;
    hesk_dbConnect();

    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.5.2' WHERE `Key` = 'modsForHeskVersion'");
}

// Version 2.5.3
function execute253Scripts() 
{
	global $hesk_settings;
	hesk_dbConnect();
	
    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.5.3' WHERE `Key` = 'modsForHeskVersion'");
}

// Version 2.5.4
function execute254Scripts()
{
    global $hesk_settings;
    hesk_dbConnect();

    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '2.5.4' WHERE `Key` = 'modsForHeskVersion'");
}

// Version 2.5.5
function execute255Scripts()
{
    updateVersion('2.5.5');
}

function updateVersion($version) {
    global $hesk_settings;

    hesk_dbConnect();

    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '{$version}' WHERE `Key` = 'modsForHeskVersion'");
}

// Version 2.6.0
function execute260Scripts()
{
    global $hesk_settings;
    hesk_dbConnect();
	
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('public_api', '0')");
	executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "logging` (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(200),
        `message` MEDIUMTEXT NOT NULL,
        `severity` INT NOT NULL,
        `location` MEDIUMTEXT,
        `timestamp` TIMESTAMP NOT NULL) ENGINE = MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "user_api_tokens` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT NOT NULL,
      `token` VARCHAR(500) NOT NULL) ENGINE = MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "temp_attachment` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `file_name` VARCHAR(255) NOT NULL,
      `saved_name` VARCHAR(255) NOT NULL,
      `size` INT(10) UNSIGNED NOT NULL,
      `type` ENUM('0','1') NOT NULL,
      `date_uploaded` TIMESTAMP NOT NULL) ENGINE = MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `start` DATETIME,
      `end` DATETIME,
      `all_day` ENUM('0','1') NOT NULL,
      `name` VARCHAR(255) NOT NULL,
      `location` VARCHAR(255),
      `comments` MEDIUMTEXT,
      `category` INT NOT NULL) ENGINE = MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "calendar_event_reminder` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT NOT NULL,
      `event_id` INT NOT NULL,
      `amount` INT NOT NULL,
      `unit` INT NOT NULL,
      `email_sent` ENUM('0', '1') NOT NULL DEFAULT '0') ENGINE = MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `due_date` DATETIME");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` ADD COLUMN `overdue_email_sent` ENUM('0','1')");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ADD COLUMN `color` VARCHAR(7)");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ADD COLUMN `usage` INT NOT NULL DEFAULT 0");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `notify_overdue_unassigned` ENUM('0', '1') NOT NULL DEFAULT '0' AFTER `notify_note_unassigned`");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ADD COLUMN `default_calendar_view` INT NOT NULL DEFAULT '0' AFTER `notify_note_unassigned`");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('enable_calendar', '1')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('first_day_of_week', '0')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('default_calendar_view', 'month')");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` ADD PRIMARY KEY ( `Key` )");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` CHANGE  `IsNewTicketStatus`  `IsNewTicketStatus` INT( 1 ) NOT NULL DEFAULT  '0',
            CHANGE  `IsClosed`  `IsClosed` INT( 1 ) NOT NULL DEFAULT  '0',
            CHANGE  `IsClosedByClient`  `IsClosedByClient` INT( 1 ) NOT NULL DEFAULT  '0',
            CHANGE  `IsCustomerReplyStatus`  `IsCustomerReplyStatus` INT( 1 ) NOT NULL DEFAULT  '0',
            CHANGE  `IsStaffClosedOption`  `IsStaffClosedOption` INT( 1 ) NOT NULL DEFAULT  '0',
            CHANGE  `IsStaffReopenedStatus`  `IsStaffReopenedStatus` INT( 1 ) NOT NULL DEFAULT  '0',
            CHANGE  `IsDefaultStaffReplyStatus`  `IsDefaultStaffReplyStatus` INT( 1 ) NOT NULL DEFAULT  '0',
            CHANGE  `LockedTicketStatus`  `LockedTicketStatus` INT( 1 ) NOT NULL DEFAULT  '0'");

    updateVersion('2.6.0');
}

// Version 2.6.1
function execute261Scripts() {
    updateVersion('2.6.1');
}

// Version 2.6.2
function execute262Scripts() {
    global $hesk_settings;
    hesk_dbConnect();

    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` ADD COLUMN `due_date` DATETIME");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets` ADD COLUMN `overdue_email_sent` ENUM('0','1')");

    updateVersion('2.6.2');
}

// Version 2.6.3
function execute263Scripts() {
    global $hesk_settings;
    hesk_dbConnect();

    updateVersion('2.6.3');
}

// Version 2.6.4
function execute264Scripts() {
    global $hesk_settings;
    hesk_dbConnect();

    updateVersion('2.6.4');
}

// Verison 3.0.0 Beta 1
function execute300Beta1Scripts() {
    global $hesk_settings;
    hesk_dbConnect();

    $hesk_statuses = executeQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_statuses` ORDER BY `order`");

    $next_status_id_rs = executeQuery("SELECT MAX(`ID`) AS `last_id` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses`");
    $next_status_id_row = hesk_dbFetchAssoc($next_status_id_rs);
    $next_status_id = intval($next_status_id_row['last_id']) + 1;

    $next_sort_rs = executeQuery("SELECT MAX(`sort`) AS `last_sort` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses`");
    $next_sort_row = hesk_dbFetchAssoc($next_sort_rs);
    $next_sort = intval($next_sort_row['last_sort']) + 10;

    while ($row = hesk_dbFetchAssoc($hesk_statuses)) {
        $closable = $row['can_customers_change'] == '1' ? 'yes' : 'sonly';

        executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` (`ID`,
        `TextColor`,
        `IsNewTicketStatus`,
        `IsClosed`,
        `IsClosedByClient`,
        `IsCustomerReplyStatus`,
        `IsStaffClosedOption`,
        `IsStaffReopenedStatus`,
        `IsDefaultStaffReplyStatus`,
        `LockedTicketStatus`,
        `IsAutocloseOption`,
        `Closable`,
        `Key`,
        `sort`)
        VALUES (" . $next_status_id . ",
        '#" . $row['color'] . "',
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        '" . $closable . "',
        'STORED IN XREF TABLE',
        " . $next_sort . ")");

        $languages = json_decode($row['name']);
        foreach ($languages as $language => $text) {
            executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "text_to_status_xref` (`language`, `text`, `status_id`)
            VALUES ('" . $language . "', '" . $text . "', " . $next_status_id . ")");
        }

        // Increment the next ID and sort
        $next_status_id++;
        $next_sort += 10;
    }

    // Migrate user's autorefresh columns to the new autoreload column
    // Mods for HESK is in millis; HESK is in seconds.
    executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `autoreload` = `autorefresh` / 10");

    // Add the admin_color_scheme setting
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('admin_color_scheme', 'skin-blue')");

    updateVersion('3.0.0 beta 1');
}

function execute300RC1Scripts() {
    global $hesk_settings;
    hesk_dbConnect();

    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` DROP COLUMN `autorefresh`");

    updateVersion('3.0.0 RC 1');
}

function execute300Scripts() {
    global $hesk_settings;
    hesk_dbConnect();

    updateVersion('3.0.0');
}

function execute301Scripts() {
    global $hesk_settings;
    hesk_dbConnect();

    updateVersion('3.0.1');
}

function execute302Scripts() {
    global $hesk_settings;
    hesk_dbConnect();

    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets`
        ADD COLUMN `custom21` MEDIUMTEXT,
        ADD COLUMN `custom22` MEDIUMTEXT,
        ADD COLUMN `custom23` MEDIUMTEXT,
        ADD COLUMN `custom24` MEDIUMTEXT,
        ADD COLUMN `custom25` MEDIUMTEXT,
        ADD COLUMN `custom26` MEDIUMTEXT,
        ADD COLUMN `custom27` MEDIUMTEXT,
        ADD COLUMN `custom28` MEDIUMTEXT,
        ADD COLUMN `custom29` MEDIUMTEXT,
        ADD COLUMN `custom30` MEDIUMTEXT,
        ADD COLUMN `custom31` MEDIUMTEXT,
        ADD COLUMN `custom32` MEDIUMTEXT,
        ADD COLUMN `custom33` MEDIUMTEXT,
        ADD COLUMN `custom34` MEDIUMTEXT,
        ADD COLUMN `custom35` MEDIUMTEXT,
        ADD COLUMN `custom36` MEDIUMTEXT,
        ADD COLUMN `custom37` MEDIUMTEXT,
        ADD COLUMN `custom38` MEDIUMTEXT,
        ADD COLUMN `custom39` MEDIUMTEXT,
        ADD COLUMN `custom40` MEDIUMTEXT,
        ADD COLUMN `custom41` MEDIUMTEXT,
        ADD COLUMN `custom42` MEDIUMTEXT,
        ADD COLUMN `custom43` MEDIUMTEXT,
        ADD COLUMN `custom44` MEDIUMTEXT,
        ADD COLUMN `custom45` MEDIUMTEXT,
        ADD COLUMN `custom46` MEDIUMTEXT,
        ADD COLUMN `custom47` MEDIUMTEXT,
        ADD COLUMN `custom48` MEDIUMTEXT,
        ADD COLUMN `custom49` MEDIUMTEXT,
        ADD COLUMN `custom50` MEDIUMTEXT");

    updateVersion('3.0.2');
}

function execute303Scripts() {
    global $hesk_settings;
    hesk_dbConnect();

    updateVersion('3.0.3');
}

function execute304Scripts() {
    global $hesk_settings;
    hesk_dbConnect();

    updateVersion('3.0.4');
}

function execute305Scripts() {
    global $hesk_settings;
    hesk_dbConnect();

    updateVersion('3.0.5');
}

function execute306Scripts() {
    global $hesk_settings;
    hesk_dbConnect();

    updateVersion('3.0.6');
}

function execute307Scripts() {
    global $hesk_settings;
    hesk_dbConnect();

    updateVersion('3.0.7');
}

function execute310Scripts() {
    global $hesk_settings;
    hesk_dbConnect();

    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "logging` ADD COLUMN `stack_trace` TEXT");
    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_nav_element` 
        (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
         image_url TEXT,
         font_icon VARCHAR(200),
         place INT NOT NULL,
         url VARCHAR(500) NOT NULL,
         sort INT NOT NULL)");
    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_nav_element_to_text`
        (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
         nav_element_id INT NOT NULL,
         language VARCHAR(200) NOT NULL,
         text VARCHAR(200) NOT NULL,
         subtext VARCHAR(200))");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ADD COLUMN `foreground_color` VARCHAR(7) NOT NULL DEFAULT 'AUTO'");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ADD COLUMN `display_border_outline` ENUM('0','1') NOT NULL DEFAULT '0'");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` CHANGE `color` `background_color` VARCHAR(7) NOT NULL DEFAULT '#FFFFFF'");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('login_background_type', 'color')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('login_background', '#d2d6de')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('login_box_header', 'helpdesk-title')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('login_box_header_image', '')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) VALUES ('api_url_rewrite', '0')");

    // Copy over color presets to the custom values
    $theme_preset_rs = executeQuery("SELECT `Value` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` WHERE `Key` = 'admin_color_scheme'");
    if (hesk_dbNumRows($theme_preset_rs) === 0) {
        $theme = 'skin-blue';
    } else {
        $theme_preset_row = hesk_dbFetchAssoc($theme_preset_rs);
        $theme = $theme_preset_row['Value'];
    }

    $light_theme = preg_match('/.*-light/g', $theme);
    $navbar = array(
        'background' => '',
        'text' => '#fff',
        'text_hover' => '#fff',
        'background_hover' => ''
    );
    $navbar_brand = array(
        'background' => '',
        'text' => '#fff',
        'text_hover' => '#fff',
        'background_hover' => ''
    );
    $sidebar = array(
        'background' => $light_theme ? '#f9fafc' : '#222d32',
        'text' => $light_theme ? '#444' : '#b8c7ce',
        'text_hover' => $light_theme ? '#444' : '#fff',
        'background_hover' => $light_theme ? '#f4f4f5' : '#1e282c',
        'font_weight' => $light_theme ? 'bold' : 'normal'
    );
    $sidebar_header = array(
        'background' => $light_theme ? '#f9fafc' : '#1a2226',
        'text' => $light_theme ? '#848484' : '#4b646f',
    );
    if (preg_match('/skin-blue.*/', $theme)) {
        $navbar['background'] = '#3c8dbc';
        $navbar['background_hover'] = '#367fa9';

        $navbar_brand['background'] = $light_theme ? '#3c8dbc' : '#367fa9';
        $navbar_brand['background_hover'] = $light_theme ? '#3b8ab8' : '#357ca5';
    } elseif (preg_match('/skin-yellow.*/', $theme)) {
        $navbar['background'] = '#f39c12';
        $navbar['background_hover'] = '#da8c10';

        $navbar_brand['background'] = $light_theme ? '#f39c12' : '#e08e0b';
        $navbar_brand['background_hover'] = $light_theme ? '#f39a0d' : '#db8b0b';
    } elseif (preg_match('/skin-green.*/', $theme)) {
        $navbar['background'] = '#00a65a';
        $navbar['background_hover'] = '#009551';

        $navbar_brand['background'] = $light_theme ? '#00a65a' : '#008d4c';
        $navbar_brand['background_hover'] = $light_theme ? '#00a157' : '#008749';
    } elseif (preg_match('/skin-purple.*/', $theme)) {
        $navbar['background'] = '#605ca8';
        $navbar['background_hover'] = '#565397';

        $navbar_brand['background'] = $light_theme ? '#605ca8' : '#555299';
        $navbar_brand['background_hover'] = $light_theme ? '#5d59a6' : '#545096';
    } elseif (preg_match('/skin-red.*/', $theme)) {
        $navbar['background'] = '#dd4b39';
        $navbar['background_hover'] = '#c64333';

        $navbar_brand['background'] = $light_theme ? '#dd4b39' : '#d73925';
        $navbar_brand['background_hover'] = $light_theme ? '#dc4735' : '#d33724';
    } else {
        $navbar['background'] = '#fff';
        $navbar['background_hover'] = '#eee';
        $navbar['text_color'] = '#333';
        $navbar['text_hover'] = '#333';

        $navbar_brand['background'] = '#fff';
        $navbar_brand['background_hover'] = '#fcfcfc';
        $navbar_brand['text'] = '#333';
        $navbar_brand['text_hover'] = '#333';
    }

    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
        VALUES ('admin_navbar_background', '{$navbar['background']}')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
        VALUES ('admin_navbar_background_hover', '{$navbar['background_hover']}')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
        VALUES ('admin_navbar_text', '{$navbar['text']}')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
        VALUES ('admin_navbar_text_hover', '{$navbar['text_hover']}')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
        VALUES ('admin_navbar_brand_background', '{$navbar_brand['background']}')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
        VALUES ('admin_navbar_brand_background_hover', '{$navbar_brand['background_hover']}')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
        VALUES ('admin_navbar_brand_text', '{$navbar_brand['text']}')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
        VALUES ('admin_navbar_brand_text_hover', '{$navbar_brand['text_hover']}')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
        VALUES ('admin_sidebar_background', '{$sidebar['background']}')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
        VALUES ('admin_sidebar_background_hover', '{$sidebar['background_hover']}')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
        VALUES ('admin_sidebar_text', '{$sidebar['text']}')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
        VALUES ('admin_sidebar_text_hover', '{$sidebar['text_hover']}')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
        VALUES ('admin_sidebar_font_weight', '{$sidebar['font_weight']}')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
        VALUES ('admin_sidebar_header_background', '{$sidebar_header['background']}')");
    executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
        VALUES ('admin_sidebar_header_text', '{$sidebar_header['text']}')");
    executeQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` WHERE `Key` IN ('rtl', 'admin_color_scheme')");

    updateVersion('3.1.0');
}

function execute311Scripts() {
    global $hesk_settings;
    hesk_dbConnect();

    updateVersion('3.1.1');
}

function execute320Scripts() {
    global $hesk_settings;
    hesk_dbConnect();

    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories`
        ADD COLUMN `mfh_description` TEXT");
    executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_fields`
        ADD COLUMN `mfh_description` TEXT");

    // Purge the custom field caches as we're adding a new field
    foreach ($hesk_settings['languages'] as $key => $value) {
        $language_hash = sha1($key);
        hesk_unlink(HESK_PATH . "cache/cf_{$language_hash}.cache.php");
    }

    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "audit_trail` (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
        `entity_id` INT NOT NULL,
        `entity_type` VARCHAR(50) NOT NULL,
        `language_key` VARCHAR(100) NOT NULL, 
        `date` TIMESTAMP NOT NULL)");
    executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "audit_trail_to_replacement_values` (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
        `audit_trail_id` INT NOT NULL, 
        `replacement_index` INT NOT NULL, 
        `replacement_value` TEXT NOT NULL)");

    updateVersion('3.2.0');
}