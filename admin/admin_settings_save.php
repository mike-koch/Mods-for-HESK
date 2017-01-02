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

// Make sure OPcache is reset when modifying settings
if (function_exists('opcache_reset')) {
    opcache_reset();
}

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/setup_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();

require(HESK_PATH . 'inc/email_functions.inc.php');

hesk_isLoggedIn();

// Check permissions for this feature
hesk_checkPermission('can_manage_settings');

// A security check
hesk_token_check('POST');

// Demo mode
if (defined('HESK_DEMO')) {
    hesk_process_messages($hesklang['sdemo'], 'admin_settings.php');
}

$set = array();

/*** GENERAL ***/

/* --> General settings */
$set['site_title'] = hesk_input(hesk_POST('s_site_title'), $hesklang['err_sname']);
$set['site_title'] = str_replace('\\&quot;', '&quot;', $set['site_title']);
$set['site_url'] = hesk_input(hesk_POST('s_site_url'), $hesklang['err_surl']);
$set['hesk_title'] = hesk_input(hesk_POST('s_hesk_title'), $hesklang['err_htitle']);
$set['hesk_title'] = str_replace('\\&quot;', '&quot;', $set['hesk_title']);
$set['hesk_url'] = rtrim(hesk_input(hesk_POST('s_hesk_url'), $hesklang['err_hurl']), '/');
$set['webmaster_mail'] = hesk_validateEmail(hesk_POST('s_webmaster_mail'), $hesklang['err_wmmail']);
$set['noreply_mail'] = hesk_validateEmail(hesk_POST('s_noreply_mail'), $hesklang['err_nomail']);
$set['noreply_name'] = hesk_input(hesk_POST('s_noreply_name'));
$set['noreply_name'] = str_replace(array('\\&quot;', '&lt;', '&gt;'), '', $set['noreply_name']);
$set['noreply_name'] = trim(preg_replace('/\s{2,}/', ' ', $set['noreply_name']));

/* --> Language settings */
$set['can_sel_lang'] = empty($_POST['s_can_sel_lang']) ? 0 : 1;
$set['languages'] = hesk_getLanguagesArray();
$lang = explode('|', hesk_input(hesk_POST('s_language')));
if (isset($lang[1]) && in_array($lang[1], hesk_getLanguagesArray(1))) {
    $set['language'] = $lang[1];
} else {
    hesk_error($hesklang['err_lang']);
}

if (hesk_testMySQL()) {
    // Database connection OK
} elseif ($mysql_log) {
    hesk_error($mysql_error . '<br /><br /><b>' . $hesklang['mysql_said'] . ':</b> ' . $mysql_log);
} else {
    hesk_error($mysql_error);
}

/*** HELP DESK ***/

// ---> check admin folder
$set['admin_dir'] = isset($_POST['s_admin_dir']) && !is_array($_POST['s_admin_dir']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['s_admin_dir']) : 'admin';
/*
if ( ! is_dir(HESK_PATH . $set['admin_dir']) )
{
	hesk_error( sprintf($hesklang['err_adf'], $set['admin_dir']) );
}
*/

// ---> check attachments folder
$set['attach_dir'] = isset($_POST['s_attach_dir']) && !is_array($_POST['s_attach_dir']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['s_attach_dir']) : 'attachments';
/*
if ( ! is_dir(HESK_PATH . $set['attach_dir']) )
{
	hesk_error( sprintf($hesklang['err_atf'], $set['attach_dir']) );
}
if ( ! is_writable(HESK_PATH . $set['attach_dir']) )
{
	hesk_error( sprintf($hesklang['err_atr'], $set['attach_dir']) );
}
*/

$set['cache_dir'] = isset($_POST['s_cache_dir']) && ! is_array($_POST['s_cache_dir']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['s_cache_dir']) : 'cache';
$set['max_listings'] = hesk_checkMinMax(intval(hesk_POST('s_max_listings')), 1, 999, 10);
$set['print_font_size'] = hesk_checkMinMax(intval(hesk_POST('s_print_font_size')), 1, 99, 12);
$set['autoclose'] = hesk_checkMinMax(intval(hesk_POST('s_autoclose')), 0, 999, 7);
$set['max_open'] = hesk_checkMinMax(intval(hesk_POST('s_max_open')), 0, 999, 0);
$set['new_top'] = empty($_POST['s_new_top']) ? 0 : 1;
$set['reply_top'] = empty($_POST['s_reply_top']) ? 0 : 1;

/* --> Features */
$set['autologin'] = empty($_POST['s_autologin']) ? 0 : 1;
$set['autoassign'] = empty($_POST['s_autoassign']) ? 0 : 1;
$set['require_email']	= empty($_POST['s_require_email']) ? 0 : 1;
$set['require_owner']	= empty($_POST['s_require_owner']) ? 0 : 1;
$set['require_subject']	= hesk_checkMinMax( intval( hesk_POST('s_require_subject') ) , -1, 1, 1);
$set['require_message']	= hesk_checkMinMax( intval( hesk_POST('s_require_message') ) , -1, 1, 1);
$set['custclose'] = empty($_POST['s_custclose']) ? 0 : 1;
$set['custopen'] = empty($_POST['s_custopen']) ? 0 : 1;
$set['rating'] = empty($_POST['s_rating']) ? 0 : 1;
$set['cust_urgency'] = empty($_POST['s_cust_urgency']) ? 0 : 1;
$set['sequential'] = empty($_POST['s_sequential']) ? 0 : 1;
$set['time_worked'] = empty($_POST['s_time_worked']) ? 0 : 1;
$set['spam_notice'] = empty($_POST['s_spam_notice']) ? 0 : 1;
$set['list_users'] = empty($_POST['s_list_users']) ? 0 : 1;
$set['debug_mode'] = empty($_POST['s_debug_mode']) ? 0 : 1;
$set['short_link'] = empty($_POST['s_short_link']) ? 0 : 1;
$set['select_cat'] = empty($_POST['s_select_cat']) ? 0 : 1;
$set['select_pri'] = empty($_POST['s_select_pri']) ? 0 : 1;
$set['cat_show_select'] = hesk_checkMinMax( intval( hesk_POST('s_cat_show_select') ) , 0, 999, 10);

/* --> SPAM prevention */
$set['secimg_use'] = empty($_POST['s_secimg_use']) ? 0 : (hesk_POST('s_secimg_use') == 2 ? 2 : 1);
$set['secimg_sum'] = '';
for ($i = 1; $i <= 10; $i++) {
    $set['secimg_sum'] .= substr('AEUYBDGHJLMNPQRSTVWXZ123456789', rand(0, 29), 1);
}
$set['recaptcha_use'] = hesk_checkMinMax(intval(hesk_POST('s_recaptcha_use')), 0, 2, 0);
$set['recaptcha_public_key'] = hesk_input(hesk_POST('s_recaptcha_public_key'));
$set['recaptcha_private_key'] = hesk_input(hesk_POST('s_recaptcha_private_key'));
$set['question_use'] = empty($_POST['s_question_use']) ? 0 : 1;
$set['question_ask'] = hesk_getHTML(hesk_POST('s_question_ask')) or hesk_error($hesklang['err_qask']);
$set['question_ans'] = hesk_input(hesk_POST('s_question_ans'), $hesklang['err_qans']);

/* --> Security */
$set['attempt_limit'] = hesk_checkMinMax(intval(hesk_POST('s_attempt_limit')), 0, 999, 5);
if ($set['attempt_limit'] > 0) {
    $set['attempt_limit']++;
}
$set['attempt_banmin'] = hesk_checkMinMax(intval(hesk_POST('s_attempt_banmin')), 5, 99999, 60);
$set['reset_pass'] = empty($_POST['s_reset_pass']) ? 0 : 1;
$set['email_view_ticket'] = ($set['require_email'] == 0) ? 0 : (empty($_POST['s_email_view_ticket']) ? 0 : 1);
$set['x_frame_opt'] = empty($_POST['s_x_frame_opt']) ? 0 : 1;
$set['force_ssl'] = HESK_SSL && isset($_POST['s_force_ssl']) && $_POST['s_force_ssl'] == 1 ? 1 : 0;

// Make sure help desk URL starts with https if forcing SSL
if ($set['force_ssl']) {
    $set['hesk_url'] = preg_replace('/^http:/i', 'https:', $set['hesk_url']);
}

/* --> Attachments */
$set['attachments']['use'] = empty($_POST['s_attach_use']) ? 0 : 1;
if ($set['attachments']['use']) {
    $set['attachments']['max_number'] = intval(hesk_POST('s_max_number', 2));

    $size = floatval(hesk_POST('s_max_size', '1.0'));
    $unit = hesk_htmlspecialchars(hesk_POST('s_max_unit', 'MB'));

    $set['attachments']['max_size'] = hesk_formatUnits($size . ' ' . $unit);

    $set['attachments']['allowed_types'] = isset($_POST['s_allowed_types']) && !is_array($_POST['s_allowed_types']) && strlen($_POST['s_allowed_types']) ? explode(',', strtolower(preg_replace('/[^a-zA-Z0-9,]/', '', $_POST['s_allowed_types']))) : array();
    $set['attachments']['allowed_types'] = array_diff($set['attachments']['allowed_types'], array('php', 'php4', 'php3', 'php5', 'phps', 'phtml', 'shtml', 'shtm', 'cgi', 'pl'));

    if (count($set['attachments']['allowed_types'])) {
        $keep_these = array();

        foreach ($set['attachments']['allowed_types'] as $ext) {
            if (strlen($ext) > 1) {
                $keep_these[] = '.' . $ext;
            }
        }

        $set['attachments']['allowed_types'] = $keep_these;
    } else {
        $set['attachments']['allowed_types'] = array('.gif', '.jpg', '.png', '.zip', '.rar', '.csv', '.doc', '.docx', '.xls', '.xlsx', '.txt', '.pdf');
    }
} else {
    $set['attachments']['max_number'] = 2;
    $set['attachments']['max_size'] = 1048576;
    $set['attachments']['allowed_types'] = array('.gif', '.jpg', '.png', '.zip', '.rar', '.csv', '.doc', '.docx', '.xls', '.xlsx', '.txt', '.pdf');
}

/*** KNOWLEDGEBASE ***/

/* --> Knowledgebase settings */
$set['kb_enable'] = hesk_checkMinMax(intval(hesk_POST('s_kb_enable')), 0, 2, 1);
$set['kb_wysiwyg'] = empty($_POST['s_kb_wysiwyg']) ? 0 : 1;
$set['kb_search'] = empty($_POST['s_kb_search']) ? 0 : (hesk_POST('s_kb_search') == 2 ? 2 : 1);
$set['kb_recommendanswers'] = empty($_POST['s_kb_recommendanswers']) ? 0 : 1;
$set['kb_views'] = empty($_POST['s_kb_views']) ? 0 : 1;
$set['kb_date'] = empty($_POST['s_kb_date']) ? 0 : 1;
$set['kb_rating'] = empty($_POST['s_kb_rating']) ? 0 : 1;
$set['kb_search_limit'] = hesk_checkMinMax(intval(hesk_POST('s_kb_search_limit')), 1, 99, 10);
$set['kb_substrart'] = hesk_checkMinMax(intval(hesk_POST('s_kb_substrart')), 20, 9999, 200);
$set['kb_cols'] = hesk_checkMinMax(intval(hesk_POST('s_kb_cols')), 1, 5, 2);
$set['kb_numshow'] = intval(hesk_POST('s_kb_numshow')); // Popular articles on subcat listing
$set['kb_popart'] = intval(hesk_POST('s_kb_popart')); // Popular articles on main category page
$set['kb_latest'] = intval(hesk_POST('s_kb_latest')); // Popular articles on main category page
$set['kb_index_popart'] = intval(hesk_POST('s_kb_index_popart'));
$set['kb_index_latest'] = intval(hesk_POST('s_kb_index_latest'));
$set['kb_related'] = intval(hesk_POST('s_kb_related'));


/*** EMAIL ***/

/* --> Email sending */
$smtp_OK = true;
if (empty($_POST['s_smtp'])) {
    $set['smtp'] = 0;
    $set['use_mailgun'] = 0;
} elseif ($_POST['s_smtp'] == 1) {
    $set['smtp'] = 1;
    $set['use_mailgun'] = 0;
} else {
    $set['smtp'] = 0;
    $set['use_mailgun'] = 1;
}
if ($set['smtp']) {
    // Test SMTP connection
    $smtp_OK = hesk_testSMTP(true);

    // If SMTP not working, disable it
    if (!$smtp_OK) {
        $set['smtp'] = 0;
    }
} else {
    $set['smtp_host_name'] = hesk_input(hesk_POST('tmp_smtp_host_name', 'mail.example.com'));
    $set['smtp_host_port'] = intval(hesk_POST('tmp_smtp_host_port', 25));
    $set['smtp_timeout'] = intval(hesk_POST('tmp_smtp_timeout', 10));
    $set['smtp_ssl'] = empty($_POST['tmp_smtp_ssl']) ? 0 : 1;
    $set['smtp_tls'] = empty($_POST['tmp_smtp_tls']) ? 0 : 1;
    $set['smtp_user'] = hesk_input(hesk_POST('tmp_smtp_user'));
    $set['smtp_password'] = hesk_input(hesk_POST('tmp_smtp_password'));
}

if ($set['use_mailgun'] == 1) {
    $set['mailgun_api_key'] = hesk_input(hesk_POST('mailgun_api_key'));
    $set['mailgun_domain'] = hesk_input(hesk_POST('mailgun_domain'));
}

/* --> Email piping */
$set['email_piping'] = empty($_POST['s_email_piping']) ? 0 : 1;

/* --> POP3 fetching */
$pop3_OK = true;
$set['pop3'] = empty($_POST['s_pop3']) ? 0 : 1;
if ($set['pop3']) {
    // Get POP3 fetching timeout
    $set['pop3_job_wait'] = hesk_checkMinMax(intval(hesk_POST('s_pop3_job_wait')), 0, 1440, 15);

    // Test POP3 connection
    $pop3_OK = hesk_testPOP3(true);

    // If POP3 not working, disable it
    if (!$pop3_OK) {
        $set['pop3'] = 0;
    }
} else {
    $set['pop3_job_wait'] = intval(hesk_POST('s_pop3_job_wait', 15));
    $set['pop3_host_name'] = hesk_input(hesk_POST('tmp_pop3_host_name', 'mail.example.com'));
    $set['pop3_host_port'] = intval(hesk_POST('tmp_pop3_host_port', 110));
    $set['pop3_tls'] = empty($_POST['tmp_pop3_tls']) ? 0 : 1;
    $set['pop3_keep'] = empty($_POST['tmp_pop3_keep']) ? 0 : 1;
    $set['pop3_user'] = hesk_input(hesk_POST('tmp_pop3_user'));
    $set['pop3_password'] = hesk_input(hesk_POST('tmp_pop3_password'));
}

/* --> IMAP fetching */
$imap_OK = true;
$set['imap'] = empty($_POST['s_imap']) ? 0 : 1;

if ($set['imap']) {
    // Get IMAP fetching timeout
    $set['imap_job_wait'] = hesk_checkMinMax( intval( hesk_POST('s_imap_job_wait') ) , 0, 1440, 15);

    // Test IMAP connection
    $imap_OK = hesk_testIMAP(true);

    // If IMAP not working, disable it
    if ( ! $imap_OK) {
        $set['imap'] = 0;
    }
} else {
    $set['imap_job_wait']	= intval( hesk_POST('s_imap_job_wait', 15) );
    $set['imap_host_name']	= hesk_input( hesk_POST('tmp_imap_host_name', 'mail.example.com') );
    $set['imap_host_port']	= intval( hesk_POST('tmp_imap_host_port', 110) );
    $set['imap_enc']		= hesk_POST('tmp_imap_enc');
    $set['imap_enc']		= ($set['imap_enc'] == 'ssl' || $set['imap_enc'] == 'tls') ? $set['imap_enc'] : '';
    $set['imap_keep']		= empty($_POST['tmp_imap_keep']) ? 0 : 1;
    $set['imap_user']		= hesk_input( hesk_POST('tmp_imap_user') );
    $set['imap_password']	= hesk_input( hesk_POST('tmp_imap_password') );
}

/* --> Email loops */
$set['loop_hits'] = hesk_checkMinMax(intval(hesk_POST('s_loop_hits')), 0, 999, 5);
$set['loop_time'] = hesk_checkMinMax(intval(hesk_POST('s_loop_time')), 1, 86400, 300);

/* --> Detect email typos */
$set['detect_typos'] = empty($_POST['s_detect_typos']) ? 0 : 1;
$set['email_providers'] = array();

if (!empty($_POST['s_email_providers']) && !is_array($_POST['s_email_providers'])) {
    $lines = preg_split('/$\R?^/m', hesk_input($_POST['s_email_providers']));
    foreach ($lines as $domain) {
        $domain = trim($domain);
        $domain = str_replace('@', '', $domain);
        $domainLen = strlen($domain);

        /* Check domain part length */
        if ($domainLen < 1 || $domainLen > 254) {
            continue;
        }

        /* Check domain part characters */
        if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
            continue;
        }

        /* Domain part mustn't have two consecutive dots */
        if (strpos($domain, '..') !== false) {
            continue;
        }

        $set['email_providers'][] = $domain;
    }
}

if (!$set['detect_typos'] || count($set['email_providers']) < 1) {
    $set['detect_typos'] = 0;
    $set['email_providers']=array('aim.com','aol.co.uk','aol.com','att.net','bellsouth.net','blueyonder.co.uk','bt.com','btinternet.com','btopenworld.com','charter.net','comcast.net','cox.net','earthlink.net','email.com','facebook.com','fastmail.fm','free.fr','freeserve.co.uk','gmail.com','gmx.at','gmx.ch','gmx.com','gmx.de','gmx.fr','gmx.net','gmx.us','googlemail.com','hotmail.be','hotmail.co.uk','hotmail.com','hotmail.com.ar','hotmail.com.mx','hotmail.de','hotmail.es','hotmail.fr','hushmail.com','icloud.com','inbox.com','laposte.net','lavabit.com','list.ru','live.be','live.co.uk','live.com','live.com.ar','live.com.mx','live.de','live.fr','love.com','lycos.com','mac.com','mail.com','mail.ru','me.com','msn.com','nate.com','naver.com','neuf.fr','ntlworld.com','o2.co.uk','online.de','orange.fr','orange.net','outlook.com','pobox.com','prodigy.net.mx','qq.com','rambler.ru','rocketmail.com','safe-mail.net','sbcglobal.net','t-online.de','talktalk.co.uk','tiscali.co.uk','verizon.net','virgin.net','virginmedia.com','wanadoo.co.uk','wanadoo.fr','yahoo.co.id','yahoo.co.in','yahoo.co.jp','yahoo.co.kr','yahoo.co.uk','yahoo.com','yahoo.com.ar','yahoo.com.mx','yahoo.com.ph','yahoo.com.sg','yahoo.de','yahoo.fr','yandex.com','yandex.ru','ymail.com');
}

$set['email_providers'] = count($set['email_providers']) ?  "'" . implode("','", array_unique($set['email_providers'])) . "'" : '';


/* --> Notify customer when */
$set['notify_new'] = empty($_POST['s_notify_new']) ? 0 : 1;
$set['notify_closed'] = empty($_POST['s_notify_closed']) ? 0 : 1;

// SPAM tags
$set['notify_skip_spam'] = empty($_POST['s_notify_skip_spam']) ? 0 : 1;
$set['notify_spam_tags'] = array();

if (!empty($_POST['s_notify_spam_tags']) && !is_array($_POST['s_notify_spam_tags'])) {
    $lines = preg_split('/$\R?^/m', $_POST['s_notify_spam_tags']);

    foreach ($lines as $tag) {
        // Remove dangerous tags just as an extra precaution
        $tag = str_replace(array('<?php', '<?', '<%', '<script'), '', $tag);

        // Remove excess spaces
        $tag = trim($tag);

        // Remove anything not utf-8
        $tag = hesk_clean_utf8($tag);

        // Limit tag length
        if (strlen($tag) < 1 || strlen($tag) > 50) {
            continue;
        }

        // Escape single quotes and backslashes
        $set['notify_spam_tags'][] = str_replace(array("\\", "'"), array("\\\\", "\\'"), $tag); // '
    }
}

if (count($set['notify_spam_tags']) < 1) {
    $set['notify_skip_spam'] = 0;
    $set['notify_spam_tags'] = array('Spam?}', '***SPAM***', '[SPAM]', 'SPAM-LOW:', 'SPAM-MED:');
}

$set['notify_spam_tags'] = count($set['notify_spam_tags']) ? "'" . implode("','", $set['notify_spam_tags']) . "'" : '';

/* --> Other */
$set['strip_quoted'] = empty($_POST['s_strip_quoted']) ? 0 : 1;
$set['eml_req_msg'] = empty($_POST['s_eml_req_msg']) ? 0 : 1;
$set['save_embedded'] = empty($_POST['s_save_embedded']) ? 0 : 1;
$set['multi_eml'] = empty($_POST['s_multi_eml']) ? 0 : 1;
$set['confirm_email'] = empty($_POST['s_confirm_email']) ? 0 : 1;
$set['open_only'] = empty($_POST['s_open_only']) ? 0 : 1;

/*** TICKET LIST ***/

$set['ticket_list'] = array();
foreach ($hesk_settings['possible_ticket_list'] as $key => $title) {
    if (hesk_POST('s_tl_' . $key, 0) == 1) {
        $set['ticket_list'][] = $key;
    }
}

// We need at least one of these: id, trackid, subject
if (!in_array('id', $set['ticket_list']) && !in_array('trackid', $set['ticket_list']) && !in_array('subject', $set['ticket_list'])) {
    // None of the required fields are there, add "trackid" as the first one
    array_unshift($set['ticket_list'], 'trackid');
}

$set['ticket_list'] = count($set['ticket_list']) ? "'" . implode("','", $set['ticket_list']) . "'" : 'trackid';

/* --> Other */
$set['submittedformat'] = hesk_checkMinMax(intval(hesk_POST('s_submittedformat')), 0, 2, 2);
$set['updatedformat'] = hesk_checkMinMax(intval(hesk_POST('s_updatedformat')), 0, 2, 2);

/*** MISC ***/

/* --> Date & Time */
$set['diff_hours'] = floatval(hesk_POST('s_diff_hours', 0));
$set['diff_minutes'] = floatval(hesk_POST('s_diff_minutes', 0));
$set['daylight'] = empty($_POST['s_daylight']) ? 0 : 1;
$set['timeformat'] = hesk_input(hesk_POST('s_timeformat')) or $set['timeformat'] = 'Y-m-d H:i:s';

/* --> Other */
$set['ip_whois'] = hesk_input(hesk_POST('s_ip_whois', 'http://whois.domaintools.com/{IP}'));

// If no {IP} tag append it to the end
if (strlen($set['ip_whois']) == 0) {
    $set['ip_whois'] = 'http://whois.domaintools.com/{IP}';
} elseif (strpos($set['ip_whois'], '{IP}') === false) {
    $set['ip_whois'] .= '{IP}';
}

$set['maintenance_mode'] = empty($_POST['s_maintenance_mode']) ? 0 : 1;
$set['alink'] = empty($_POST['s_alink']) ? 0 : 1;
$set['submit_notice'] = empty($_POST['s_submit_notice']) ? 0 : 1;
$set['online'] = empty($_POST['s_online']) ? 0 : 1;
$set['online_min'] = hesk_checkMinMax(intval(hesk_POST('s_online_min')), 1, 999, 10);
$set['check_updates'] = empty($_POST['s_check_updates']) ? 0 : 1;
$set['hesk_version'] = $hesk_settings['hesk_version'];

// Process quick help sections
hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` SET `show` = '0'");
$postArray = hesk_POST_array('quick_help_sections');
foreach ($postArray as $value) {
    hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` SET `show` = '1' WHERE `id` = '" . intval($value) . "'");
}

// Save the modsForHesk_settings.inc.php file
$set['rtl'] = empty($_POST['rtl']) ? 0 : 1;
$set['show-icons'] = empty($_POST['show-icons']) ? 0 : 1;
$set['custom-field-setting'] = empty($_POST['custom-field-setting']) ? 0 : 1;
$set['customer-email-verification-required'] = empty($_POST['email-verification']) ? 0 : 1;
$set['html_emails'] = empty($_POST['html_emails']) ? 0 : 1;
$set['use_bootstrap_theme'] = empty($_POST['use_bootstrap_theme']) ? 0 : 1;
$set['new_kb_article_visibility'] = hesk_checkMinMax(intval(hesk_POST('new_kb_article_visibility')), 0, 2, 2);
$set['mfh_attachments'] = empty($_POST['email_attachments']) ? 0 : 1;
$set['show_number_merged'] = empty($_POST['show_number_merged']) ? 0 : 1;
$set['request_location'] = empty($_POST['request_location']) ? 0 : 1;
$set['category_order_column'] = empty($_POST['category_order_column']) ? 'cat_order' : 'name';

$rich_text_setting = hesk_POST('rich_text_for_tickets', 0);
if ($rich_text_setting == 0) {
    $set['rich_text_for_tickets'] = 0;
    $set['rich_text_for_tickets_for_customers'] = 0;
} elseif ($rich_text_setting == 1) {
    $set['rich_text_for_tickets'] = 1;
    $set['rich_text_for_tickets_for_customers'] = 0;
} else {
    $set['rich_text_for_tickets'] = 1;
    $set['rich_text_for_tickets_for_customers'] = 1;
}

$set['statuses_order_column'] = empty($_POST['statuses_order_column']) ? 'sort' : 'name';
$set['kb_attach_dir'] = hesk_POST('kb_attach_dir', 'attachments');
$set['display_user_agent_information'] = empty($_POST['display_user_agent_information']) ? 0 : 1;
$set['navbar_title_url'] = hesk_POST('navbar_title_url');
$set['enable_calendar'] = hesk_checkMinMax(intval(hesk_POST('enable_calendar')), 0, 2, 2);
$set['first_day_of_week'] = hesk_POST('first-day-of-week', 0);
$set['default_view'] = hesk_POST('default-view', 'month');

if ($set['customer-email-verification-required']) {
    //-- Don't allow multiple emails if verification is required
    $set['multi_eml'] = 0;
}
$set['navbarBackgroundColor'] = hesk_input(hesk_POST('navbarBackgroundColor'));
$set['navbarBrandColor'] = hesk_input(hesk_POST('navbarBrandColor'));
$set['navbarBrandHoverColor'] = hesk_input(hesk_POST('navbarBrandHoverColor'));
$set['navbarItemTextColor'] = hesk_input(hesk_POST('navbarItemTextColor'));
$set['navbarItemTextHoverColor'] = hesk_input(hesk_POST('navbarItemTextHoverColor'));
$set['navbarItemTextSelectedColor'] = hesk_input(hesk_POST('navbarItemTextSelectedColor'));
$set['navbarItemSelectedBackgroundColor'] = hesk_input(hesk_POST('navbarItemSelectedBackgroundColor'));
$set['dropdownItemTextColor'] = hesk_input(hesk_POST('dropdownItemTextColor'));
$set['dropdownItemTextHoverColor'] = hesk_input(hesk_POST('dropdownItemTextHoverColor'));
$set['questionMarkColor'] = hesk_input(hesk_POST('questionMarkColor'));
$set['dropdownItemTextHoverBackgroundColor'] = hesk_input(hesk_POST('dropdownItemTextHoverBackgroundColor'));
$set['admin_color_scheme'] = hesk_input(hesk_POST('admin-color-scheme'));
mfh_updateSetting('rtl', $set['rtl']);
mfh_updateSetting('show_icons', $set['show-icons']);
mfh_updateSetting('custom_field_setting', $set['custom-field-setting']);
mfh_updateSetting('customer_email_verification_required', $set['customer-email-verification-required']);
mfh_updateSetting('html_emails', $set['html_emails']);
mfh_updateSetting('use_bootstrap_theme', $set['use_bootstrap_theme']);
mfh_updateSetting('new_kb_article_visibility', $set['new_kb_article_visibility']);
mfh_updateSetting('attachments', $set['mfh_attachments']);
mfh_updateSetting('show_number_merged', $set['show_number_merged']);
mfh_updateSetting('request_location', $set['request_location']);
mfh_updateSetting('category_order_column', $set['category_order_column'], true);
mfh_updateSetting('rich_text_for_tickets', $set['rich_text_for_tickets']);
mfh_updateSetting('rich_text_for_tickets_for_customers', $set['rich_text_for_tickets_for_customers']);
mfh_updateSetting('statuses_order_column', $set['statuses_order_column'], true);
mfh_updateSetting('kb_attach_dir', $set['kb_attach_dir'], true);
mfh_updateSetting('navbarBackgroundColor', $set['navbarBackgroundColor'], true);
mfh_updateSetting('navbarBrandColor', $set['navbarBrandColor'], true);
mfh_updateSetting('navbarBrandHoverColor', $set['navbarBrandHoverColor'], true);
mfh_updateSetting('navbarItemTextColor', $set['navbarItemTextColor'], true);
mfh_updateSetting('navbarItemTextHoverColor', $set['navbarItemTextHoverColor'], true);
mfh_updateSetting('navbarItemTextSelectedColor', $set['navbarItemTextSelectedColor'], true);
mfh_updateSetting('navbarItemSelectedBackgroundColor', $set['navbarItemSelectedBackgroundColor'], true);
mfh_updateSetting('dropdownItemTextColor', $set['dropdownItemTextColor'], true);
mfh_updateSetting('dropdownItemTextHoverColor', $set['dropdownItemTextHoverColor'], true);
mfh_updateSetting('questionMarkColor', $set['questionMarkColor'], true);
mfh_updateSetting('dropdownItemTextHoverBackgroundColor', $set['dropdownItemTextHoverBackgroundColor'], true);
mfh_updateSetting('display_user_agent_information', $set['display_user_agent_information']);
mfh_updateSetting('navbar_title_url', $set['navbar_title_url'], true);
if ($set['use_mailgun'] == 1) {
    mfh_updateSetting('mailgun_api_key', $set['mailgun_api_key'], true);
    mfh_updateSetting('mailgun_domain', $set['mailgun_domain'], true);
}
mfh_updateSetting('use_mailgun', $set['use_mailgun'], false);
mfh_updateSetting('enable_calendar', $set['enable_calendar'], false);
mfh_updateSetting('first_day_of_week', $set['first_day_of_week'], false);
mfh_updateSetting('default_calendar_view', $set['default_view'], true);
mfh_updateSetting('admin_color_scheme', $set['admin_color_scheme'], true);

// Prepare settings file and save it
$settings_file_content = '<?php
// Settings file for HESK ' . $set['hesk_version'] . '

// ==> GENERAL

// --> General settings
$hesk_settings[\'site_title\']=\'' . $set['site_title'] . '\';
$hesk_settings[\'site_url\']=\'' . $set['site_url'] . '\';
$hesk_settings[\'hesk_title\']=\'' . $set['hesk_title'] . '\';
$hesk_settings[\'hesk_url\']=\'' . $set['hesk_url'] . '\';
$hesk_settings[\'webmaster_mail\']=\'' . $set['webmaster_mail'] . '\';
$hesk_settings[\'noreply_mail\']=\'' . $set['noreply_mail'] . '\';
$hesk_settings[\'noreply_name\']=\'' . $set['noreply_name'] . '\';

// --> Language settings
$hesk_settings[\'can_sel_lang\']=' . $set['can_sel_lang'] . ';
$hesk_settings[\'language\']=\'' . $set['language'] . '\';
$hesk_settings[\'languages\']=array(
' . $set['languages'] . ');

// --> Database settings
$hesk_settings[\'db_host\']=\'' . $set['db_host'] . '\';
$hesk_settings[\'db_name\']=\'' . $set['db_name'] . '\';
$hesk_settings[\'db_user\']=\'' . $set['db_user'] . '\';
$hesk_settings[\'db_pass\']=\'' . $set['db_pass'] . '\';
$hesk_settings[\'db_pfix\']=\'' . $set['db_pfix'] . '\';
$hesk_settings[\'db_vrsn\']=' . $set['db_vrsn'] . ';


// ==> HELP DESK

// --> Help desk settings
$hesk_settings[\'admin_dir\']=\'' . $set['admin_dir'] . '\';
$hesk_settings[\'attach_dir\']=\'' . $set['attach_dir'] . '\';
$hesk_settings[\'cache_dir\']=\'' . $set['cache_dir'] . '\';
$hesk_settings[\'max_listings\']=' . $set['max_listings'] . ';
$hesk_settings[\'print_font_size\']=' . $set['print_font_size'] . ';
$hesk_settings[\'autoclose\']=' . $set['autoclose'] . ';
$hesk_settings[\'max_open\']=' . $set['max_open'] . ';
$hesk_settings[\'new_top\']=' . $set['new_top'] . ';
$hesk_settings[\'reply_top\']=' . $set['reply_top'] . ';

// --> Features
$hesk_settings[\'autologin\']=' . $set['autologin'] . ';
$hesk_settings[\'autoassign\']=' . $set['autoassign'] . ';
$hesk_settings[\'require_email\']=' . $set['require_email'] . ';
$hesk_settings[\'require_owner\']=' . $set['require_owner'] . ';
$hesk_settings[\'require_subject\']=' . $set['require_subject'] . ';
$hesk_settings[\'require_message\']=' . $set['require_message'] . ';
$hesk_settings[\'custclose\']=' . $set['custclose'] . ';
$hesk_settings[\'custopen\']=' . $set['custopen'] . ';
$hesk_settings[\'rating\']=' . $set['rating'] . ';
$hesk_settings[\'cust_urgency\']=' . $set['cust_urgency'] . ';
$hesk_settings[\'sequential\']=' . $set['sequential'] . ';
$hesk_settings[\'time_worked\']=' . $set['time_worked'] . ';
$hesk_settings[\'spam_notice\']=' . $set['spam_notice'] . ';
$hesk_settings[\'list_users\']=' . $set['list_users'] . ';
$hesk_settings[\'debug_mode\']=' . $set['debug_mode'] . ';
$hesk_settings[\'short_link\']=' . $set['short_link'] . ';
$hesk_settings[\'select_cat\']=' . $set['select_cat'] . ';
$hesk_settings[\'select_pri\']=' . $set['select_pri'] . ';
$hesk_settings[\'cat_show_select\']=' . $set['cat_show_select'] . ';

// --> SPAM Prevention
$hesk_settings[\'secimg_use\']=' . $set['secimg_use'] . ';
$hesk_settings[\'secimg_sum\']=\'' . $set['secimg_sum'] . '\';
$hesk_settings[\'recaptcha_use\']=' . $set['recaptcha_use'] . ';
$hesk_settings[\'recaptcha_public_key\']=\'' . $set['recaptcha_public_key'] . '\';
$hesk_settings[\'recaptcha_private_key\']=\'' . $set['recaptcha_private_key'] . '\';
$hesk_settings[\'question_use\']=' . $set['question_use'] . ';
$hesk_settings[\'question_ask\']=\'' . $set['question_ask'] . '\';
$hesk_settings[\'question_ans\']=\'' . $set['question_ans'] . '\';

// --> Security
$hesk_settings[\'attempt_limit\']=' . $set['attempt_limit'] . ';
$hesk_settings[\'attempt_banmin\']=' . $set['attempt_banmin'] . ';
$hesk_settings[\'reset_pass\']=' . $set['reset_pass'] . ';
$hesk_settings[\'email_view_ticket\']=' . $set['email_view_ticket'] . ';
$hesk_settings[\'x_frame_opt\']=' . $set['x_frame_opt'] . ';
$hesk_settings[\'force_ssl\']=' . $set['force_ssl'] . ';

// --> Attachments
$hesk_settings[\'attachments\']=array (
\'use\' => ' . $set['attachments']['use'] . ',
\'max_number\' => ' . $set['attachments']['max_number'] . ',
\'max_size\' => ' . $set['attachments']['max_size'] . ',
\'allowed_types\' => array(\'' . implode('\',\'', $set['attachments']['allowed_types']) . '\')
);

// --> IMAP Fetching
$hesk_settings[\'imap\']=' . $set['imap'] . ';
$hesk_settings[\'imap_job_wait\']=' . $set['imap_job_wait'] . ';
$hesk_settings[\'imap_host_name\']=\'' . $set['imap_host_name'] . '\';
$hesk_settings[\'imap_host_port\']=' . $set['imap_host_port'] . ';
$hesk_settings[\'imap_enc\']=\'' . $set['imap_enc'] . '\';
$hesk_settings[\'imap_keep\']=' . $set['imap_keep'] . ';
$hesk_settings[\'imap_user\']=\'' . $set['imap_user'] . '\';
$hesk_settings[\'imap_password\']=\'' . $set['imap_password'] . '\';

// ==> KNOWLEDGEBASE

// --> Knowledgebase settings
$hesk_settings[\'kb_enable\']=' . $set['kb_enable'] . ';
$hesk_settings[\'kb_wysiwyg\']=' . $set['kb_wysiwyg'] . ';
$hesk_settings[\'kb_search\']=' . $set['kb_search'] . ';
$hesk_settings[\'kb_search_limit\']=' . $set['kb_search_limit'] . ';
$hesk_settings[\'kb_views\']=' . $set['kb_views'] . ';
$hesk_settings[\'kb_date\']=' . $set['kb_date'] . ';
$hesk_settings[\'kb_recommendanswers\']=' . $set['kb_recommendanswers'] . ';
$hesk_settings[\'kb_rating\']=' . $set['kb_rating'] . ';
$hesk_settings[\'kb_substrart\']=' . $set['kb_substrart'] . ';
$hesk_settings[\'kb_cols\']=' . $set['kb_cols'] . ';
$hesk_settings[\'kb_numshow\']=' . $set['kb_numshow'] . ';
$hesk_settings[\'kb_popart\']=' . $set['kb_popart'] . ';
$hesk_settings[\'kb_latest\']=' . $set['kb_latest'] . ';
$hesk_settings[\'kb_index_popart\']=' . $set['kb_index_popart'] . ';
$hesk_settings[\'kb_index_latest\']=' . $set['kb_index_latest'] . ';
$hesk_settings[\'kb_related\']=' . $set['kb_related'] . ';


// ==> EMAIL

// --> Email sending
$hesk_settings[\'smtp\']=' . $set['smtp'] . ';
$hesk_settings[\'smtp_host_name\']=\'' . $set['smtp_host_name'] . '\';
$hesk_settings[\'smtp_host_port\']=' . $set['smtp_host_port'] . ';
$hesk_settings[\'smtp_timeout\']=' . $set['smtp_timeout'] . ';
$hesk_settings[\'smtp_ssl\']=' . $set['smtp_ssl'] . ';
$hesk_settings[\'smtp_tls\']=' . $set['smtp_tls'] . ';
$hesk_settings[\'smtp_user\']=\'' . $set['smtp_user'] . '\';
$hesk_settings[\'smtp_password\']=\'' . $set['smtp_password'] . '\';

// --> Email piping
$hesk_settings[\'email_piping\']=' . $set['email_piping'] . ';

// --> POP3 Fetching
$hesk_settings[\'pop3\']=' . $set['pop3'] . ';
$hesk_settings[\'pop3_job_wait\']=' . $set['pop3_job_wait'] . ';
$hesk_settings[\'pop3_host_name\']=\'' . $set['pop3_host_name'] . '\';
$hesk_settings[\'pop3_host_port\']=' . $set['pop3_host_port'] . ';
$hesk_settings[\'pop3_tls\']=' . $set['pop3_tls'] . ';
$hesk_settings[\'pop3_keep\']=' . $set['pop3_keep'] . ';
$hesk_settings[\'pop3_user\']=\'' . $set['pop3_user'] . '\';
$hesk_settings[\'pop3_password\']=\'' . $set['pop3_password'] . '\';

// --> Email loops
$hesk_settings[\'loop_hits\']=' . $set['loop_hits'] . ';
$hesk_settings[\'loop_time\']=' . $set['loop_time'] . ';

// --> Detect email typos
$hesk_settings[\'detect_typos\']=' . $set['detect_typos'] . ';
$hesk_settings[\'email_providers\']=array(' . $set['email_providers'] . ');

// --> Notify customer when
$hesk_settings[\'notify_new\']=' . $set['notify_new'] . ';
$hesk_settings[\'notify_skip_spam\']=' . $set['notify_skip_spam'] . ';
$hesk_settings[\'notify_spam_tags\']=array(' . $set['notify_spam_tags'] . ');
$hesk_settings[\'notify_closed\']=' . $set['notify_closed'] . ';

// --> Other
$hesk_settings[\'strip_quoted\']=' . $set['strip_quoted'] . ';
$hesk_settings[\'eml_req_msg\']=' . $set['eml_req_msg'] . ';
$hesk_settings[\'save_embedded\']=' . $set['save_embedded'] . ';
$hesk_settings[\'multi_eml\']=' . $set['multi_eml'] . ';
$hesk_settings[\'confirm_email\']=' . $set['confirm_email'] . ';
$hesk_settings[\'open_only\']=' . $set['open_only'] . ';

// ==> TICKET LIST

$hesk_settings[\'ticket_list\']=array(' . $set['ticket_list'] . ');

// --> Other
$hesk_settings[\'submittedformat\']=' . $set['submittedformat'] . ';
$hesk_settings[\'updatedformat\']=' . $set['updatedformat'] . ';


// ==> MISC

// --> Date & Time
$hesk_settings[\'diff_hours\']=' . $set['diff_hours'] . ';
$hesk_settings[\'diff_minutes\']=' . $set['diff_minutes'] . ';
$hesk_settings[\'daylight\']=' . $set['daylight'] . ';
$hesk_settings[\'timeformat\']=\'' . $set['timeformat'] . '\';

// --> Other
$hesk_settings[\'ip_whois\']=\'' . $set['ip_whois'] . '\';
$hesk_settings[\'maintenance_mode\']=' . $set['maintenance_mode'] . ';
$hesk_settings[\'alink\']=' . $set['alink'] . ';
$hesk_settings[\'submit_notice\']=' . $set['submit_notice'] . ';
$hesk_settings[\'online\']=' . $set['online'] . ';
$hesk_settings[\'online_min\']=' . $set['online_min'] . ';
$hesk_settings[\'check_updates\']=' . $set['check_updates'] . ';


#############################
#     DO NOT EDIT BELOW     #
#############################
$hesk_settings[\'hesk_version\']=\'' . $set['hesk_version'] . '\';
if ($hesk_settings[\'debug_mode\'])
{
    error_reporting(E_ALL);
}
else
{
    error_reporting(0);
}
if (!defined(\'IN_SCRIPT\')) {die(\'Invalid attempt!\');}';

// Write to the settings file
if (!file_put_contents(HESK_PATH . 'hesk_settings.inc.php', $settings_file_content)) {
    hesk_error($hesklang['err_openset']);
}

// Any settings problems?
$tmp = array();

if (!$smtp_OK) {
    $tmp[] = '<span style="color:red; font-weight:bold">' . $hesklang['sme'] . ':</span> ' . $smtp_error . '<br /><br /><a href="Javascript:void(0)" onclick="Javascript:hesk_toggleLayerDisplay(\'smtplog\')">' . $hesklang['scl'] . '</a><div id="smtplog" style="display:none">&nbsp;<br /><textarea name="log" rows="10" cols="60">' . $smtp_log . '</textarea></div>';
}

if (!$pop3_OK) {
    $tmp[] = '<span style="color:red; font-weight:bold">' . $hesklang['pop3e'] . ':</span> ' . $pop3_error . '<br /><br /><a href="Javascript:void(0)" onclick="Javascript:hesk_toggleLayerDisplay(\'pop3log\')">' . $hesklang['pop3log'] . '</a><div id="pop3log" style="display:none">&nbsp;<br /><textarea name="log" rows="10" cols="60">' . $pop3_log . '</textarea></div>';
}

// Show the settings page and display any notices or success
if (count($tmp)) {
    $errors = implode('<br /><br />', $tmp);
    hesk_process_messages($hesklang['sns'] . '<br /><br />' . $errors, 'admin_settings.php', 'NOTICE');
} else {
    hesk_process_messages($hesklang['set_were_saved'], 'admin_settings.php', 'SUCCESS');
}
exit();


function mfh_updateSetting($key, $value, $isString = false)
{
    global $hesk_settings;

    $formattedValue = $isString ? "'" . hesk_dbEscape($value) . "'" : intval($value);

    hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = " . $formattedValue . " WHERE `Key` = '" . $key . "'");
}


function hesk_getLanguagesArray($returnArray = 0)
{
    global $hesk_settings, $hesklang;

    /* Get a list of valid emails */
    $hesk_settings['smtp'] = 0;
    $valid_emails = array_keys(hesk_validEmails());

    $dir = HESK_PATH . 'language/';
    $path = opendir($dir);
    $code = '';
    $langArray = array();

    /* Test all folders inside the language folder */
    while (false !== ($subdir = readdir($path))) {
        if ($subdir == "." || $subdir == "..") {
            continue;
        }

        if (filetype($dir . $subdir) == 'dir') {
            $add = 1;
            $langu = $dir . $subdir . '/text.php';
            $email = $dir . $subdir . '/emails';

            /* Check the text.php */
            if (file_exists($langu)) {
                $tmp = file_get_contents($langu);

                // Some servers add slashes to file_get_contents output
                if (strpos($tmp, '[\\\'LANGUAGE\\\']') !== false) {
                    $tmp = stripslashes($tmp);
                }

                $err = '';
                if (!preg_match('/\$hesklang\[\'LANGUAGE\'\]\=\'(.*)\'\;/', $tmp, $l)) {
                    $add = 0;
                } elseif (!preg_match('/\$hesklang\[\'ENCODING\'\]\=\'(.*)\'\;/', $tmp)) {
                    $add = 0;
                } elseif (!preg_match('/\$hesklang\[\'_COLLATE\'\]\=\'(.*)\'\;/', $tmp)) {
                    $add = 0;
                } elseif (!preg_match('/\$hesklang\[\'EMAIL_HR\'\]\=\'(.*)\'\;/', $tmp, $hr)) {
                    $add = 0;
                } elseif (!preg_match('/\$hesklang\[\'LANGUAGE_EN\'\]/', $tmp)) {
                    $add = 0;
                }
            } else {
                $add = 0;
            }

            /* Check emails folder */
            if (file_exists($email) && filetype($email) == 'dir') {
                foreach ($valid_emails as $eml) {
                    if (!file_exists($email . '/' . $eml . '.txt')) {
                        $add = 0;
                    }
                }
            } else {
                $add = 0;
            }

            /* Add an option for the <select> if needed */
            if ($add) {
                $code .= "'" . addslashes($l[1]) . "' => array('folder'=>'" . $subdir . "','hr'=>'" . addslashes($hr[1]) . "'),\n";
                $langArray[] = $l[1];
            }
        }
    }

    closedir($path);

    if ($returnArray) {
        return $langArray;
    } else {
        return $code;
    }
} // END hesk_getLanguagesArray()


function hesk_formatUnits($size)
{
    $units = array(
        'GB' => 1073741824,
        'MB' => 1048576,
        'kB' => 1024,
        'B' => 1
    );

    list($size, $suffix) = explode(' ', $size);

    if (isset($units[$suffix])) {
        return round($size * $units[$suffix]);
    }

    return false;
} // End hesk_formatBytes()
