<?php

//-- Mods For Hesk Theme Color Settings
$modsForHesk_settings['navbarBackgroundColor'] = '#414a5c';
$modsForHesk_settings['navbarBrandColor'] = '#d4dee7';
$modsForHesk_settings['navbarBrandHoverColor'] = '#ffffff';
$modsForHesk_settings['navbarItemTextColor'] = '#d4dee7';
$modsForHesk_settings['navbarItemTextHoverColor'] = '#ffffff';
$modsForHesk_settings['navbarItemTextSelectedColor'] = '#ffffff';
$modsForHesk_settings['navbarItemSelectedBackgroundColor'] = '#2d3646';
$modsForHesk_settings['dropdownItemTextColor'] = '#333333';
$modsForHesk_settings['dropdownItemTextHoverColor'] = '#262626';
$modsForHesk_settings['dropdownItemTextHoverBackgroundColor'] = '#f5f5f5';
$modsForHesk_settings['questionMarkColor'] = '#000000';

//-- Set this to 1 for right-to-left text.
$modsForHesk_settings['rtl'] = 0;

//-- Set this to 1 to show icons next to navigation menu items
$modsForHesk_settings['show_icons'] = 0;

//-- Set this to 1 to enable custom field names as keys
$modsForHesk_settings['custom_field_setting'] = 0;

//-- Set this to 1 to enable email verification for new customers
$modsForHesk_settings['customer_email_verification_required'] = 0;

//-- Set this to 1 to enable HTML-formatted emails.
$modsForHesk_settings['html_emails'] = 1;

//-- Mailgun Settings
$modsForHesk_settings['use_mailgun'] = 0;
$modsForHesk_settings['mailgun_api_key'] = '';
$modsForHesk_settings['mailgun_domain'] = '';

//-- Set this to 1 to enable bootstrap-theme.css
$modsForHesk_settings['use_bootstrap_theme'] = 1;

//-- Default value for new Knowledgebase article: 0 = Published, 1 = Private, 2 = Draft
$modsForHesk_settings['new_kb_article_visibility'] = 0;

//-- Setting for adding attachments to email messages. Either 0 for default-HESK behavior, or 1 to send as attachments
$modsForHesk_settings['attachments'] = 0;

//-- Setting for showing number of merged tickets in the ticket search screen. 0 = Disable, 1 = Enable
$modsForHesk_settings['show_number_merged'] = 1;

//-- Setting for requesting user's location. 0 = Disable, 1 = Enable
$modsForHesk_settings['request_location'] = 0;

//-- Column to sort categories by. Can be either 'name' or 'cat_order'
$modsForHesk_settings['category_order_column'] = 'cat_order';

//-- Setting for using rich-text editor for tickets. 0 = Disable, 1 = Enable
$modsForHesk_settings['rich_text_for_tickets'] = 1;