<?php

namespace v250;


class MigrateSettingsToDatabase extends \AbstractMigration {

    function up($hesk_settings) {
        $modsForHesk_settings = array();
        if (file_exists(HESK_PATH . 'modsForHesk_settings.inc.php')) {
            require_once(HESK_PATH . 'modsForHesk_settings.inc.php');
        }

        $rtl = $this->getSettingValue($modsForHesk_settings, 'rtl', 0);
        $show_icons = $this->getSettingValue($modsForHesk_settings, 'show_icons', 0);
        $custom_field_setting = $this->getSettingValue($modsForHesk_settings, 'custom_field_setting', 0);
        $customer_email_verification_required = $this->getSettingValue($modsForHesk_settings, 'customer_email_verification_required', 0);
        $html_emails = $this->getSettingValue($modsForHesk_settings, 'html_emails', 1);
        $use_mailgun = $this->getSettingValue($modsForHesk_settings, 'use_mailgun', 0);
        $mailgun_api_key = $this->getSettingValue($modsForHesk_settings, 'mailgun_api_key', '');
        $mailgun_domain = $this->getSettingValue($modsForHesk_settings, 'mailgun_domain', '');
        $use_bootstrap_theme = $this->getSettingValue($modsForHesk_settings, 'use_bootstrap_theme', 1);
        $new_kb_article_visibility = $this->getSettingValue($modsForHesk_settings, 'new_kb_article_visibility', 0);
        $attachments = $this->getSettingValue($modsForHesk_settings, 'attachments', 0);
        $show_number_merged = $this->getSettingValue($modsForHesk_settings, 'show_number_merged', 1);
        $request_location = $this->getSettingValue($modsForHesk_settings, 'request_location', 0);
        $category_order_column = $this->getSettingValue($modsForHesk_settings, 'category_order_column', 'cat_order');
        $rich_text_for_tickets = $this->getSettingValue($modsForHesk_settings, 'rich_text_for_tickets', 0);
        $statuses_order_column = $this->getSettingValue($modsForHesk_settings, 'statuses_order_column', 'sort');
        $kb_attach_dir = $this->getSettingValue($modsForHesk_settings, 'kb_attach_dir', 'attachments');
        $rich_text_for_tickets_for_customers = $this->getSettingValue($modsForHesk_settings, 'rich_text_for_tickets_for_customers', 0);

        $navbar_background_color = $this->getSettingValue($modsForHesk_settings, 'navbarBackgroundColor', '#414a5c');
        $navbar_brand_color = $this->getSettingValue($modsForHesk_settings, 'navbarBrandColor', '#d4dee7');
        $navbar_brand_hover_color = $this->getSettingValue($modsForHesk_settings, 'navbarBrandHoverColor', '#ffffff');
        $navbar_item_text_color = $this->getSettingValue($modsForHesk_settings, 'navbarItemTextColor', '#d4dee7');
        $navbar_item_text_hover_color = $this->getSettingValue($modsForHesk_settings, 'navbarItemTextHoverColor', '#ffffff');
        $navbar_item_text_selected_color = $this->getSettingValue($modsForHesk_settings, 'navbarItemTextSelectedColor', '#ffffff');
        $navbar_item_selected_background_color = $this->getSettingValue($modsForHesk_settings, 'navbarItemSelectedBackgroundColor', '#2d3646');
        $dropdown_item_text_color = $this->getSettingValue($modsForHesk_settings, 'dropdownItemTextColor', '#333333');
        $dropdown_item_text_hover_color = $this->getSettingValue($modsForHesk_settings, 'dropdownItemTextHoverColor', '#262626');
        $dropdown_item_text_hover_background_color = $this->getSettingValue($modsForHesk_settings, 'dropdownItemTextHoverBackgroundColor', '#f5f5f5');
        $question_mark_color = $this->getSettingValue($modsForHesk_settings, 'questionMarkColor', '#000000');


        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('rtl', " . intval($rtl) . ")");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('show_icons', " . intval($show_icons) . ")");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('custom_field_setting', " . intval($custom_field_setting) . ")");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('customer_email_verification_required', " . intval($customer_email_verification_required) . ")");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('html_emails', " . intval($html_emails) . ")");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('use_mailgun', " . intval($use_mailgun) . ")");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('mailgun_api_key', '" . hesk_dbEscape($mailgun_api_key) . "')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('mailgun_domain', '" . hesk_dbEscape($mailgun_domain) . "')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('use_bootstrap_theme', " . intval($use_bootstrap_theme) . ")");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('new_kb_article_visibility', " . intval($new_kb_article_visibility) . ")");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('attachments', " . intval($attachments) . ")");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('show_number_merged', " . intval($show_number_merged) . ")");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('request_location', " . intval($request_location) . ")");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('category_order_column', '" . hesk_dbEscape($category_order_column) . "')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('rich_text_for_tickets', " . intval($rich_text_for_tickets) . ")");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('statuses_order_column', '" . hesk_dbEscape($statuses_order_column) . "')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('kb_attach_dir', '" . hesk_dbEscape($kb_attach_dir) . "')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('rich_text_for_tickets_for_customers', " . intval($rich_text_for_tickets_for_customers) . ")");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarBackgroundColor', '" . hesk_dbEscape($navbar_background_color) . "')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarBrandColor', '" . hesk_dbEscape($navbar_brand_color) . "')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarBrandHoverColor', '" . hesk_dbEscape($navbar_brand_hover_color) . "')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarItemTextColor', '" . hesk_dbEscape($navbar_item_text_color) . "')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarItemTextHoverColor', '" . hesk_dbEscape($navbar_item_text_hover_color) . "')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarItemTextSelectedColor', '" . hesk_dbEscape($navbar_item_text_selected_color) . "')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('navbarItemSelectedBackgroundColor', '" . hesk_dbEscape($navbar_item_selected_background_color) . "')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('dropdownItemTextColor', '" . hesk_dbEscape($dropdown_item_text_color) . "')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('dropdownItemTextHoverColor', '" . hesk_dbEscape($dropdown_item_text_hover_color) . "')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('dropdownItemTextHoverBackgroundColor', '" . hesk_dbEscape($dropdown_item_text_hover_background_color) . "')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` (`Key`, `Value`)
        VALUES ('questionMarkColor', '" . hesk_dbEscape($question_mark_color) . "')");
    }



    function getSettingValue($settings, $setting, $default) {
        return isset($settings[$setting]) ? $settings[$setting] : $default;
    }

    function down($hesk_settings) {
        $this->executeQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings`
            WHERE `Key` IN ('rtl', 'show_icons', 'custom_field_setting', 'customer_email_verification_required', 'html_emails',
                'use_mailgun', 'mailgun_api_key', 'mailgun_domain', 'use_bootstrap_theme', 'new_kb_article_visibility',
                'attachments', 'show_number_merged', 'request_location', 'category_order_column', 'rich_text_for_tickets',
                'statuses_order_column', 'kb_attach_dir', 'rich_text_for_tickets_for_customers', 'navbarBackgroundColor',
                'navbarBrandColor', 'navbarBrandHoverColor', 'navbarItemTextColor', 'navbarItemTextHoverColor',
                'navbarItemTextSelectedColor', 'navbarItemSelectedBackgroundColor', 'dropdownItemTextColor',
                'dropdownItemTextHoverColor', 'dropdownItemTextHoverBackgroundColor', 'questionMarkColor')");
    }
}