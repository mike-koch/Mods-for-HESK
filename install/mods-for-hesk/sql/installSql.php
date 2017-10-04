<?php

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