<?php

namespace v310;


class ConvertPresetToIndividualColors extends \AbstractMigration {

    function up($hesk_settings) {
        $theme_preset_rs = $this->executeQuery("SELECT `Value` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` WHERE `Key` = 'admin_color_scheme'");
        if (hesk_dbNumRows($theme_preset_rs) === 0) {
            $theme = 'skin-blue';
        } else {
            $theme_preset_row = hesk_dbFetchAssoc($theme_preset_rs);
            $theme = $theme_preset_row['Value'];
        }

        $light_theme = preg_match_all('/.*-light/', $theme);
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

        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
            VALUES ('admin_navbar_background', '{$navbar['background']}')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
            VALUES ('admin_navbar_background_hover', '{$navbar['background_hover']}')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
            VALUES ('admin_navbar_text', '{$navbar['text']}')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
            VALUES ('admin_navbar_text_hover', '{$navbar['text_hover']}')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
            VALUES ('admin_navbar_brand_background', '{$navbar_brand['background']}')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
            VALUES ('admin_navbar_brand_background_hover', '{$navbar_brand['background_hover']}')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
            VALUES ('admin_navbar_brand_text', '{$navbar_brand['text']}')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
            VALUES ('admin_navbar_brand_text_hover', '{$navbar_brand['text_hover']}')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
            VALUES ('admin_sidebar_background', '{$sidebar['background']}')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
            VALUES ('admin_sidebar_background_hover', '{$sidebar['background_hover']}')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
            VALUES ('admin_sidebar_text', '{$sidebar['text']}')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
            VALUES ('admin_sidebar_text_hover', '{$sidebar['text_hover']}')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
            VALUES ('admin_sidebar_font_weight', '{$sidebar['font_weight']}')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
            VALUES ('admin_sidebar_header_background', '{$sidebar_header['background']}')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
            VALUES ('admin_sidebar_header_text', '{$sidebar_header['text']}')");
        $this->executeQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` WHERE `Key` IN ('rtl', 'admin_color_scheme')");
    }

    function down($hesk_settings) {
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`) 
            VALUES ('admin_color_scheme', 'skin-blue')");
        $this->executeQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` WHERE `Key` IN ('admin_navbar_background',
            'admin_navbar_background_hover', 'admin_navbar_text', 'admin_navbar_text_hover', 'admin_navbar_brand_background',
            'admin_navbar_brand_background_hover', 'admin_navbar_brand_text', 'admin_navbar_brand_text_hover', 'admin_sidebar_background',
            'admin_sidebar_background_hover', 'admin_sidebar_text', 'admin_sidebar_text_hover', 'admin_sidebar_font_weight',
            'admin_sidebar_header_background', 'admin_sidebar_header_text')");
    }
}