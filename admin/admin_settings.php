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
define('PAGE_TITLE', 'ADMIN_SETTINGS');
define('MFH_PAGE_LAYOUT', 'TOP_AND_SIDE');
define('LOAD_TABS', 1);

// Make sure the install folder is deleted
if (is_dir(HESK_PATH . 'install')) {
    die('Please delete the <b>install</b> folder from your server for security reasons then refresh this page!');
}

// Get all the required files and functions
require(HESK_PATH . 'hesk_settings.inc.php');

// Save the default language for the settings page before choosing user's preferred one
$hesk_settings['language_default'] = $hesk_settings['language'];
require(HESK_PATH . 'inc/common.inc.php');
$hesk_settings['language'] = $hesk_settings['language_default'];
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/setup_functions.inc.php');
require(HESK_PATH . 'inc/mail_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

// Check permissions for this feature
hesk_checkPermission('can_man_settings');


// Load custom fields
require_once(HESK_PATH . 'inc/custom_fields.inc.php');

// Test languages function
if (isset($_GET['test_languages'])) {
    hesk_testLanguage(0);
}

$help_folder = '../language/' . $hesk_settings['languages'][$hesk_settings['language']]['folder'] . '/help_files/';

$enable_save_settings = 0;
$enable_use_attachments = 0;

// Print header
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

// Print main manage users page
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');

// Get the current version of Mods for Hesk
$modsForHeskVersionRS = hesk_dbQuery("SELECT `Value` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` WHERE `Key` = 'modsForHeskVersion'");
$modsForHeskVersionArray = hesk_dbFetchAssoc($modsForHeskVersionRS);
$modsForHeskVersion = $modsForHeskVersionArray['Value'];

// Demo mode? Hide values of sensitive settings
if (defined('HESK_DEMO')) {
    $hesk_settings['smtp_host_name'] = $hesklang['hdemo'];
    $hesk_settings['smtp_user'] = $hesklang['hdemo'];
    $hesk_settings['smtp_password'] = $hesklang['hdemo'];
    $hesk_settings['pop3_host_name'] = $hesklang['hdemo'];
    $hesk_settings['pop3_user'] = $hesklang['hdemo'];
    $hesk_settings['pop3_password'] = $hesklang['hdemo'];
    $hesk_settings['recaptcha_public_key'] = $hesklang['hdemo'];
    $hesk_settings['recaptcha_private_key'] = $hesklang['hdemo'];
    $hesk_settings['imap_host_name']	= $hesklang['hdemo'];
    $hesk_settings['imap_user']			= $hesklang['hdemo'];
    $hesk_settings['imap_password']		= $hesklang['hdemo'];
}


$hesklang['err_custname'] = addslashes($hesklang['err_custname']);

$modsForHesk_settings = mfh_getSettings();
?>
<script language="javascript" type="text/javascript"><!--
    function hesk_checkFields() {
        d = document.form1;

        // GENERAL
        if (d.s_site_title.value == '') {
            alert('<?php echo addslashes($hesklang['err_sname']); ?>');
            return false;
        }
        if (d.s_site_url.value == '') {
            alert('<?php echo addslashes($hesklang['err_surl']); ?>');
            return false;
        }
        if (d.s_webmaster_mail.value == '' || d.s_webmaster_mail.value.indexOf(".") == -1 || d.s_webmaster_mail.value.indexOf("@") == -1) {
            alert('<?php echo addslashes($hesklang['err_wmmail']); ?>');
            return false;
        }
        if (d.s_noreply_mail.value == '' || d.s_noreply_mail.value.indexOf(".") == -1 || d.s_noreply_mail.value.indexOf("@") == -1) {
            alert('<?php echo addslashes($hesklang['err_nomail']); ?>');
            return false;
        }

        if (d.s_db_host.value == '') {
            alert('<?php echo addslashes($hesklang['err_dbhost']); ?>');
            return false;
        }
        if (d.s_db_name.value == '') {
            alert('<?php echo addslashes($hesklang['err_dbname']); ?>');
            return false;
        }
        if (d.s_db_user.value == '') {
            alert('<?php echo addslashes($hesklang['err_dbuser']); ?>');
            return false;
        }
        if (d.s_db_pass.value == '') {
            if (!confirm('<?php echo addslashes($hesklang['mysql_root']); ?>')) {
                return false;
            }
        }

        // HELPDESK
        if (d.s_hesk_title.value == '') {
            alert('<?php echo addslashes($hesklang['err_htitle']); ?>');
            return false;
        }
        if (d.s_hesk_url.value == '') {
            alert('<?php echo addslashes($hesklang['err_hurl']); ?>');
            return false;
        }
        if (d.s_max_listings.value == '') {
            alert('<?php echo addslashes($hesklang['err_max']); ?>');
            return false;
        }
        if (d.s_print_font_size.value == '') {
            alert('<?php echo addslashes($hesklang['err_psize']); ?>');
            return false;
        }

        // KNOWLEDGEBASE

        // MISC

        // DISABLE SUBMIT BUTTON
        d.submitbutton.disabled = true;
        d.submitbutton.value = '<?php echo addslashes($hesklang['saving']); ?>';

        return true;
    }

    function hesk_toggleLayer(nr, setto) {
        if (document.all)
            document.all[nr].style.display = setto;
        else if (document.getElementById)
            document.getElementById(nr).style.display = setto;
    }

    function hesk_testLanguage() {
        window.open('admin_settings.php?test_languages=1', "Hesk_window", "height=400,width=500,menubar=0,location=0,toolbar=0,status=0,resizable=1,scrollbars=1");
        return false;
    }

    var tabberOptions = {

        'cookie': "tabber",
        'onLoad': function (argsObj) {
            var t = argsObj.tabber;
            var i;
            if (t.id) {
                t.cookie = t.id + t.cookie;
            }

            i = parseInt(getCookie(t.cookie));
            if (isNaN(i)) {
                return;
            }
            t.tabShow(i);
        },

        'onClick': function (argsObj) {
            var c = argsObj.tabber.cookie;
            var i = argsObj.index;
            setCookie(c, i);
        }
    };

    function checkRequiredEmail(field) {
        if (document.getElementById('s_require_email_0').checked && document.getElementById('s_email_view_ticket').checked) {
            if (field == 's_require_email_0' && confirm('<?php echo addslashes($hesklang['re_confirm1']); ?>')) {
                document.getElementById('s_email_view_ticket').checked = false;
                return true;
            } else if (field == 's_email_view_ticket' && confirm('<?php echo addslashes($hesklang['re_confirm2']); ?>')) {
                document.getElementById('s_require_email_1').checked = true;
                return true;
            }
            return false;
        }
        return true;
    }
    //-->
</script>
<aside class="main-sidebar">
    <section class="sidebar" style="height: auto">
        <ul class="sidebar-menu">
            <li class="header text-uppercase"><?php echo $hesklang['settings']; ?></li>
            <li>
                <a href="#general"><?php echo $hesklang['tab_1']; ?></a>
            </li>
            <li>
                <a href="#helpdesk"><?php echo $hesklang['tab_2']; ?></a>
            </li>
            <li>
                <a href="#knowledgebase"><?php echo $hesklang['tab_3']; ?></a>
            </li>
            <li>
                <a href="#calendar">
                    <?php echo $hesklang['calendar_title_case']; ?>
                    <span class="label label-primary" data-toggle="tooltip"
                          title="<?php echo $hesklang['added_in_mods_for_hesk']; ?>">
                            <?php echo $hesklang['mods_for_hesk_acronym']; ?>
                    </span>
                </a>
            </li>
            <li>
                <a href="#email"><?php echo $hesklang['tab_6']; ?></a>
            </li>
            <li>
                <a href="#ticket-list"><?php echo $hesklang['tab_7']; ?></a>
            </li>
            <li>
                <a href="#miscellaneous"><?php echo $hesklang['tab_5']; ?></a>
            </li>
            <li>
                <a href="#ui-colors">
                    <?php echo $hesklang['uiColors']; ?>
                    <span class="label label-primary" data-toggle="tooltip"
                          title="<?php echo $hesklang['added_in_mods_for_hesk']; ?>">
                            <?php echo $hesklang['mods_for_hesk_acronym']; ?>
                    </span>
                </a>
            </li>
        </ul>
    </section>
</aside>
<div class="content-wrapper">
    <section class="content">
    <?php
    /* This will handle error, success and notice messages */
    hesk_handle_messages();

    // Check file attachment limits
    if ($hesk_settings['attachments']['use'] && !defined('HESK_DEMO')) {
        // Check number of attachments per post
        if (version_compare(phpversion(), '5.2.12', '>=') && @ini_get('max_file_uploads') && @ini_get('max_file_uploads') < $hesk_settings['attachments']['max_number']) {
            hesk_show_notice($hesklang['fatte1']);
        }

        // Check max attachment size
        $tmp = @ini_get('upload_max_filesize');
        if ($tmp) {
            $last = strtoupper(substr($tmp, -1));
            $number = substr($tmp, 0, -1);

            switch ($last) {
                case 'K':
                    $tmp = $number * 1024;
                    break;
                case 'M':
                    $tmp = $number * 1048576;
                    break;
                case 'G':
                    $tmp = $number * 1073741824;
                    break;
                default:
                    $tmp = $number;
            }

            if ($tmp < $hesk_settings['attachments']['max_size']) {
                hesk_show_notice($hesklang['fatte2']);
            }
        }

        // Check max post size
        $tmp = @ini_get('post_max_size');
        if ($tmp) {
            $last = strtoupper(substr($tmp, -1));
            $number = substr($tmp, 0, -1);

            switch ($last) {
                case 'K':
                    $tmp = $number * 1024;
                    break;
                case 'M':
                    $tmp = $number * 1048576;
                    break;
                case 'G':
                    $tmp = $number * 1073741824;
                    break;
                default:
                    $tmp = $number;
            }

            if ($tmp < ($hesk_settings['attachments']['max_size'] * $hesk_settings['attachments']['max_number'] + 524288)) {
                hesk_show_notice($hesklang['fatte3']);
            }
        }

        // If SMTP server is used, "From email" should match SMTP username
        if ($hesk_settings['smtp'] && strtolower($hesk_settings['smtp_user']) != strtolower($hesk_settings['noreply_mail']) && hesk_validateEmail($hesk_settings['smtp_user'], 'ERR', 0)) {
            hesk_show_notice(sprintf($hesklang['from_warning'], $hesklang['email_noreply'], $hesklang['tab_1'], $hesk_settings['smtp_user']));
        }

        // If POP3 fetching is active, no user should have the same email address
        if ($hesk_settings['pop3'] && hesk_validateEmail($hesk_settings['pop3_user'], 'ERR', 0)) {
            $res = hesk_dbQuery("SELECT `name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `email` LIKE '".hesk_dbEscape($hesk_settings['pop3_user'])."'");

            if (hesk_dbNumRows($res) > 0) {
                hesk_show_notice(sprintf($hesklang['pop3_warning'], hesk_dbResult($res,0,0), $hesk_settings['pop3_user']) . "<br /><br />" . $hesklang['fetch_warning'], $hesklang['warn']);
            }
        }

        // If IMAP fetching is active, no user should have the same email address
        if ($hesk_settings['imap'] && hesk_validateEmail($hesk_settings['imap_user'], 'ERR', 0)) {
            $res = hesk_dbQuery("SELECT `name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `email` LIKE '".hesk_dbEscape($hesk_settings['imap_user'])."'");

            if (hesk_dbNumRows($res) > 0) {
                hesk_show_notice(sprintf($hesklang['imap_warning'], hesk_dbResult($res,0,0), $hesk_settings['imap_user']) . "<br /><br />" . $hesklang['fetch_warning'], $hesklang['warn']);
            }
        }
    }
    ?>
    <div class="box">
        <div class="box-header with-border">
            <h1 class="box-title">
                <?php echo $hesklang['installation_information']; ?>
            </h1>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <table class="table table-striped">
                <tr>
                    <td class="text-right">
                        <?php echo $hesklang['v']; ?>:
                    </td>
                    <td class="pad-right-10" id="hesk-version-status">
                        <?php echo $hesk_settings['hesk_version']; ?>
                        <?php if ($hesk_settings['check_updates']) : ?>
                            -
                            <i class="spinner fa fa-spin fa-spinner"></i>
                            <span class="up-to-date green" style="display: none">
                                <?php echo $hesklang['hud']; ?>
                            </span>
                            <span class="beta-version orange" style="display: none">
                                <?php echo $hesklang['beta']; ?>
                                <a href="https://www.hesk.com/update.php?v=<?php echo $hesk_settings['hesk_version']; ?>"
                                    target="_blank"><?php echo $hesklang['check4updates']; ?></a>
                            </span>
                            <span class="update-available orange" style="display: none">
                                <?php echo $hesklang['hnw']; ?>
                                <a href="https://www.hesk.com/update.php?v=<?php echo $hesk_settings['hesk_version']; ?>"
                                    target="_blank">
                                    <?php echo $hesklang['getup']; ?>
                                </a>
                            </span>
                            <a class="response-error" href="https://www.hesk.com/update.php?v=<?php echo $hesk_settings['hesk_version']; ?>"
                                target="_blank" style="display: none"><?php echo $hesklang['check4updates']; ?></a>
                            <script>
                                var heskUrl = $('p#hesk-path').text();
                                var $versionStatus = $('#hesk-version-status');
                                $.ajax({
                                    url: heskUrl + 'api/index.php/v1-public/hesk-version',
                                    method: 'GET',
                                    success: function(data) {
                                        if ('<?php echo $hesk_settings['hesk_version']; ?>' === data.version) {
                                            $versionStatus.addClass('success');
                                            $versionStatus.find('.up-to-date').show();
                                        } else if (<?php echo strpos($hesk_settings['hesk_version'], 'beta') ||
                                            strpos($hesk_settings['hesk_version'], 'dev') ||
                                            strpos($hesk_settings['hesk_version'], 'RC') ? 'true' : 'false'; ?>) {
                                            $versionStatus.addClass('warning');
                                            $versionStatus.find('.beta-version').show();
                                        } else {
                                            $versionStatus.addClass('warning');
                                            $versionStatus.find('.update-available').show();
                                        }
                                    },
                                    error: function() {
                                        $versionStatus.find('.response-error').show();
                                    },
                                    complete: function(data) {
                                        $versionStatus.find('.spinner').hide();
                                    }
                                });
                            </script>
                        <?php else: ?>
                            - <a
                            href="https://www.hesk.com/update.php?v=<?php echo $hesk_settings['hesk_version']; ?>"
                            target="_blank"><?php echo $hesklang['check4updates']; ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-right pad-up-5">
                        <?php echo $hesklang['mods_for_hesk_version']; ?>:
                    </td>
                    <td class="pad-right-10 pad-up-5" id="mfh-version-status">
                        <?php echo $modsForHeskVersion; ?>
                        <?php if ($hesk_settings['check_updates']) : ?>
                            -
                            <i class="spinner fa fa-spin fa-spinner"></i>
                            <span class="up-to-date green" style="display: none">
                                <?php echo $hesklang['mfh_up_to_date']; ?>
                            </span>
                            <span class="beta-version orange" style="display: none">
                                <?php echo $hesklang['beta']; ?>
                                <a href="https://www.mods-for-hesk.com/versioncheck.php?version=<?php echo $modsForHeskVersion; ?>"
                                   target="_blank"><?php echo $hesklang['check4updates']; ?></a>
                            </span>
                            <span class="update-available" style="display: none">
                                <a class="orange" href="https://www.mods-for-hesk.com/versioncheck.php?version=<?php echo $modsForHeskVersion; ?>"
                                   target="_blank">
                                    <?php echo $hesklang['hnw']; ?>
                                </a>
                            </span>
                            <a class="response-error" href="https://www.mods-for-hesk.com/versioncheck.php?version=<?php echo $modsForHeskVersion; ?>"
                               target="_blank" style="display: none"><?php echo $hesklang['check4updates']; ?></a>
                        <?php else: ?>
                            - <a
                                href="https://www.mods-for-hesk.com/versioncheck.php?version=<?php echo $modsForHeskVersion; ?>"
                                target="_blank"><?php echo $hesklang['check4updates']; ?></a>
                        <?php endif; ?>
                        <script>
                            var heskUrl = $('p#hesk-path').text();
                            var $mfhVersionStatus = $('#mfh-version-status');
                            $.ajax({
                                url: heskUrl + 'api/index.php/v1-public/mods-for-hesk-version',
                                method: 'GET',
                                success: function(data) {
                                    if ('<?php echo $modsForHeskVersion; ?>' === data.version) {
                                        $mfhVersionStatus.addClass('success');
                                        $mfhVersionStatus.find('.up-to-date').show();
                                    } else if (<?php echo strpos($modsForHeskVersion, 'beta') ||
                                    strpos($modsForHeskVersion, 'dev') ||
                                    strpos($modsForHeskVersion, 'RC') ? 'true' : 'false'; ?>) {
                                        $mfhVersionStatus.addClass('warning');
                                        $mfhVersionStatus.find('.beta-version').show();
                                    } else {
                                        $mfhVersionStatus.addClass('warning');
                                        $mfhVersionStatus.find('.update-available').show();
                                    }
                                },
                                error: function() {
                                    $mfhVersionStatus.find('.response-error').show();
                                },
                                complete: function(data) {
                                    $mfhVersionStatus.find('.spinner').hide();
                                }
                            });
                        </script>
                    </td>
                </tr>
                <tr>
                    <td class="text-right">
                        <?php echo $hesklang['phpv']; ?>:
                    </td>
                    <td class="pad-right-10">
                        <?php echo defined('HESK_DEMO') ? $hesklang['hdemo'] : PHP_VERSION . ' ' . (function_exists('mysqli_connect') ? '(MySQLi)' : '(MySQL)'); ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-right pad-up-5">
                        <?php echo $hesklang['mysqlv']; ?>:
                    </td>
                    <td class="pad-right-10 pad-up-5">
                        <?php echo defined('HESK_DEMO') ? $hesklang['hdemo'] : hesk_dbResult(hesk_dbQuery('SELECT VERSION() AS version')); ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-right">
                        /hesk_settings.inc.php
                    </td>
                    <?php
                    $heskSettingsWritable = is_writable(HESK_PATH . 'hesk_settings.inc.php');
                    $cellClass = $heskSettingsWritable ? 'success' : 'danger';
                    ?>
                    <td class="pad-right-10 <?php echo $cellClass; ?>">
                        <?php
                        if ($heskSettingsWritable) {
                            $enable_save_settings = 1;
                            echo '<font class="success">' . $hesklang['exists'] . '</font>, <font class="success">' . $hesklang['writable'] . '</font>';
                        } else {
                            echo '<font class="success">' . $hesklang['exists'] . '</font>, <font class="error">' . $hesklang['not_writable'] . '</font><br />' . $hesklang['e_settings'];
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-right">
                        /<?php echo $hesk_settings['attach_dir']; ?>
                    </td>
                    <?php
                    $attachmentsExist = is_dir(HESK_PATH . $hesk_settings['attach_dir']);
                    $attachmentsWritable = is_writable(HESK_PATH . $hesk_settings['attach_dir']);
                    $cellClass = $attachmentsExist && $attachmentsWritable ? 'success' : 'danger';
                    ?>
                    <td class="pad-right-10 <?php echo $cellClass; ?>">
                        <?php
                        if ($attachmentsExist) {
                            echo '<font class="success">' . $hesklang['exists'] . '</font>, ';
                            if (is_writable(HESK_PATH . $hesk_settings['attach_dir'])) {
                                $enable_use_attachments = 1;
                                echo '<font class="success">' . $hesklang['writable'] . '</font>';
                            } else {
                                echo '<font class="error">' . $hesklang['not_writable'] . '</font><br />' . $hesklang['e_attdir'];
                            }
                        } else {
                            echo '<font class="error">' . $hesklang['no_exists'] . '</font>, <font class="error">' . $hesklang['not_writable'] . '</font><br />' . $hesklang['e_attdir'];
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-right">
                        /<?php echo $hesk_settings['cache_dir']; ?>
                    </td>
                    <?php
                    $attachmentsExist = is_dir(HESK_PATH . $hesk_settings['cache_dir']);
                    $attachmentsWritable = is_writable(HESK_PATH . $hesk_settings['cache_dir']);
                    $cellClass = $attachmentsExist && $attachmentsWritable ? 'success' : 'danger';
                    ?>
                    <td class="pad-right-10 <?php echo $cellClass; ?>">
                        <?php
                        if ($attachmentsExist) {
                            echo '<span class="success">' . $hesklang['exists'] . '</span>, ';
                            if ($attachmentsWritable) {
                                $enable_use_attachments = 1;
                                echo '<span class="success">' . $hesklang['writable'] . '</span>';
                            } else {
                                echo '<span class="error">' . $hesklang['not_writable'] . '</span><br>' . $hesklang['e_cdir'];
                            }
                        } else {
                            echo '<span class="error">' . $hesklang['no_exists'] . '</span>, <span class="error">' . $hesklang['not_writable'] . '</span><br>' . $hesklang['e_cdir'];
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <h2>
        <?php echo $hesklang['settings']; ?>
        <a href="javascript:void(0)"
           onclick="javascript:alert('<?php echo hesk_makeJsString($hesklang['settings_intro']) . '\n\n' . hesk_makeJsString($hesklang['all_req']); ?>')"><i
                class="fa fa-question-circle settingsquestionmark"></i></a>
    </h2>
    <form method="post" enctype="multipart/form-data" action="admin_settings_save.php" name="form1" onsubmit="return hesk_checkFields()"
          class="form-horizontal" role="form">

        <!-- General Settings -->
        <a class="anchor" id="general">&nbsp;</a>
        <div class="box">
            <div class="box-header with-border">
                <h1 class="box-title">
                    <?php echo $hesklang['tab_1']; ?>
                </h1>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <h4 class="bold"><?php echo $hesklang['gs']; ?></h4>

                <div class="form-group">
                    <label for="s_site_title" class="col-sm-3 control-label"><?php echo $hesklang['wbst_title']; ?>
                        <a href="Javascript:void(0)"
                           onclick="Javascript:hesk_window('<?php echo $help_folder; ?>general.html#1','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="s_site_title" size="40" maxlength="255"
                               value="<?php echo $hesk_settings['site_title']; ?>"
                               placeholder="<?php echo htmlspecialchars($hesklang['wbst_title']); ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_site_url" class="col-sm-3 control-label"><?php echo $hesklang['wbst_url']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>general.html#2','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="s_site_url" size="40" maxlength="255"
                               value="<?php echo $hesk_settings['site_url']; ?>"
                               placeholder="<?php echo htmlspecialchars($hesklang['wbst_url']); ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="navbar_title_url" class="col-sm-3 control-label">
                                <span class="label label-primary"
                                      data-toggle="tooltip"
                                      title="<?php echo $hesklang['added_in_mods_for_hesk']; ?>"><?php echo $hesklang['mods_for_hesk_acronym']; ?></span>
                        <?php echo $hesklang['navbar_title_url']; ?>
                        <i class="fa fa-question-circle settingsquestionmark" data-toggle="htmlpopover"
                           title="<?php echo $hesklang['navbar_title_url']; ?>"
                           data-content="<?php echo $hesklang['navbar_title_url_help']; ?>"></i>
                    </label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="navbar_title_url" size="40" maxlength="255"
                               value="<?php echo $modsForHesk_settings['navbar_title_url']; ?>"
                               placeholder="<?php echo htmlspecialchars($hesklang['navbar_title_url']); ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_hesk_title" class="col-sm-3 control-label"><?php echo $hesklang['hesk_title']; ?>
                        <a href="Javascript:void(0)"
                           onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#6','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['hesk_title']); ?>"
                               name="s_hesk_title" size="40" maxlength="255"
                               value="<?php echo $hesk_settings['hesk_title']; ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_hesk_url" class="col-sm-3 control-label"><?php echo $hesklang['hesk_url']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#7','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['hesk_url']); ?>"
                               name="s_hesk_url" size="40" maxlength="255"
                               value="<?php echo $hesk_settings['hesk_url']; ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_webmaster_email"
                           class="col-sm-3 control-label"><?php echo $hesklang['email_wm']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>general.html#4','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="s_webmaster_mail" size="40" maxlength="255"
                               value="<?php echo $hesk_settings['webmaster_mail']; ?>"
                               placeholder="<?php echo htmlspecialchars($hesklang['email_wm']); ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_noreply_mail"
                           class="col-sm-3 control-label"><?php echo $hesklang['email_noreply']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>general.html#5','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="s_noreply_mail" size="40" maxlength="255"
                               value="<?php echo $hesk_settings['noreply_mail']; ?>"
                               placeholder="<?php echo htmlspecialchars($hesklang['email_noreply']); ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_noreply_name"
                           class="col-sm-3 control-label"><?php echo $hesklang['email_name']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>general.html#6','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="s_noreply_name" size="40" maxlength="255"
                               value="<?php echo $hesk_settings['noreply_name']; ?>"
                               placeholder="<?php echo htmlspecialchars($hesklang['email_name']); ?>"/>
                    </div>
                </div>

                <h4 class="bold"><?php echo $hesklang['lgs']; ?></h4>

                <div class="footerWithBorder blankSpace"></div>
                <div class="form-group">
                    <label for="s_language" class="col-sm-3 control-label"><?php echo $hesklang['hesk_lang']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>general.html#9','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <select class="form-control" name="s_language">
                            <?php echo hesk_testLanguage(1); ?>
                        </select>
                        &nbsp;
                        <a href="Javascript:void(0)"
                           onclick="Javascript:return hesk_testLanguage()"><?php echo $hesklang['s_inl']; ?></a>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_mlang" class="col-sm-3 control-label"><?php echo $hesklang['s_mlang']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>general.html#43','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <?php
                        $on = $hesk_settings['can_sel_lang'] ? 'checked="checked"' : '';
                        $off = $hesk_settings['can_sel_lang'] ? '' : 'checked="checked"';
                        echo '
                        <div class="radio"><label><input type="radio" name="s_can_sel_lang" value="0" ' . $off . ' /> ' . $hesklang['off'] . '</label></div>
                        <div class="radio"><label><input type="radio" name="s_can_sel_lang" value="1" ' . $on . ' /> ' . $hesklang['on'] . '</label></div>';
                        ?>
                    </div>
                </div>

                <h4 class="bold"><?php echo $hesklang['db']; ?></h4>

                <div class="form-group">
                    <label for="s_db_host" class="col-sm-3 control-label"><?php echo $hesklang['db_host']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>general.html#32','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <input class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['db_host']); ?>" type="text"
                               name="s_db_host" id="m1" size="40" maxlength="255"
                               value="<?php echo defined('HESK_DEMO') ? $hesklang['hdemo'] : $hesk_settings['db_host']; ?>"
                               autocomplete="off"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_db_name" class="col-sm-3 control-label"><?php echo $hesklang['db_name']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>general.html#33','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['db_name']); ?>" name="s_db_name"
                               id="m2" size="40" maxlength="255"
                               value="<?php echo defined('HESK_DEMO') ? $hesklang['hdemo'] : $hesk_settings['db_name']; ?>"
                               autocomplete="off"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_db_user" class="col-sm-3 control-label"><?php echo $hesklang['db_user']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>general.html#34','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['db_user']); ?>" name="s_db_user"
                               id="m3" size="40" maxlength="255"
                               value="<?php echo defined('HESK_DEMO') ? $hesklang['hdemo'] : $hesk_settings['db_user']; ?>"
                               autocomplete="off"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_db_pass" class="col-sm-3 control-label"><?php echo $hesklang['db_pass']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>general.html#35','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <input type="password" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['db_pass']); ?>" name="s_db_pass"
                               id="m4" size="40" maxlength="255"
                               value="<?php echo defined('HESK_DEMO') ? $hesklang['hdemo'] : $hesk_settings['db_pass']; ?>"
                               autocomplete="off"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_db_pfix" class="col-sm-3 control-label"><?php echo $hesklang['prefix']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>general.html#36','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['prefix']); ?>" name="s_db_pfix"
                               id="m5" size="40" maxlength="255"
                               value="<?php echo defined('HESK_DEMO') ? $hesklang['hdemo'] : $hesk_settings['db_pfix']; ?>"
                               autocomplete="off"/>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-3">
                        <input type="button" class="btn btn-default move-down-4" onclick="hesk_testMySQL()"
                               value="<?php echo $hesklang['mysqltest']; ?>"/>
                        <!-- START MYSQL TEST -->
                        <div id="mysql_test" style="display: none">
                        </div>

                        <script language="Javascript" type="text/javascript">
                            function hesk_testMySQL() {
                                var element = document.getElementById('mysql_test');
                                element.innerHTML = '<img src="<?php echo HESK_PATH; ?>img/loading.gif" width="24" height="24" alt="" border="0" style="vertical-align:text-bottom" /> <i><?php echo addslashes($hesklang['contest']); ?></i>';
                                element.style.display = 'block';

                                var s_db_host = document.getElementById('m1').value;
                                var s_db_name = document.getElementById('m2').value;
                                var s_db_user = document.getElementById('m3').value;
                                var s_db_pass = document.getElementById('m4').value;
                                var s_db_pfix = document.getElementById('m5').value;

                                var params = "test=mysql" +
                                    "&s_db_host=" + encodeURIComponent(s_db_host) +
                                    "&s_db_name=" + encodeURIComponent(s_db_name) +
                                    "&s_db_user=" + encodeURIComponent(s_db_user) +
                                    "&s_db_pass=" + encodeURIComponent(s_db_pass) +
                                    "&s_db_pfix=" + encodeURIComponent(s_db_pfix);

                                xmlHttp = GetXmlHttpObject();
                                if (xmlHttp == null) {
                                    return;
                                }

                                xmlHttp.open('POST', 'test_connection.php', true);
                                xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                                xmlHttp.setRequestHeader("Content-length", params.length);
                                xmlHttp.setRequestHeader("Connection", "close");

                                xmlHttp.onreadystatechange = function () {
                                    if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
                                        element.innerHTML = xmlHttp.responseText;
                                    }
                                }

                                xmlHttp.send(params);
                            }
                        </script>
                        <!-- END MYSQL TEST -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Helpdesk Settings -->
        <a class="anchor" id="helpdesk">&nbsp;</a>
        <div class="box">
            <div class="box-header with-border">
                <h1 class="box-title">
                    <?php echo $hesklang['tab_2']; ?>
                </h1>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <h4 class="bold"><?php echo $hesklang['hd']; ?></h4>
                <div class="form-group">
                    <label for="s_admin_dir" class="col-sm-3 control-label"><?php echo $hesklang['adf']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#61','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['adf']); ?>" name="s_admin_dir"
                               size="40" maxlength="255" value="<?php echo $hesk_settings['admin_dir']; ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_attach_dir"
                           class="col-sm-3 control-label"><?php echo $hesklang['ticket_attach_dir']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#62','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['ticket_attach_dir']); ?>"
                               name="s_attach_dir" size="40" maxlength="255"
                               value="<?php echo $hesk_settings['attach_dir']; ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_cache_dir"
                           class="col-sm-3 control-label"><?php echo $hesklang['cf']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#77','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['ticket_attach_dir']); ?>"
                               name="s_cache_dir" size="40" maxlength="255"
                               value="<?php echo $hesk_settings['cache_dir']; ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_max_listings"
                           class="col-sm-3 control-label"><?php echo $hesklang['max_listings']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#10','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-3">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['max_listings']); ?>"
                               name="s_max_listings" size="5" maxlength="30"
                               value="<?php echo $hesk_settings['max_listings']; ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_print_font_size"
                           class="col-sm-3 control-label"><?php echo $hesklang['print_size']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#11','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-3">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['print_size']); ?>"
                               name="s_print_font_size" size="5" maxlength="3"
                               value="<?php echo $hesk_settings['print_font_size']; ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_autoclose" class="col-sm-3 control-label"><?php echo $hesklang['aclose']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#15','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-3">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['aclose']); ?>" name="s_autoclose"
                               size="5" maxlength="3"
                               value="<?php echo $hesk_settings['autoclose']; ?>"/><?php echo $hesklang['aclose2']; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_max_open" class="col-sm-3 control-label"><?php echo $hesklang['mop']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#58','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-3">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['mop']); ?>" name="s_max_open"
                               size="5" maxlength="3" value="<?php echo $hesk_settings['max_open']; ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="new_top" class="col-sm-3 control-label"><?php echo $hesklang['rord']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#59','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <?php
                        $on = $hesk_settings['new_top'] ? 'checked="checked"' : '';
                        $off = $hesk_settings['new_top'] ? '' : 'checked="checked"';
                        echo '
                        <div class="radio"><label><input type="radio" name="s_new_top" value="1" ' . $on . ' /> ' . $hesklang['newtop'] . '</label></div>
                        <div class="radio"><label><input type="radio" name="s_new_top" value="0" ' . $off . ' /> ' . $hesklang['newbot'] . '</label></div>';
                        ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_reply_top" class="col-sm-3 control-label"><?php echo $hesklang['ford']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#60','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <?php
                        $on = $hesk_settings['reply_top'] ? 'checked="checked"' : '';
                        $off = $hesk_settings['reply_top'] ? '' : 'checked="checked"';
                        echo '
                        <div class="radio"><label><input type="radio" name="s_reply_top" value="1" ' . $on . ' /> ' . $hesklang['formtop'] . '</label></div>
                        <div class="radio"><label><input type="radio" name="s_reply_top" value="0" ' . $off . ' /> ' . $hesklang['formbot'] . '</label></div>';
                        ?>
                    </div>
                </div>

                <h4 class="bold"><?php echo $hesklang['features']; ?></h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="s_autologin" class="col-sm-6 control-label"><?php echo $hesklang['alo']; ?>
                                <a href="Javascript:void(0)"
                                   onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#44','400','500')"><i
                                        class="fa fa-question-circle settingsquestionmark"></i></a></label>

                            <div class="col-sm-6 form-inline">
                                <?php
                                $on = $hesk_settings['autologin'] ? 'checked="checked"' : '';
                                $off = $hesk_settings['autologin'] ? '' : 'checked="checked"';
                                echo '
                                <div class="radio"><label><input type="radio" name="s_autologin" value="0" ' . $off . ' /> ' . $hesklang['off'] . '</div>&nbsp;&nbsp;&nbsp;
                                <div class="radio"><label><input type="radio" name="s_autologin" value="1" ' . $on . ' /> ' . $hesklang['on'] . '</div>';
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="s_require_email" class="col-sm-6 control-label"><?php echo $hesklang['req_email']; ?>
                                <a href="Javascript:void(0)"
                                   onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#73','400','500')"><i
                                        class="fa fa-question-circle settingsquestionmark"></i></a></label>

                            <div class="col-sm-6 form-inline">
                                <?php
                                $on = $hesk_settings['require_email'] ? 'checked="checked"' : '';
                                $off = $hesk_settings['require_email'] ? '' : 'checked="checked"';
                                echo '
                                <div class="radio"><label><input type="radio" id="s_require_email_0" name="s_require_email" value="0" onclick="return checkRequiredEmail(\'s_require_email_0\');" ' . $off . ' /> ' . $hesklang['off'] . '</div>&nbsp;&nbsp;&nbsp;
                                <div class="radio"><label><input type="radio" id="s_require_email_1" name="s_require_email" value="1" ' . $on . ' /> ' . $hesklang['on'] . '</div>';
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="s_require_owner" class="col-sm-6 control-label"><?php echo $hesklang['fass']; ?>
                                <a href="Javascript:void(0)"
                                   onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#70','400','500')"><i
                                        class="fa fa-question-circle settingsquestionmark"></i></a></label>

                            <div class="col-sm-6 form-inline">
                                <?php
                                $on = $hesk_settings['require_owner'] ? 'checked="checked"' : '';
                                $off = $hesk_settings['require_owner'] ? '' : 'checked="checked"';
                                echo '
                                <div class="radio"><label><input type="radio" name="s_require_owner" value="0" ' . $off . '> ' . $hesklang['off'] . '</div>&nbsp;&nbsp;&nbsp;
                                <div class="radio"><label><input type="radio" name="s_require_owner" value="1" ' . $on . '> ' . $hesklang['on'] . '</div>';
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="s_require_message"
                                   class="col-sm-6 control-label"><?php echo $hesklang['req_msg']; ?> <a
                                    href="Javascript:void(0)"
                                    onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#74','400','500')"><i
                                        class="fa fa-question-circle settingsquestionmark"></i></a></label>

                            <div class="col-sm-6 form-inline">
                                <?php
                                $on = $hesk_settings['require_message'] == 1 ? 'checked="checked"' : '';
                                $off = $hesk_settings['require_message'] == 0 ? 'checked="checked"' : '';
                                $hide = $hesk_settings['require_message'] == -1 ? 'checked="checked"' : '';
                                echo '
                                <div class="radio"><label><input type="radio" name="s_require_message" value="0" ' . $off . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                                <div class="radio"><label><input type="radio" name="s_require_message" value="1" ' . $on . ' /> ' . $hesklang['on'] . '</label></div>&nbsp;&nbsp;&nbsp;
                                <div class="radio"><label><input type="radio" name="s_require_message" value="-1" ' . $hide . ' /> ' . $hesklang['off-hide'] . '</label></div>';
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="s_custclose" class="col-sm-6 control-label"><?php echo $hesklang['ccct']; ?>
                                <a href="Javascript:void(0)"
                                   onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#67','400','500')"><i
                                        class="fa fa-question-circle settingsquestionmark"></i></a></label>

                            <div class="col-sm-6 form-inline">
                                <?php
                                $on = $hesk_settings['custclose'] ? 'checked="checked"' : '';
                                $off = $hesk_settings['custclose'] ? '' : 'checked="checked"';
                                echo '
                                <div class="radio"><label><input type="radio" name="s_custclose" value="0" ' . $off . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                                <div class="radio"><label><input type="radio" name="s_custclose" value="1" ' . $on . ' /> ' . $hesklang['on'] . '</label></div>';
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="s_custopen"
                                   class="col-sm-6 control-label"><?php echo $hesklang['s_ucrt']; ?> <a
                                    href="Javascript:void(0)"
                                    onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#16','400','500')"><i
                                        class="fa fa-question-circle settingsquestionmark"></i></a></label>

                            <div class="col-sm-6 form-inline">
                                <?php
                                $on = $hesk_settings['custopen'] ? 'checked="checked"' : '';
                                $off = $hesk_settings['custopen'] ? '' : 'checked="checked"';
                                echo '
                                <div class="radio"><label><input type="radio" name="s_custopen" value="0" ' . $off . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                                <div class="radio"><label><input type="radio" name="s_custopen" value="1" ' . $on . ' /> ' . $hesklang['on'] . '</label></div>';
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="s_cust_urgency"
                                   class="col-sm-6 control-label"><?php echo $hesklang['cpri']; ?> <a
                                    href="Javascript:void(0)"
                                    onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#45','400','500')"><i
                                        class="fa fa-question-circle settingsquestionmark"></i></a></label>

                            <div class="col-sm-6 form-inline">
                                <?php
                                $on = $hesk_settings['cust_urgency'] ? 'checked="checked"' : '';
                                $off = $hesk_settings['cust_urgency'] ? '' : 'checked="checked"';
                                echo '
                                <div class="radio"><label><input type="radio" name="s_cust_urgency" value="0" ' . $off . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                                <div class="radio"><label><input type="radio" name="s_cust_urgency" value="1" ' . $on . ' /> ' . $hesklang['on'] . '</label></div>';
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="s_time_worked" class="col-sm-6 control-label"><?php echo $hesklang['ts']; ?>
                                <a href="Javascript:void(0)"
                                   onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#66','400','500')"><i
                                        class="fa fa-question-circle settingsquestionmark"></i></a></label>

                            <div class="col-sm-6 form-inline">
                                <?php
                                $on = $hesk_settings['time_worked'] ? 'checked="checked"' : '';
                                $off = $hesk_settings['time_worked'] ? '' : 'checked="checked"';
                                echo '
                                <div class="radio"><label><input type="radio" name="s_time_worked" value="0" ' . $off . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                                <div class="radio"><label><input type="radio" name="s_time_worked" value="1" ' . $on . ' /> ' . $hesklang['on'] . '</label></div>';
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="s_spam_notice"
                                   class="col-sm-6 control-label"><?php echo $hesklang['spamn']; ?> <a
                                    href="Javascript:void(0)"
                                    onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#68','400','500')"><i
                                        class="fa fa-question-circle settingsquestionmark"></i></a></label>

                            <div class="col-sm-6 form-inline">
                                <?php
                                $on = $hesk_settings['spam_notice'] ? 'checked="checked"' : '';
                                $off = $hesk_settings['spam_notice'] ? '' : 'checked="checked"';
                                echo '
                                <div class="radio"><label><input type="radio" name="s_spam_notice" value="0" ' . $off . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                                <div class="radio"><label><input type="radio" name="s_spam_notice" value="1" ' . $on . ' /> ' . $hesklang['on'] . '</label></div>';
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="s_list_users" class="col-sm-6 control-label"><?php echo $hesklang['lu']; ?>
                                <a href="Javascript:void(0)"
                                   onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#14','400','500')"><i
                                        class="fa fa-question-circle settingsquestionmark"></i></a></label>

                            <div class="col-sm-6 form-inline">
                                <?php
                                $on = $hesk_settings['list_users'] ? 'checked="checked"' : '';
                                $off = $hesk_settings['list_users'] ? '' : 'checked="checked"';
                                echo '
                                <div class="radio"><label><input type="radio" name="s_list_users" value="0" ' . $off . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                                <div class="radio"><label><input type="radio" name="s_list_users" value="1" ' . $on . ' /> ' . $hesklang['on'] . '</label></div>';
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="category_order_column" class="col-sm-6 control-label">
                                <span class="label label-primary"
                                      data-toggle="tooltip"
                                      title="<?php echo $hesklang['added_in_mods_for_hesk']; ?>"><?php echo $hesklang['mods_for_hesk_acronym']; ?></span>
                                <?php echo $hesklang['category_sort']; ?>
                                <i class="fa fa-question-circle settingsquestionmark" data-toggle="htmlpopover"
                                   title="<?php echo $hesklang['category_sort']; ?>"
                                   data-content="<?php echo $hesklang['category_sort_help']; ?>"></i>
                            </label>

                            <div class="col-sm-6">
                                <?php
                                $on = $modsForHesk_settings['category_order_column'] == 'name' ? 'checked' : '';
                                $off = $modsForHesk_settings['category_order_column'] == 'name' ? '' : 'checked';
                                echo '
								<div class="radio"><label><input type="radio" name="category_order_column" value="0" ' . $off . '>' . $hesklang['sort_by_user_defined_order'] . '</label></div>
								<div class="radio"><label><input type="radio" name="category_order_column" value="1" ' . $on . '>' . $hesklang['sort_alphabetically'] . '</label></div>
								';
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="rich_text_for_tickets" class="col-sm-6 control-label">
                                <span class="label label-primary"
                                      data-toggle="tooltip"
                                      title="<?php echo $hesklang['added_in_mods_for_hesk']; ?>"><?php echo $hesklang['mods_for_hesk_acronym']; ?></span>
                                <?php echo $hesklang['allow_rich_text_for_tickets']; ?>
                                <i class="fa fa-question-circle settingsquestionmark" data-toggle="htmlpopover"
                                   title="<?php echo $hesklang['allow_rich_text_for_tickets']; ?>"
                                   data-content="<?php echo $hesklang['allow_rich_text_for_tickets_help']; ?>"></i>
                            </label>

                            <div class="col-sm-6 form-inline">
                                <?php
                                $both = $modsForHesk_settings['rich_text_for_tickets'] && $modsForHesk_settings['rich_text_for_tickets_for_customers'] ? 'checked' : '';
                                $staff = $modsForHesk_settings['rich_text_for_tickets'] && !$modsForHesk_settings['rich_text_for_tickets_for_customers'] ? 'checked' : '';
                                $no = $modsForHesk_settings['rich_text_for_tickets'] && $modsForHesk_settings['rich_text_for_tickets_for_customers'] ? '' : 'checked';
                                echo '
								<div class="radio"><label><input type="radio" name="rich_text_for_tickets" value="0" ' . $no . '> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
								<div class="radio"><label><input type="radio" name="rich_text_for_tickets" value="1" ' . $staff . '> ' . $hesklang['staff_only'] . '</label></div>&nbsp;&nbsp;&nbsp;
								<div class="radio"><label><input type="radio" name="rich_text_for_tickets" value="2" ' . $both . '> ' . $hesklang['on'] . '</label></div>
								';
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="statuses_order_column" class="col-sm-6 control-label">
                                <span class="label label-primary"
                                      data-toggle="tooltip"
                                      title="<?php echo $hesklang['added_in_mods_for_hesk']; ?>"><?php echo $hesklang['mods_for_hesk_acronym']; ?></span>
                                <?php echo $hesklang['status_sort']; ?>
                                <i class="fa fa-question-circle settingsquestionmark" data-toggle="htmlpopover"
                                   title="<?php echo $hesklang['status_sort']; ?>"
                                   data-content="<?php echo $hesklang['status_sort_help']; ?>"></i>
                            </label>

                            <div class="col-sm-6">
                                <?php
                                $on = $modsForHesk_settings['statuses_order_column'] == 'name' ? 'checked' : '';
                                $off = $modsForHesk_settings['statuses_order_column'] == 'name' ? '' : 'checked';
                                echo '
								<div class="radio"><label><input type="radio" name="statuses_order_column" value="0" ' . $off . '>' . $hesklang['sort_by_user_defined_order'] . '</label></div>
								<div class="radio"><label><input type="radio" name="statuses_order_column" value="1" ' . $on . '>' . $hesklang['sort_alphabetically'] . '</label></div>
								';
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="display_user_agent_information" class="col-sm-6 control-label">
                                <span class="label label-primary"
                                      data-toggle="tooltip"
                                      title="<?php echo $hesklang['added_in_mods_for_hesk']; ?>"><?php echo $hesklang['mods_for_hesk_acronym']; ?></span>
                                <?php echo $hesklang['display_user_agent_information']; ?>
                                <i class="fa fa-question-circle settingsquestionmark" data-toggle="htmlpopover"
                                   title="<?php echo $hesklang['display_user_agent_information']; ?>"
                                   data-content="<?php echo $hesklang['display_user_agent_information_help']; ?>"></i>
                            </label>

                            <div class="col-sm-6">
                                <?php
                                $on = $modsForHesk_settings['display_user_agent_information'] ? 'checked' : '';
                                $off = $modsForHesk_settings['display_user_agent_information'] ? '' : 'checked';
                                echo '
								<div class="radio"><label><input type="radio" name="display_user_agent_information" value="0" ' . $off . '>' . $hesklang['no'] . '</label></div>
								<div class="radio"><label><input type="radio" name="display_user_agent_information" value="1" ' . $on . '>' . $hesklang['yes'] . '</label></div>
								';
                                ?>
                            </div>
                        </div>
                    </div>
                    <!-- Second column -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="s_autoassign"
                                   class="col-sm-6 control-label"><?php echo $hesklang['saass']; ?> <a
                                    href="Javascript:void(0)"
                                    onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#51','400','500')"><i
                                        class="fa fa-question-circle settingsquestionmark"></i></a></label>

                            <div class="col-sm-6 form-inline">
                                <?php
                                $on = $hesk_settings['autoassign'] ? 'checked="checked"' : '';
                                $off = $hesk_settings['autoassign'] ? '' : 'checked="checked"';
                                echo '
                                <div class="radio"><label><input type="radio" name="s_autoassign" value="0" ' . $off . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                                <div class="radio"><label><input type="radio" name="s_autoassign" value="1" ' . $on . ' /> ' . $hesklang['on'] . '</label></div>';
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="s_require_subject"
                                   class="col-sm-6 control-label"><?php echo $hesklang['req_sub']; ?> <a
                                    href="Javascript:void(0)"
                                    onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#72','400','500')"><i
                                        class="fa fa-question-circle settingsquestionmark"></i></a></label>

                            <div class="col-sm-6 form-inline">
                                <?php
                                $on = $hesk_settings['require_subject'] == 1 ? 'checked="checked"' : '';
                                $off = $hesk_settings['require_subject'] == 0 ? 'checked="checked"' : '';
                                $hide = $hesk_settings['require_subject'] == -1 ? 'checked="checked"' : '';
                                echo '
                                <div class="radio"><label><input type="radio" name="s_require_subject" value="0" ' . $off . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                                <div class="radio"><label><input type="radio" name="s_require_subject" value="1" ' . $on . ' /> ' . $hesklang['on'] . '</label></div>&nbsp;&nbsp;&nbsp;
                                <div class="radio"><label><input type="radio" name="s_require_subject" value="-1" ' . $hide . ' /> ' . $hesklang['off-hide'] . '</label></div>';
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="s_rating" class="col-sm-6 control-label"><?php echo $hesklang['urate']; ?>
                                <a href="Javascript:void(0)"
                                   onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#17','400','500')"><i
                                        class="fa fa-question-circle settingsquestionmark"></i></a></label>

                            <div class="col-sm-6 form-inline">
                                <?php
                                $on = $hesk_settings['rating'] ? 'checked="checked"' : '';
                                $off = $hesk_settings['rating'] ? '' : 'checked="checked"';
                                echo '
                                <div class="radio"><label><input type="radio" name="s_rating" value="0" ' . $off . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                                <div class="radio"><label><input type="radio" name="s_rating" value="1" ' . $on . ' /> ' . $hesklang['on'] . '</label></div>';
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="s_sequential"
                                   class="col-sm-6 control-label"><?php echo $hesklang['eseqid']; ?> <a
                                    href="Javascript:void(0)"
                                    onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#49','400','500')"><i
                                        class="fa fa-question-circle settingsquestionmark"></i></a></label>

                            <div class="col-sm-6 form-inline">
                                <?php
                                $on = $hesk_settings['sequential'] ? 'checked="checked"' : '';
                                $off = $hesk_settings['sequential'] ? '' : 'checked="checked"';
                                echo '
                                <div class="radio"><label><input type="radio" name="s_sequential" value="0" ' . $off . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                                <div class="radio"><label><input type="radio" name="s_sequential" value="1" ' . $on . ' /> ' . $hesklang['on'] . '</label></div>';
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="s_debug_mode"
                                   class="col-sm-6 control-label"><?php echo $hesklang['debug_mode']; ?> <a
                                    href="Javascript:void(0)"
                                    onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#12','400','500')"><i
                                        class="fa fa-question-circle settingsquestionmark"></i></a></label>

                            <div class="col-sm-6 form-inline">
                                <?php
                                $on = $hesk_settings['debug_mode'] ? 'checked="checked"' : '';
                                $off = $hesk_settings['debug_mode'] ? '' : 'checked="checked"';
                                echo '
                                <div class="radio"><label><input type="radio" name="s_debug_mode" value="0" ' . $off . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                                <div class="radio"><label><input type="radio" name="s_debug_mode" value="1" ' . $on . ' /> ' . $hesklang['on'] . '</label></div>';
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="s_short_link" class="col-sm-6 control-label"><?php echo $hesklang['shu']; ?>
                                <a href="Javascript:void(0)"
                                   onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#63','400','500')"><i
                                        class="fa fa-question-circle settingsquestionmark"></i></a></label>

                            <div class="col-sm-6 form-inline">
                                <?php
                                $on = $hesk_settings['short_link'] ? 'checked="checked"' : '';
                                $off = $hesk_settings['short_link'] ? '' : 'checked="checked"';
                                echo '
                                <div class="radio"><label><input type="radio" name="s_short_link" value="0" ' . $off . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                                <div class="radio"><label><input type="radio" name="s_short_link" value="1" ' . $on . ' /> ' . $hesklang['on'] . '</label></div>';
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="request_location" class="col-sm-6 control-label">
                                <span class="label label-primary"
                                      data-toggle="tooltip"
                                      title="<?php echo $hesklang['added_in_mods_for_hesk']; ?>"><?php echo $hesklang['mods_for_hesk_acronym']; ?></span>
                                <?php echo $hesklang['request_user_location']; ?>
                                <i class="fa fa-question-circle settingsquestionmark" data-toggle="htmlpopover"
                                   title="<?php echo $hesklang['request_user_location']; ?>"
                                   data-content="<?php echo $hesklang['request_user_location_help']; ?>"></i>
                            </label>

                            <div class="col-sm-6 form-inline">
                                <?php
                                $on = $modsForHesk_settings['request_location'] ? 'checked' : '';
                                $off = $modsForHesk_settings['request_location'] ? '' : 'checked';
                                echo '
                                <div class="radio"><label><input type="radio" name="request_location" value="0" ' . $off . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                                <div class="radio"><label><input type="radio" name="request_location" value="1" ' . $on . ' /> ' . $hesklang['on'] . '</label></div>';
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-6 control-label"><?php echo $hesklang['select']; ?> <a
                                    href="Javascript:void(0)"
                                    onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#65','400','500')"><i
                                        class="fa fa-question-circle settingsquestionmark"></i></a></label>

                            <div class="col-sm-6">
                                <div class="checkbox">
                                    <label><input type="checkbox" name="s_select_cat"
                                                  value="1" <?php if ($hesk_settings['select_cat']) {
                                            echo 'checked="checked"';
                                        } ?>/> <?php echo $hesklang['category']; ?></label>
                                </div>
                                <div class="checkbox">
                                    <label><input type="checkbox" name="s_select_pri"
                                                  value="1" <?php if ($hesk_settings['select_pri']) {
                                            echo 'checked="checked"';
                                        } ?>/> <?php echo $hesklang['priority']; ?></label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="quick_help_sections[]" class="col-sm-6 control-label">
                                <span class="label label-primary"
                                      data-toggle="tooltip"
                                      title="<?php echo $hesklang['added_in_mods_for_hesk']; ?>"><?php echo $hesklang['mods_for_hesk_acronym']; ?></span>
                                <?php echo $hesklang['quick_help_sections']; ?>
                                <i class="fa fa-question-circle settingsquestionmark" data-toggle="htmlpopover"
                                   title="<?php echo $hesklang['quick_help_sections']; ?>"
                                   data-content="<?php echo htmlspecialchars($hesklang['quick_help_sections_help']); ?>"></i>
                            </label>

                            <div class="col-sm-6">
                                <?php
                                $quickHelpRs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections`");
                                while ($row = hesk_dbFetchAssoc($quickHelpRs)): ?>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="quick_help_sections[]"
                                                   value="<?php echo $row['id']; ?>" <?php if ($row['show']) {
                                                echo 'checked';
                                            } ?>>
                                            <?php echo $hesklang[$row['location']]; ?>
                                        </label>
                                    </div>
                                    <?php
                                endwhile;
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="s_cat_show_select" class="col-sm-6 control-label"><?php echo $hesklang['scat']; ?> <a
                                    href="Javascript:void(0)"
                                    onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#71','400','500')"><i
                                        class="fa fa-question-circle settingsquestionmark"></i></a></label>

                            <div class="col-sm-6">
                                <input type="text" class="form-control"
                                       placeholder="<?php echo htmlspecialchars($hesklang['scat']); ?>" name="s_cat_show_select"
                                       size="5" maxlength="3" value="<?php echo $hesk_settings['cat_show_select']; ?>">
                                <?php echo $hesklang['scat2']; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <h4 class="bold"><?php echo $hesklang['sp']; ?></h4>
                <div class="form-group">
                    <label for="s_secimg_use" class="col-sm-3 control-label"><?php echo $hesklang['use_secimg']; ?>
                        <a href="Javascript:void(0)"
                           onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#13','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9 form-inline">
                        <?php
                        $onc = $hesk_settings['secimg_use'] == 1 ? 'checked="checked"' : '';
                        $ons = $hesk_settings['secimg_use'] == 2 ? 'checked="checked"' : '';
                        $off = $hesk_settings['secimg_use'] ? '' : 'checked="checked"';
                        $div = $hesk_settings['secimg_use'] ? 'block' : 'none';

                        echo '
                        <div class="radio"><label><input type="radio" name="s_secimg_use" value="0" ' . $off . ' onclick="javascript:hesk_toggleLayer(\'captcha\',\'none\')" /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                        <div class="radio"><label><input type="radio" name="s_secimg_use" value="1" ' . $onc . ' onclick="javascript:hesk_toggleLayer(\'captcha\',\'block\')" /> ' . $hesklang['onc'] . '</label></div>&nbsp;&nbsp;&nbsp;
                        <div class="radio"><label><input type="radio" name="s_secimg_use" value="2" ' . $ons . ' onclick="javascript:hesk_toggleLayer(\'captcha\',\'block\')" /> ' . $hesklang['ons'] . '</label></div>
                        ';

                        ?>
                        <div id="captcha" style="display: <?php echo $div; ?>;">

                            &nbsp;<br/>

                            <b><?php echo $hesklang['sit']; ?>:</b><br/>

                            <?php

                            $on = '';
                            $on2 = '';
                            $off = '';
                            $div = 'block';

                            if ($hesk_settings['recaptcha_use'] == 1) {
                                $on = 'checked="checked"';
                            } elseif ($hesk_settings['recaptcha_use'] == 2) {
                                $on2 = 'checked="checked"';
                            } else {
                                $off = 'checked="checked"';
                                $div = 'none';
                            }
                            ?>

                            <div class="radio"><label><input type="radio" name="s_recaptcha_use" value="0"
                                                             onclick="javascript:hesk_toggleLayer('recaptcha','none')" <?php echo $off; ?> /> <?php echo $hesklang['sis']; ?>
                                </label></div>
                            <br/>

                            <div class="radio"><label><input type="radio" name="s_recaptcha_use" value="2"
                                                             onclick="javascript:hesk_toggleLayer('recaptcha','block')" <?php echo $on2; ?> /> <?php echo $hesklang['recaptcha']; ?>
                                </label> <a href="Javascript:void(0)"
                                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#64','400','500')"><i
                                        class="fa fa-question-circle settingsquestionmark"></i></a></div>
                            <br/>

                            <div class="radio"><label><input type="radio" name="s_recaptcha_use" value="1"
                                                             onclick="javascript:hesk_toggleLayer('recaptcha','block')" <?php echo $on; ?> /> <?php echo $hesklang['sir3']; ?>
                                </label> <a href="Javascript:void(0)"
                                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#64','400','500')"><i
                                        class="fa fa-question-circle settingsquestionmark"></i></a></div>
                            <br/>

                            <div id="recaptcha" style="display: <?php echo $div; ?>;">

                                &nbsp;<br/>

                                <label for="s_recaptcha_public_key"
                                       class="control-label"><?php echo $hesklang['rcpb']; ?> <a
                                        href="Javascript:void(0)"
                                        onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#64','400','500')"><i
                                            class="fa fa-question-circle settingsquestionmark"></i></a></label>
                                <input type="text" class="form-control"
                                       placeholder="<?php echo htmlspecialchars($hesklang['rcpb']); ?>"
                                       name="s_recaptcha_public_key" size="50" maxlength="255"
                                       value="<?php echo $hesk_settings['recaptcha_public_key']; ?>"/><br/>
                                &nbsp;<br/>

                                <label for="s_recaptcha_private_key"
                                       class="control-label"><?php echo $hesklang['rcpv']; ?> <a
                                        href="Javascript:void(0)"
                                        onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#64','400','500')"><i
                                            class="fa fa-question-circle settingsquestionmark"></i></a></label>
                                <input type="text" class="form-control"
                                       placeholder="<?php echo htmlspecialchars($hesklang['rcpv']); ?>"
                                       name="s_recaptcha_private_key" size="50" maxlength="255"
                                       value="<?php echo $hesk_settings['recaptcha_private_key']; ?>"/><br/>
                                &nbsp;<br/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_question_use" class="col-sm-3 control-label"><?php echo $hesklang['use_q']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#42','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9 form-inline">
                        <?php
                        $on = '';
                        $off = '';
                        $div = 'block';

                        if ($hesk_settings['question_use']) {
                            $on = 'checked="checked"';
                        } else {
                            $off = 'checked="checked"';
                            $div = 'none';
                        }
                        echo '
                    <div class="radio"><label><input type="radio" name="s_question_use" value="0" ' . $off . ' onclick="javascript:hesk_toggleLayer(\'question\',\'none\')" /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                    <div class="radio"><label><input type="radio" name="s_question_use" value="1" ' . $on . ' onclick="javascript:hesk_toggleLayer(\'question\',\'block\')" /> ' . $hesklang['on'] . '</label></div>';
                        ?>

                        <div id="question" style="display: <?php echo $div; ?>;">
                            &nbsp;<br/>
                            <a href="Javascript:void(0)"
                               onclick="Javascript:hesk_rate('generate_spam_question.php','question')"><?php echo $hesklang['genq']; ?></a><br/>

                            <label for="s_question_ask"
                                   class="control-label"><?php echo $hesklang['q_q']; ?></label><br/>
                                <textarea name="s_question_ask" class="form-control" rows="3"
                                          cols="40"><?php echo hesk_htmlentities($hesk_settings['question_ask']); ?></textarea><br/>
                            &nbsp;<br/>

                            <label for="s_question_ans"
                                   class="control-label"><?php echo $hesklang['q_a']; ?></label><br/>
                            <input type="text" class="form-control" name="s_question_ans"
                                   value="<?php echo $hesk_settings['question_ans']; ?>" size="10"/><br/>
                            &nbsp;<br/>

                        </div>
                    </div>
                </div>
                <h4 class="bold"><?php echo $hesklang['security']; ?></h4>
                <div class="form-group">
                    <label for="s_attempt_limit" class="col-sm-4 control-label"><?php echo $hesklang['banlim']; ?>
                        <a href="Javascript:void(0)"
                           onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#47','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-3">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['banlim']); ?>"
                               name="s_attempt_limit" size="5" maxlength="30"
                               value="<?php echo($hesk_settings['attempt_limit'] ? ($hesk_settings['attempt_limit'] - 1) : 0); ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_attempt_banmin" class="col-sm-4 control-label"><?php echo $hesklang['banmin']; ?>
                        <a href="Javascript:void(0)"
                           onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#47','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-3">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['banmin']); ?>"
                               name="s_attempt_banmin" size="5" maxlength="3"
                               value="<?php echo $hesk_settings['attempt_banmin']; ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_reset_pass" class="col-sm-4 control-label"><?php echo $hesklang['passr']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#69','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8">
                        <div class="checkbox">
                            <label><input type="checkbox" name="s_reset_pass"
                                          value="1" <?php if ($hesk_settings['reset_pass']) {
                                    echo 'checked="checked"';
                                } ?>/> <?php echo $hesklang['passa']; ?></label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_email_view_ticket"
                           class="col-sm-4 control-label"><?php echo $hesklang['viewvtic']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#46','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8">
                        <div class="checkbox">
                            <label><input type="checkbox" name="s_email_view_ticket"
                                          id="s_email_view_ticket" onclick="return checkRequiredEmail('s_email_view_ticket');"
                                          value="1" <?php if ($hesk_settings['email_view_ticket']) {
                                    echo 'checked="checked"';
                                } ?>/> <?php echo $hesklang['reqetv']; ?></label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_x_frame_opt"
                           class="col-sm-4 control-label"><?php echo $hesklang['frames']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#76','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8">
                        <div class="checkbox">
                            <label><input type="checkbox" name="s_x_frame_opt"
                                          value="1" <?php if ($hesk_settings['x_frame_opt']) {
                                    echo 'checked="checked"';} ?>> <?php echo $hesklang['frames2']; ?></label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_force_ssl"
                           class="col-sm-4 control-label"><?php echo $hesklang['ssl']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#75','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8">
                        <div class="checkbox">
                            <?php if (HESK_SSL): ?>
                            <label><input type="checkbox" name="s_force_ssl"
                                          value="1" <?php if ($hesk_settings['force_ssl']) {
                                    echo 'checked="checked"';} ?>>
                                <?php echo $hesklang['frames2']; ?>
                            </label>
                            <?php else: echo $hesklang['d_ssl']; ?>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email-verification" class="col-sm-4 col-xs-12 control-label">
                    <span class="label label-primary"
                          data-toggle="tooltip"
                          title="<?php echo $hesklang['added_in_mods_for_hesk']; ?>"><?php echo $hesklang['mods_for_hesk_acronym']; ?></span>
                        <?php echo $hesklang['customer_email_verification']; ?>
                        <i class="fa fa-question-circle settingsquestionmark" data-toggle="htmlpopover"
                           title="<?php echo $hesklang['customer_email_verification']; ?>"
                           data-content="<?php echo $hesklang['customer_email_verification_help']; ?>"></i>
                    </label>

                    <div class="col-sm-8 col-xs-12">
                        <div class="checkbox">
                            <label>
                                <input id="email-verification" name="email-verification"
                                       type="checkbox" <?php if ($modsForHesk_settings['customer_email_verification_required']) {
                                    echo 'checked';
                                } ?>> <?php echo $hesklang['require_customer_validate_email']; ?>
                            </label>
                        </div>
                    </div>
                </div>
                <h4 class="bold">
                    <span class="label label-primary"
                          data-toggle="tooltip"
                          title="<?php echo $hesklang['added_in_mods_for_hesk']; ?>"><?php echo $hesklang['mods_for_hesk_acronym']; ?></span>
                    <?php echo $hesklang['ldap_ad_settings']; ?>
                </h4>
                <div class="form-group">
                    <label for="use_ldap" class="col-sm-4 control-label">
                        <?php echo $hesklang['login_using_ldap']; ?>
                        <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                           title="<?php echo $hesklang['login_using_ldap']; ?>"
                           data-content="<?php echo hesk_htmlspecialchars($hesklang['login_using_ldap_help']); ?>"></i>
                    </label>
                    <div class="col-sm-8 form-inline">
                        <?php
                        $on = $modsForHesk_settings['use_ldap'] ? 'checked="checked"' : '';
                        $off = $modsForHesk_settings['use_ldap'] ? '' : 'checked="checked"';
                        echo '
                                <div class="radio"><label><input onclick="$(\'#ldap_dn_group\').hide()" type="radio" name="use_ldap" value="0" ' . $off . '> ' . $hesklang['off'] . '</div>&nbsp;&nbsp;&nbsp;
                                <div class="radio"><label><input onclick="$(\'#ldap_dn_group\').show()" type="radio" name="use_ldap" value="1" ' . $on . '> ' . $hesklang['on'] . '</div>';
                        ?>
                    </div>
                </div>
                <div id="ldap_dn_group" <?php if (!$modsForHesk_settings['use_ldap']): ?>style="display:none"<?php endif; ?>>
                    <div class="form-group" id="ldap_domain">
                        <label for="ldap_server" class="col-sm-4 control-label">
                            <?php echo $hesklang['ldap_server_address']; ?>
                            <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                               title="<?php echo $hesklang['ldap_server_address']; ?>"
                               data-content="<?php echo hesk_htmlspecialchars($hesklang['ldap_server_address_help']); ?>"></i>
                        </label>
                        <div class="col-sm-3">
                            <input type="text" name="ldap_server" value="<?php echo $modsForHesk_settings['ldap_server']; ?>" placeholder="<?php echo $hesklang['ldap_server_address']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="form-group" id="ldap_base_dn">
                        <label for="ldap_base_dn" class="col-sm-4 control-label">
                            <?php echo $hesklang['ldap_base_dn']; ?>
                            <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                               title="<?php echo $hesklang['ldap_base_dn']; ?>"
                               data-content="<?php echo hesk_htmlspecialchars($hesklang['ldap_base_dn_help']); ?>"></i>
                        </label>
                        <div class="col-sm-3">
                            <input type="text" name="ldap_base_dn" value="<?php echo $modsForHesk_settings['ldap_base_dn']; ?>" placeholder="<?php echo $hesklang['ldap_base_dn']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="form-group" id="ldap_user">
                        <label for="ldap_user" class="col-sm-4 control-label">
                            <?php echo $hesklang['ldap_user']; ?>
                            <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                               title="<?php echo $hesklang['ldap_user']; ?>"
                               data-content="<?php echo hesk_htmlspecialchars($hesklang['ldap_user_help']); ?>"></i>
                        </label>
                        <div class="col-sm-3">
                            <input type="text" name="ldap_user" value="<?php echo $modsForHesk_settings['ldap_user']; ?>" placeholder="<?php echo $hesklang['ldap_user']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="form-group" id="ldap_password">
                        <label for="ldap_password" class="col-sm-4 control-label">
                            <?php echo $hesklang['ldap_password']; ?>
                            <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                               title="<?php echo $hesklang['ldap_password']; ?>"
                               data-content="<?php echo hesk_htmlspecialchars($hesklang['ldap_password_help']); ?>"></i>
                        </label>
                        <div class="col-sm-3">
                            <input type="text" name="ldap_password" value="<?php echo $modsForHesk_settings['ldap_password']; ?>" placeholder="<?php echo $hesklang['ldap_password']; ?>" class="form-control">
                            <div onclick="hesk_testLDAP()" class="btn btn-default push-down-10"><?php echo $hesklang['test_ldap_connection']; ?></div>
                        </div>
                    </div>
                </div>

                <!-- START LDAP TEST -->
                <div id="ldap_test" style="display:none">
                </div>

                <script language="Javascript" type="text/javascript"><!--
                    function hesk_testLDAP() {
                        var $element = $('#ldap-test');
                        $element.html('<img src="<?php echo HESK_PATH; ?>img/loading.gif" width="24" height="24" alt="" border="0" style="vertical-align:text-bottom" /> <i><?php echo addslashes($hesklang['contest']); ?></i>');
                        $element.show();

                        var domain = $('input[name="ldap_server"]').val();
                        var baseDn = $('input[name="ldap_base_dn"]').val();
                        var username = $('input[name="ldap_user"]').val();
                        var password = $('input[name="ldap_password"]').val();

                        if (domain === '' || baseDn === '' || username === '' || password === '') {
                            alert('missing required fields');
                        }

                        $.ajax({
                            url: <?php echo json_encode(HESK_PATH); ?> + "api/index.php/v1-internal/ldap-test",
                            method: 'POST',
                            data: JSON.stringify({
                                domain: domain,
                                baseDn: baseDn,
                                username: username,
                                password: password
                            }),
                            success: function(result) {
                                console.log(result.message);
                            },
                            error: function(data) {
                                console.log(data);
                            },
                            complete: function() {

                            }
                        });
                    }
                    //-->
                </script>
                <!-- END SMTP TEST -->

                <h4 class="bold"><?php echo $hesklang['attachments']; ?></h4>
                <div class="form-group">
                    <label for="s_attach_use" class="col-sm-3 control-label"><?php echo $hesklang['attach_use'];
                        $onload_status = ''; ?> <a href="Javascript:void(0)"
                                                   onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#37','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9 form-inline">
                        <?php
                        if ($enable_use_attachments) {
                            ?>
                            <div class="radio"><label><input type="radio" name="s_attach_use" value="0"
                                                             onclick="hesk_attach_disable(new Array('a1','a2','a3','a4'))" <?php if (!$hesk_settings['attachments']['use']) {
                                        echo ' checked="checked" ';
                                        $onload_status = ' disabled="disabled" ';
                                    } ?> />
                                    <?php echo $hesklang['no']; ?></label></div>&nbsp;&nbsp;&nbsp;
                            <div class="radio"><label><input type="radio" name="s_attach_use" value="1"
                                                             onclick="hesk_attach_enable(new Array('a1','a2','a3','a4'))" <?php if ($hesk_settings['attachments']['use']) {
                                        echo ' checked="checked" ';
                                    } ?> />
                                <?php echo $hesklang['yes'] . '</label>'; ?></div>

                            <?php
                            if (!defined('HESK_DEMO')) {
                                ?>

                                &nbsp; (<a href="javascript:void(0);"
                                           onclick="hesk_toggleLayerDisplay('attachments_limits');"><?php echo $hesklang['vscl']; ?></a>)

                                <div id="attachments_limits" style="display:none">
                                    upload_max_filesize: <?php echo @ini_get('upload_max_filesize'); ?><br/>
                                    <?php
                                    if (version_compare(phpversion(), '5.2.12', '>=')) {
                                        echo 'max_file_uploads: ' . @ini_get('max_file_uploads') . '<br />';
                                    }
                                    ?>
                                    post_max_size: <?php echo @ini_get('post_max_size'); ?><br/>
                                </div>
                                <?php
                            }
                        } else {
                            $onload_status = ' disabled="disabled" ';
                            echo '<input type="hidden" name="s_attach_use" value="0" /><font class="notice">' . $hesklang['e_attach'] . '</font>';
                        }
                        ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_max_num" class="col-sm-3 control-label"><?php echo $hesklang['attach_num']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#38','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-3">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['attach_num']); ?>"
                               name="s_max_number" size="5" maxlength="2" id="a1"
                               value="<?php echo $hesk_settings['attachments']['max_number']; ?>" <?php echo $onload_status; ?> />
                    </div>
                </div>
                <div class="form-group">
                    <?php
                    $suffixes = array(
                        'B' => $hesklang['B'] . ' (' . $hesklang['bytes'] . ')',
                        'kB' => $hesklang['kB'] . ' (' . $hesklang['kilobytes'] . ')',
                        'MB' => $hesklang['MB'] . ' (' . $hesklang['megabytes'] . ')',
                        'GB' => $hesklang['GB'] . ' (' . $hesklang['gigabytes'] . ')',
                    );
                    $tmp = hesk_formatBytes($hesk_settings['attachments']['max_size'], 0);
                    list($size, $unit) = explode(' ', $tmp);
                    ?>
                    <label for="s_max_size" class="col-sm-3 control-label"><?php echo $hesklang['attach_size']; ?>
                        <a href="Javascript:void(0)"
                           onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#39','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-3">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['attach_size']); ?>"
                               name="s_max_size" size="5" maxlength="6" id="a2"
                               value="<?php echo $size; ?>" <?php echo $onload_status; ?> />
                    </div>
                    <div class="col-sm-6">
                        <select name="s_max_unit" class="form-control" id="a4" <?php echo $onload_status; ?> >
                            <?php
                            foreach ($suffixes as $k => $v) {
                                if ($k == $unit) {
                                    echo '<option value="' . $k . '" selected="selected">' . $v . '</option>';
                                } else {
                                    echo '<option value="' . $k . '">' . $v . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_allowed_types"
                           class="col-sm-3 control-label"><?php echo $hesklang['attach_type']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>helpdesk.html#40','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['attach_type']); ?>"
                               name="s_allowed_types" size="40" maxlength="255" id="a3"
                               value="<?php echo implode(',', $hesk_settings['attachments']['allowed_types']); ?>" <?php echo $onload_status; ?> />
                    </div>
                </div>
            </div>
        </div>

        <!-- Knowledgebase Settings -->
        <a class="anchor" id="knowledgebase">&nbsp;</a>
        <div class="box">
            <div class="box-header with-border">
                <h1 class="box-title">
                    <?php echo $hesklang['tab_3']; ?>
                </h1>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label for="s_kb_enable" class="col-sm-4 control-label"><?php echo $hesklang['s_ekb']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>knowledgebase.html#22','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8 form-inline">
                        <?php
                        $on = $hesk_settings['kb_enable'] == 1 ? 'checked="checked"' : '';
                        $off = $hesk_settings['kb_enable'] ? '' : 'checked="checked"';
                        $only = $hesk_settings['kb_enable'] == 2 ? 'checked="checked"' : '';
                        echo '
                        <div class="radio"><label><input type="radio" name="s_kb_enable" value="1" ' . $on . ' /> ' . $hesklang['ekb_y'] . '</label></div><br>
                        <div class="radio"><label><input type="radio" name="s_kb_enable" value="2" ' . $only . ' /> ' . $hesklang['ekb_o'] . '</label></div><br>
                        <div class="radio"><label><input type="radio" name="s_kb_enable" value="0" ' . $off . ' /> ' . $hesklang['ekb_n'] . '</label></div>';
                        ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="kb_attach_dir" class="col-sm-4 control-label">
                          <span class="label label-primary"
                                data-toggle="tooltip"
                                title="<?php echo $hesklang['added_in_mods_for_hesk']; ?>"><?php echo $hesklang['mods_for_hesk_acronym']; ?></span>
                        <?php echo $hesklang['kb_attach_dir']; ?>
                        <i class="fa fa-question-circle settingsquestionmark" data-toggle="htmlpopover"
                           title="<?php echo $hesklang['kb_attach_dir']; ?>"
                           data-content="<?php echo $hesklang['kb_attach_dir_help']; ?>"></i>
                    </label>

                    <div class="col-sm-8">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['kb_attach_dir']); ?>"
                               name="kb_attach_dir" size="40" maxlength="255"
                               value="<?php echo $modsForHesk_settings['kb_attach_dir']; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="knowledgebase-visibility-setting" class="col-sm-4 col-xs-12 control-label">
                          <span class="label label-primary"
                                data-toggle="tooltip"
                                title="<?php echo $hesklang['added_in_mods_for_hesk']; ?>"><?php echo $hesklang['mods_for_hesk_acronym']; ?></span>
                        <?php echo $hesklang['new_article_default_type']; ?>
                        <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                           title="<?php echo $hesklang['new_article_default_type']; ?>"
                           data-content="<?php echo $hesklang['new_article_default_type_help']; ?>"></i>
                    </label>

                    <div class="col-sm-8 col-xs-12">
                        <div class="radio">
                            <label>
                                <input type="radio" name="new_kb_article_visibility"
                                       value="0" <?php echo $modsForHesk_settings['new_kb_article_visibility'] == 0 ? 'checked' : ''; ?>>
                                <?php echo $hesklang['kb_published']; ?>
                                <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                                   title="<?php echo $hesklang['kb_published']; ?>"
                                   data-content="<?php echo $hesklang['kb_published2']; ?>"></i>
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="new_kb_article_visibility"
                                       value="1" <?php echo $modsForHesk_settings['new_kb_article_visibility'] == 1 ? 'checked' : ''; ?>>
                                <?php echo $hesklang['kb_private']; ?>
                                <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                                   title="<?php echo $hesklang['kb_private']; ?>"
                                   data-content="<?php echo $hesklang['kb_private2']; ?>"></i>
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="new_kb_article_visibility"
                                       value="2" <?php echo $modsForHesk_settings['new_kb_article_visibility'] == 2 ? 'checked' : ''; ?>>
                                <?php echo $hesklang['kb_draft']; ?>
                                <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                                   title="<?php echo $hesklang['kb_draft']; ?>"
                                   data-content="<?php echo $hesklang['kb_draft3']; ?>"></i>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_kb_wysiwyg" class="col-sm-4 control-label"><?php echo $hesklang['swyse']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>knowledgebase.html#52','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8 form-inline">
                        <?php
                        $on = $hesk_settings['kb_wysiwyg'] ? 'checked="checked"' : '';
                        $off = $hesk_settings['kb_wysiwyg'] ? '' : 'checked="checked"';
                        echo '
                        <div class="radio"><label><input type="radio" name="s_kb_wysiwyg" value="0" ' . $off . ' /> ' . $hesklang['no'] . '</label></div>&nbsp;&nbsp;&nbsp;
                        <div class="radio"><label><input type="radio" name="s_kb_wysiwyg" value="1" ' . $on . ' /> ' . $hesklang['yes'] . '</label></div>';
                        ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_kb_recommend_answers"
                           class="col-sm-4 control-label"><?php echo $hesklang['s_suggest']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>knowledgebase.html#23','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8 form-inline">
                        <?php
                        $on = $hesk_settings['kb_recommendanswers'] ? 'checked="checked"' : '';
                        $off = $hesk_settings['kb_recommendanswers'] ? '' : 'checked="checked"';
                        echo '
                        <div class="radio"><label><input type="radio" name="s_kb_recommendanswers" value="0" ' . $off . ' /> ' . $hesklang['no'] . '</label></div>&nbsp;&nbsp;&nbsp;
                        <div class="radio"><label><input type="radio" name="s_kb_recommendanswers" value="1" ' . $on . ' /> ' . $hesklang['yes'] . '</label></div>';
                        ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_kb_rating" class="col-sm-4 control-label"><?php echo $hesklang['s_kbr']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>knowledgebase.html#24','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8 form-inline">
                        <?php
                        $on = $hesk_settings['kb_rating'] ? 'checked="checked"' : '';
                        $off = $hesk_settings['kb_rating'] ? '' : 'checked="checked"';
                        echo '
                        <div class="radio"><label><input type="radio" name="s_kb_rating" value="0" ' . $off . ' /> ' . $hesklang['no'] . '</label></div>&nbsp;&nbsp;&nbsp;
                        <div class="radio"><label><input type="radio" name="s_kb_rating" value="1" ' . $on . ' /> ' . $hesklang['yes'] . '</label></div>';
                        ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_kb_views" class="col-sm-4 control-label"><?php echo $hesklang['sav']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>knowledgebase.html#58','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8 form-inline">
                        <?php
                        $on = $hesk_settings['kb_views'] ? 'checked="checked"' : '';
                        $off = $hesk_settings['kb_views'] ? '' : 'checked="checked"';
                        echo '
                        <div class="radio"><label><input type="radio" name="s_kb_views" value="0" ' . $off . ' /> ' . $hesklang['no'] . '</label></div>&nbsp;&nbsp;&nbsp;
                        <div class="radio"><label><input type="radio" name="s_kb_views" value="1" ' . $on . ' /> ' . $hesklang['yes'] . '</label></div>';
                        ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_kb_date" class="col-sm-4 control-label"><?php echo $hesklang['sad']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>knowledgebase.html#59','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8 form-inline">
                        <?php
                        $on = $hesk_settings['kb_date'] ? 'checked="checked"' : '';
                        $off = $hesk_settings['kb_date'] ? '' : 'checked="checked"';
                        echo '
                        <div class="radio"><label><input type="radio" name="s_kb_date" value="0" ' . $off . ' /> ' . $hesklang['no'] . '</label></div>&nbsp;&nbsp;&nbsp;
                        <div class="radio"><label><input type="radio" name="s_kb_date" value="1" ' . $on . ' /> ' . $hesklang['yes'] . '</label></div>';
                        ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_kb_search" class="col-sm-4 control-label"><?php echo $hesklang['s_kbs']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>knowledgebase.html#25','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8 form-inline">
                        <?php
                        $off = $hesk_settings['kb_search'] ? '' : 'checked="checked"';
                        $small = $hesk_settings['kb_search'] == 1 ? 'checked="checked"' : '';
                        $large = $hesk_settings['kb_search'] == 2 ? 'checked="checked"' : '';

                        echo '
                        <div class="radio"><label><input type="radio" name="s_kb_search" value="0" ' . $off . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                        <div class="radio"><label><input type="radio" name="s_kb_search" value="1" ' . $small . ' /> ' . $hesklang['small'] . '</label></div>&nbsp;&nbsp;&nbsp;
                        <div class="radio"><label><input type="radio" name="s_kb_search" value="2" ' . $large . ' /> ' . $hesklang['large'] . '</label></div>
                        ';
                        ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_kb_search_limit"
                           class="col-sm-4 control-label"><?php echo $hesklang['s_maxsr']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>knowledgebase.html#26','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-3">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['s_maxsr']); ?>"
                               name="s_kb_search_limit" size="5" maxlength="3"
                               value="<?php echo $hesk_settings['kb_search_limit']; ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_kb_substrart" class="col-sm-4 control-label"><?php echo $hesklang['s_ptxt']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>knowledgebase.html#27','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-3">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['s_ptxt']); ?>"
                               name="s_kb_substrart" size="5" maxlength="5"
                               value="<?php echo $hesk_settings['kb_substrart']; ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_kb_cols" class="col-sm-4 control-label"><?php echo $hesklang['s_scol']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>knowledgebase.html#28','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-3">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['s_scol']); ?>" name="s_kb_cols"
                               size="5" maxlength="2" value="<?php echo $hesk_settings['kb_cols']; ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_kb_numshow" class="col-sm-4 control-label"><?php echo $hesklang['s_psubart']; ?>
                        <a href="Javascript:void(0)"
                           onclick="Javascript:hesk_window('<?php echo $help_folder; ?>knowledgebase.html#29','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-3">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['s_psubart']); ?>"
                               name="s_kb_numshow" size="5" maxlength="2"
                               value="<?php echo $hesk_settings['kb_numshow']; ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_kb_index_popart" class="col-sm-4 control-label"><?php echo $hesklang['s_spop']; ?>
                        <a href="Javascript:void(0)"
                           onclick="Javascript:hesk_window('<?php echo $help_folder; ?>knowledgebase.html#30','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-3">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['s_spop']); ?>"
                               name="s_kb_index_popart" size="5" maxlength="2"
                               value="<?php echo $hesk_settings['kb_index_popart']; ?>"/>
                    </div>
                    <div class="col-sm-5 pad-right-0">
                        <p class="form-control-static"><?php echo $hesklang['s_onin']; ?></p>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-3 col-sm-offset-4">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['s_spop']); ?>" name="s_kb_popart"
                               size="5" maxlength="2" value="<?php echo $hesk_settings['kb_popart']; ?>"/>
                    </div>
                    <div class="col-sm-5 pad-right-0">
                        <p class="form-control-static"><?php echo $hesklang['s_onkb']; ?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_kb_latest" class="col-sm-4 control-label"><?php echo $hesklang['s_slat']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>knowledgebase.html#31','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-3">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['s_slat']); ?>"
                               name="s_kb_index_latest" size="5" maxlength="2"
                               value="<?php echo $hesk_settings['kb_index_latest']; ?>"/>
                    </div>
                    <div class="col-sm-5 pad-right-0">
                        <p class="form-control-static"><?php echo $hesklang['s_onin']; ?></p>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-3 col-sm-offset-4">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['s_slat']); ?>" name="s_kb_latest"
                               size="5" maxlength="2" value="<?php echo $hesk_settings['kb_latest']; ?>"/>
                    </div>
                    <div class="col-sm-5 pad-right-0">
                        <p class="form-control-static"><?php echo $hesklang['s_onkb']; ?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_kb_related" class="col-sm-4 control-label"><?php echo $hesklang['s_relart']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>knowledgebase.html#60','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-3">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['s_relart']); ?>"
                               name="s_kb_related" size="5" maxlength="2"
                               value="<?php echo $hesk_settings['kb_related']; ?>"/>
                    </div>
                    <div class="col-sm-5 pad-right-0">
                        <p class="form-control-static"><?php echo $hesklang['s_onin']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar Settings -->
        <a class="anchor" id="calendar">&nbsp;</a>
        <div class="box">
            <div class="box-header with-border">
                <h1 class="box-title">
                    <?php echo $hesklang['calendar_title_case']; ?>
                    <span class="label label-primary" data-toggle="tooltip"
                          title="<?php echo $hesklang['added_in_mods_for_hesk']; ?>">
                        <?php echo $hesklang['mods_for_hesk_acronym']; ?>
                    </span>
                </h1>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <h4 class="bold"><?php echo $hesklang['calendar_settings']; ?></h4>
                <div class="form-group">
                    <label for="enable_calendar" class="col-sm-4 col-xs-12 control-label">
                        <?php echo $hesklang['enable_calendar']; ?>
                        <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                           title="<?php echo $hesklang['enable_calendar']; ?>"
                           data-content="<?php echo $hesklang['enable_calendar_help']; ?>"></i>
                    </label>
                    <div class="col-sm-8 form-inline">
                        <?php
                        $on = $modsForHesk_settings['enable_calendar'] == 1 ? 'checked="checked"' : '';
                        $off = $modsForHesk_settings['enable_calendar'] ? '' : 'checked="checked"';
                        $only = $modsForHesk_settings['enable_calendar'] == 2 ? 'checked="checked"' : '';
                        echo '
                        <div class="radio"><label><input type="radio" name="enable_calendar" value="1" ' . $on . ' /> ' . $hesklang['yes_enable_calendar'] . '</label></div><br>
                        <div class="radio"><label><input type="radio" name="enable_calendar" value="2" ' . $only . ' /> ' . $hesklang['yes_enable_calendar_staff_only'] . '</label></div><br>
                        <div class="radio"><label><input type="radio" name="enable_calendar" value="0" ' . $off . ' /> ' . $hesklang['no_disable_calendar'] . '</label></div>';
                        ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="first-day-of-week" class="col-sm-4 col-xs-12 control-label">
                        <?php echo $hesklang['first_day_of_week']; ?>
                        <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                           title="<?php echo $hesklang['first_day_of_week']; ?>"
                           data-content="<?php echo $hesklang['first_day_of_week_help']; ?>"></i>
                    </label>
                    <div class="col-sm-8 col-xs-12">
                        <select name="first-day-of-week" class="form-control">
                            <option value="0" <?php if ($modsForHesk_settings['first_day_of_week'] == '0') { echo 'selected'; } ?>>
                                <?php echo $hesklang['d0']; ?>
                            </option>
                            <option value="1" <?php if ($modsForHesk_settings['first_day_of_week'] == '1') { echo 'selected'; } ?>>
                                <?php echo $hesklang['d1']; ?>
                            </option>
                            <option value="2" <?php if ($modsForHesk_settings['first_day_of_week'] == '2') { echo 'selected'; } ?>>
                                <?php echo $hesklang['d2']; ?>
                            </option>
                            <option value="3" <?php if ($modsForHesk_settings['first_day_of_week'] == '3') { echo 'selected'; } ?>>
                                <?php echo $hesklang['d3']; ?>
                            </option>
                            <option value="4" <?php if ($modsForHesk_settings['first_day_of_week'] == '4') { echo 'selected'; } ?>>
                                <?php echo $hesklang['d4']; ?>
                            </option>
                            <option value="5" <?php if ($modsForHesk_settings['first_day_of_week'] == '5') { echo 'selected'; } ?>>
                                <?php echo $hesklang['d5']; ?>
                            </option>
                            <option value="6" <?php if ($modsForHesk_settings['first_day_of_week'] == '6') { echo 'selected'; } ?>>
                                <?php echo $hesklang['d6']; ?>
                            </option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="default-view" class="col-sm-4 col-xs-12 control-label">
                        <?php echo $hesklang['default_view']; ?>
                        <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                           title="<?php echo $hesklang['default_view']; ?>"
                           data-content="<?php echo $hesklang['default_view_help']; ?>"></i>
                    </label>
                    <div class="col-sm-8 col-xs-12">
                        <select name="default-view" class="form-control">
                            <option value="month" <?php if ($modsForHesk_settings['default_calendar_view'] == 'month') { echo 'selected'; } ?>>
                                <?php echo $hesklang['month']; ?>
                            </option>
                            <option value="agendaWeek" <?php if ($modsForHesk_settings['default_calendar_view'] == 'week') { echo 'selected'; } ?>>
                                <?php echo $hesklang['week']; ?>
                            </option>
                            <option value="agendaDay" <?php if ($modsForHesk_settings['default_calendar_view'] == 'agenda') { echo 'selected'; } ?>>
                                <?php echo $hesklang['calendar_day']; ?>
                            </option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="show-start-time" class="col-sm-4 col-xs-12 control-label">
                        <?php echo $hesklang['show_event_start_time']; ?>
                        <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                           title="<?php echo $hesklang['show_event_start_time']; ?>"
                           data-content="<?php echo $hesklang['show_event_start_time_help']; ?>"></i>
                    </label>
                    <div class="col-sm-8 form-inline">
                        <?php
                        $on = $modsForHesk_settings['calendar_show_start_time'] == 'true' ? 'checked="checked"' : '';
                        $off = $modsForHesk_settings['calendar_show_start_time'] == 'false' ? 'checked="checked"' : '';
                        echo '
                        <div class="radio"><label><input type="radio" name="calendar-show-start-time" value="true" ' . $on . ' /> ' . $hesklang['yes'] . '</label></div><br>
                        <div class="radio"><label><input type="radio" name="calendar-show-start-time" value="false" ' . $off . ' /> ' . $hesklang['no'] . '</label></div><br>'; ?>
                    </div>
                </div>
                <h4 class="bold">
                    <?php echo $hesklang['business_hours']; ?>
                    <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                           title="<?php echo $hesklang['business_hours']; ?>"
                       data-content="<?php echo $hesklang['business_hours_help']; ?>"></i>
                </h4>
                <?php
                $rs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mfh_calendar_business_hours`");
                $business_hours = array();
                while ($row = hesk_dbFetchAssoc($rs)) {
                    $business_hours[intval($row['day_of_week'])]['start'] = $row['start_time'];
                    $business_hours[intval($row['day_of_week'])]['end'] = $row['end_time'];
                }
                ?>
                <div class="form-group">
                    <label for="business-hours-sunday" class="col-sm-4 col-xs-12 control-label">
                        <?php echo $hesklang['d0']; ?>
                    </label>
                    <div class="col-sm-8 col-xs-12 form-inline">
                        <input type="text" class="form-control clockpicker" data-autoclose="true" name="business-hours-sunday[0]" value="<?php echo $business_hours[0]['start']; ?>">
                        <?php echo $hesklang['to']; ?>
                        <input type="text" class="form-control clockpicker" data-autoclose="true" name="business-hours-sunday[1]" value="<?php echo $business_hours[0]['end']; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="business-hours-monday" class="col-sm-4 col-xs-12 control-label">
                        <?php echo $hesklang['d1']; ?>
                    </label>
                    <div class="col-sm-8 col-xs-12 form-inline">
                        <input type="text" class="form-control clockpicker" data-autoclose="true" name="business-hours-monday[0]" value="<?php echo $business_hours[1]['start']; ?>">
                        <?php echo $hesklang['to']; ?>
                        <input type="text" class="form-control clockpicker" data-autoclose="true" name="business-hours-monday[1]" value="<?php echo $business_hours[1]['end']; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="business-hours-tuesday" class="col-sm-4 col-xs-12 control-label">
                        <?php echo $hesklang['d2']; ?>
                    </label>
                    <div class="col-sm-8 col-xs-12 form-inline">
                        <input type="text" class="form-control clockpicker" data-autoclose="true" name="business-hours-tuesday[0]" value="<?php echo $business_hours[2]['start']; ?>">
                        <?php echo $hesklang['to']; ?>
                        <input type="text" class="form-control clockpicker" data-autoclose="true" name="business-hours-tuesday[1]" value="<?php echo $business_hours[2]['end']; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="business-hours-wednesday" class="col-sm-4 col-xs-12 control-label">
                        <?php echo $hesklang['d3']; ?>
                    </label>
                    <div class="col-sm-8 col-xs-12 form-inline">
                        <input type="text" class="form-control clockpicker" data-autoclose="true" name="business-hours-wednesday[0]" value="<?php echo $business_hours[3]['start']; ?>">
                        <?php echo $hesklang['to']; ?>
                        <input type="text" class="form-control clockpicker" data-autoclose="true" name="business-hours-wednesday[1]" value="<?php echo $business_hours[3]['end']; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="business-hours-thursday" class="col-sm-4 col-xs-12 control-label">
                        <?php echo $hesklang['d4']; ?>
                    </label>
                    <div class="col-sm-8 col-xs-12 form-inline">
                        <input type="text" class="form-control clockpicker" data-autoclose="true" name="business-hours-thursday[0]" value="<?php echo $business_hours[4]['start']; ?>">
                        <?php echo $hesklang['to']; ?>
                        <input type="text" class="form-control clockpicker" data-autoclose="true" name="business-hours-thursday[1]" value="<?php echo $business_hours[4]['end']; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="business-hours-friday" class="col-sm-4 col-xs-12 control-label">
                        <?php echo $hesklang['d5']; ?>
                    </label>
                    <div class="col-sm-8 col-xs-12 form-inline">
                        <input type="text" class="form-control clockpicker" data-autoclose="true" name="business-hours-friday[0]" value="<?php echo $business_hours[5]['start']; ?>">
                        <?php echo $hesklang['to']; ?>
                        <input type="text" class="form-control clockpicker" data-autoclose="true" name="business-hours-friday[1]" value="<?php echo $business_hours[5]['end']; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="business-hours-saturday" class="col-sm-4 col-xs-12 control-label">
                        <?php echo $hesklang['d6']; ?>
                    </label>
                    <div class="col-sm-8 col-xs-12 form-inline">
                        <input type="text" class="form-control clockpicker" data-autoclose="true" name="business-hours-saturday[0]" value="<?php echo $business_hours[6]['start']; ?>">
                        <?php echo $hesklang['to']; ?>
                        <input type="text" class="form-control clockpicker" data-autoclose="true" name="business-hours-saturday[1]" value="<?php echo $business_hours[6]['end']; ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Settings -->
        <a class="anchor" id="email">&nbsp;</a>
        <div class="box">
            <div class="box-header with-border">
                <h1 class="box-title">
                    <?php echo $hesklang['tab_6']; ?>
                </h1>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <h4 class="bold"><?php echo $hesklang['emlsend']; ?></h4>

                <div class="form-group">
                    <label for="s_smtp" class="col-sm-3 control-label"><?php echo $hesklang['emlsend2']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#55','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <?php
                        $on = '';
                        $off = '';
                        $mailgunOn = '';
                        $onload_div = 'none';
                        $onload_mailgun = 'none';
                        $onload_status = '';

                        if ($hesk_settings['smtp']) {
                            $on = 'checked="checked"';
                            $onload_div = 'block';
                        } elseif ($modsForHesk_settings['use_mailgun']) {
                            $mailgunOn = 'checked="checked"';
                            $onload_mailgun = 'block';
                        } else {
                            $off = 'checked="checked"';
                            $onload_status = ' disabled="disabled" ';
                        }

                        echo '
                        <div class="radio">
                            <label>
                                <input type="radio" name="s_smtp" value="0"
                                    onclick="hesk_attach_disable(new Array(\'s1\',\'s2\',\'s3\',\'s4\',\'s5\',\'s6\',\'s7\',\'s8\',\'s9\'));toggleContainers([],[\'smtp_settings\',\'mailgun_settings\']);" ' . $off . ' />
                                    ' . $hesklang['phpmail'] . '
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="s_smtp" value="1"
                                    onclick="hesk_attach_enable(new Array(\'s1\',\'s2\',\'s3\',\'s4\',\'s5\',\'s6\',\'s7\',\'s8\',\'s9\'));toggleContainers([\'smtp_settings\'],[\'mailgun_settings\']);" ' . $on . ' />
                                    ' . $hesklang['smtp'] . '
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="s_smtp" value="2"
                                    onclick="toggleContainers([\'mailgun_settings\'],[\'smtp_settings\']);" ' . $mailgunOn . '>
                                    <span class="label label-primary"
                                        data-toggle="tooltip"
                                        title="' . $hesklang['added_in_mods_for_hesk'] . '">' . $hesklang['mods_for_hesk_acronym'] . '</span>
                                    ' . $hesklang['mailgun'] . '
                                    <i class="fa fa-question-circle settingsquestionmark"
                                        data-toggle="popover" title="' . $hesklang['mailgun'] . '"
                                        data-content="' . $hesklang['mailgun_help'] . '">
                                    </i>
                            </label>
                        </div>';
                        ?>
                        <input type="hidden" name="tmp_smtp_host_name"
                               value="<?php echo $hesk_settings['smtp_host_name']; ?>"/>
                        <input type="hidden" name="tmp_smtp_host_port"
                               value="<?php echo $hesk_settings['smtp_host_port']; ?>"/>
                        <input type="hidden" name="tmp_smtp_timeout"
                               value="<?php echo $hesk_settings['smtp_timeout']; ?>"/>
                        <input type="hidden" name="tmp_smtp_user"
                               value="<?php echo $hesk_settings['smtp_user']; ?>"/>
                        <input type="hidden" name="tmp_smtp_password"
                               value="<?php echo $hesk_settings['smtp_password']; ?>"/>
                        <input type="hidden" name="tmp_smtp_ssl" value="<?php echo $hesk_settings['smtp_ssl']; ?>"/>
                        <input type="hidden" name="tmp_smtp_tls" value="<?php echo $hesk_settings['smtp_tls']; ?>"/>


                    </div>
                </div>
                <div id="mailgun_settings" style="display:<?php echo $onload_mailgun; ?>">
                    <div class="form-group">
                        <label for="mailgun_api_key" class="col-sm-3 control-label">
                            <span class="label label-primary"
                                  data-toggle="tooltip"
                                  title="<?php echo $hesklang['added_in_mods_for_hesk'] ?>"><?php echo $hesklang['mods_for_hesk_acronym']; ?></span>
                            <?php echo $hesklang['mailgun_api_key']; ?>
                            <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                               title="<?php echo $hesklang['mailgun_api_key']; ?>"
                               data-content="<?php echo $hesklang['mailgun_api_key_help']; ?>">
                            </i>
                        </label>

                        <div class="col-sm-9">
                            <input type="text" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($hesklang['mailgun_api_key']); ?>"
                                   id="mailgun_api_key" name="mailgun_api_key"
                                   value="<?php echo $modsForHesk_settings['mailgun_api_key']; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mailgun_domain" class="col-sm-3 control-label">
                            <span class="label label-primary"
                                  data-toggle="tooltip"
                                  title="<?php echo $hesklang['added_in_mods_for_hesk'] ?>"><?php echo $hesklang['mods_for_hesk_acronym']; ?></span>
                            <?php echo $hesklang['mailgun_domain']; ?>
                            <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                               title="<?php echo $hesklang['mailgun_domain']; ?>"
                               data-content="<?php echo $hesklang['mailgun_domain_help']; ?>"></i>
                        </label>

                        <div class="col-sm-9">
                            <input type="text" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($hesklang['mailgun_domain']); ?>"
                                   id="mailgun_domain" name="mailgun_domain"
                                   value="<?php echo $modsForHesk_settings['mailgun_domain']; ?>">
                        </div>
                    </div>
                </div>
                <div id="smtp_settings" style="display:<?php echo $onload_div; ?>">
                    <div class="form-group">
                        <label for="s_smtp_host_name"
                               class="col-sm-3 control-label"><?php echo $hesklang['smtph']; ?> <a
                                href="Javascript:void(0)"
                                onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#55','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-9">
                            <input type="text" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($hesklang['smtph']); ?>" id="s1"
                                   name="s_smtp_host_name" size="40" maxlength="255"
                                   value="<?php echo $hesk_settings['smtp_host_name']; ?>" <?php echo $onload_status; ?> />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="s_smtp_host_port"
                               class="col-sm-3 control-label"><?php echo $hesklang['smtpp']; ?> <a
                                href="Javascript:void(0)"
                                onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#55','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-3">
                            <input type="text" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($hesklang['smtpp']); ?>" id="s2"
                                   name="s_smtp_host_port" size="5" maxlength="255"
                                   value="<?php echo $hesk_settings['smtp_host_port']; ?>" <?php echo $onload_status; ?> />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="s_smtp_timeout" class="col-sm-3 control-label"><?php echo $hesklang['smtpt']; ?>
                            <a href="Javascript:void(0)"
                               onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#55','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-3">
                            <input type="text" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($hesklang['smtpt']); ?>" id="s3"
                                   name="s_smtp_timeout" size="5" maxlength="255"
                                   value="<?php echo $hesk_settings['smtp_timeout']; ?>" <?php echo $onload_status; ?> />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="s_smtp_ssl" class="col-sm-3 control-label"><?php echo $hesklang['smtpssl']; ?>
                            <a href="Javascript:void(0)"
                               onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#55','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-9 form-inline">
                            <?php
                            $on = $hesk_settings['smtp_ssl'] ? 'checked="checked"' : '';
                            $off = $hesk_settings['smtp_ssl'] ? '' : 'checked="checked"';
                            echo '
                            <div class="radio"><label><input type="radio" name="s_smtp_ssl" value="0" id="s6" ' . $off . ' ' . $onload_status . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                            <div class="radio"><label><input type="radio" name="s_smtp_ssl" value="1" id="s7" ' . $on . ' ' . $onload_status . ' /> ' . $hesklang['on'] . '</label></div>';
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="s_smtp_tls" class="col-sm-3 control-label"><?php echo $hesklang['smtptls']; ?>
                            <a href="Javascript:void(0)"
                               onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#55','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-9 form-inline">
                            <?php
                            $on = $hesk_settings['smtp_tls'] ? 'checked="checked"' : '';
                            $off = $hesk_settings['smtp_tls'] ? '' : 'checked="checked"';
                            echo '
                            <div class="radio"><label><input type="radio" name="s_smtp_tls" value="0" id="s8" ' . $off . ' ' . $onload_status . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                            <div class="radio"><label><input type="radio" name="s_smtp_tls" value="1" id="s9" ' . $on . ' ' . $onload_status . ' /> ' . $hesklang['on'] . '</label></div>';
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="s_smtp_user" class="col-sm-3 control-label"><?php echo $hesklang['smtpu']; ?> <a
                                href="Javascript:void(0)"
                                onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#55','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-9">
                            <input type="text" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($hesklang['smtpu']); ?>" id="s4"
                                   name="s_smtp_user" size="40" maxlength="255"
                                   value="<?php echo $hesk_settings['smtp_user']; ?>" <?php echo $onload_status; ?>
                                   autocomplete="off"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="s_smtp_password"
                               class="col-sm-3 control-label"><?php echo $hesklang['smtpw']; ?> <a
                                href="Javascript:void(0)"
                                onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#55','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-9">
                            <input type="password" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($hesklang['smtpw']); ?>" id="s5"
                                   name="s_smtp_password" size="40" maxlength="255"
                                   value="<?php echo $hesk_settings['smtp_password']; ?>" <?php echo $onload_status; ?>
                                   autocomplete="off"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-9 col-sm-offset-3">
                            <input type="button" class="btn btn-default" onclick="hesk_testSMTP()"
                                   value="<?php echo $hesklang['smtptest']; ?>"/>
                        </div>
                    </div>

                    <!-- START SMTP TEST -->
                    <div id="smtp_test" style="display:none">
                    </div>

                    <script language="Javascript" type="text/javascript"><!--
                        function hesk_testSMTP() {
                            var element = document.getElementById('smtp_test');
                            element.innerHTML = '<img src="<?php echo HESK_PATH; ?>img/loading.gif" width="24" height="24" alt="" border="0" style="vertical-align:text-bottom" /> <i><?php echo addslashes($hesklang['contest']); ?></i>';
                            element.style.display = 'block';

                            var s_smtp_host_name = document.getElementById('s1').value;
                            var s_smtp_host_port = document.getElementById('s2').value;
                            var s_smtp_timeout = document.getElementById('s3').value;
                            var s_smtp_user = document.getElementById('s4').value;
                            var s_smtp_password = document.getElementById('s5').value;
                            var s_smtp_ssl = document.getElementById('s7').checked ? 1 : 0;
                            var s_smtp_tls = document.getElementById('s9').checked ? 1 : 0;

                            var params = "test=smtp" +
                                "&s_smtp_host_name=" + encodeURIComponent(s_smtp_host_name) +
                                "&s_smtp_host_port=" + encodeURIComponent(s_smtp_host_port) +
                                "&s_smtp_timeout=" + encodeURIComponent(s_smtp_timeout) +
                                "&s_smtp_user=" + encodeURIComponent(s_smtp_user) +
                                "&s_smtp_password=" + encodeURIComponent(s_smtp_password) +
                                "&s_smtp_ssl=" + encodeURIComponent(s_smtp_ssl) +
                                "&s_smtp_tls=" + encodeURIComponent(s_smtp_tls);

                            xmlHttp = GetXmlHttpObject();
                            if (xmlHttp == null) {
                                return;
                            }

                            xmlHttp.open('POST', 'test_connection.php', true);
                            xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                            xmlHttp.setRequestHeader("Content-length", params.length);
                            xmlHttp.setRequestHeader("Connection", "close");

                            xmlHttp.onreadystatechange = function () {
                                if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
                                    element.innerHTML = xmlHttp.responseText;
                                }
                            }

                            xmlHttp.send(params);
                        }
                        //-->
                    </script>
                    <!-- END SMTP TEST -->

                </div>
                <!-- END SMTP SETTINGS DIV -->

                <h4 class="bold"><?php echo $hesklang['emlpipe']; ?></h4>
                <div class="form-group">
                    <label for="s_email_piping" class="col-sm-3 control-label"><?php echo $hesklang['emlpipe']; ?>
                        <a href="Javascript:void(0)"
                           onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#54','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9 form-inline">
                        <?php
                        $on = $hesk_settings['email_piping'] ? 'checked="checked"' : '';
                        $off = $hesk_settings['email_piping'] ? '' : 'checked="checked"';
                        echo '
                        <div class="radio"><label><input type="radio" name="s_email_piping" value="0" ' . $off . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                        <div class="radio"><label><input type="radio" name="s_email_piping" value="1" ' . $on . ' /> ' . $hesklang['on'] . '</label></div>';
                        ?>
                    </div>
                </div>

                <h4 class="bold"><?php echo $hesklang['pop3']; ?></h4>
                <div class="form-group">
                    <label for="s_pop3" class="col-sm-3 control-label"><?php echo $hesklang['pop3']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#59','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9 form-inline">
                        <?php
                        $on = '';
                        $off = '';
                        $onload_div = 'none';
                        $onload_status = '';

                        if ($hesk_settings['pop3']) {
                            $on = 'checked="checked"';
                            $onload_div = 'block';
                        } else {
                            $off = 'checked="checked"';
                            $onload_status = ' disabled="disabled" ';
                        }

                        echo '
                        <div class="radio"><label><input type="radio" name="s_pop3" value="0" onclick="hesk_attach_disable(new Array(\'p0\',\'p1\',\'p2\',\'p3\',\'p4\',\'p5\',\'p6\',\'p7\',\'p8\'))" onchange="hesk_toggleLayerDisplay(\'pop3_settings\');" ' . $off . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                        <div class="radio"><label><input type="radio" name="s_pop3" value="1" onclick="hesk_attach_enable(new Array(\'p0\',\'p1\',\'p2\',\'p3\',\'p4\',\'p5\',\'p6\',\'p7\',\'p8\'))" onchange="hesk_toggleLayerDisplay(\'pop3_settings\');"  ' . $on . ' /> ' . $hesklang['on'] . '</label></div>';
                        ?>
                        <input type="hidden" name="tmp_pop3_host_name"
                               value="<?php echo $hesk_settings['pop3_host_name']; ?>"/>
                        <input type="hidden" name="tmp_pop3_host_port"
                               value="<?php echo $hesk_settings['pop3_host_port']; ?>"/>
                        <input type="hidden" name="tmp_pop3_user"
                               value="<?php echo $hesk_settings['pop3_user']; ?>"/>
                        <input type="hidden" name="tmp_pop3_password"
                               value="<?php echo $hesk_settings['pop3_password']; ?>"/>
                        <input type="hidden" name="tmp_pop3_tls" value="<?php echo $hesk_settings['pop3_tls']; ?>"/>
                        <input type="hidden" name="tmp_pop3_keep"
                               value="<?php echo $hesk_settings['pop3_keep']; ?>"/>
                    </div>
                </div>
                <div id="pop3_settings" style="display:<?php echo $onload_div; ?>">
                    <div class="form-group">
                        <label for="s_pop3_job_wait" class="col-sm-3 control-label"><?php echo $hesklang['pjt']; ?>
                            <a href="Javascript:void(0)"
                               onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#59','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-9">
                            <input type="text" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($hesklang['pjt']); ?>" id="p0"
                                   name="s_pop3_job_wait" size="40" maxlength="255"
                                   value="<?php echo $hesk_settings['pop3_job_wait']; ?>" <?php echo $onload_status; ?> /> <?php echo $hesklang['pjt2']; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="s_pop3_host_name"
                               class="col-sm-3 control-label"><?php echo $hesklang['pop3h']; ?> <a
                                href="Javascript:void(0)"
                                onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#59','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-9">
                            <input type="text" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($hesklang['pop3h']); ?>" id="p1"
                                   name="s_pop3_host_name" size="40" maxlength="255"
                                   value="<?php echo $hesk_settings['pop3_host_name']; ?>" <?php echo $onload_status; ?> />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="s_pop3_host_port"
                               class="col-sm-3 control-label"><?php echo $hesklang['pop3p']; ?> <a
                                href="Javascript:void(0)"
                                onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#59','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-3">
                            <input type="text" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($hesklang['pop3p']); ?>" id="p2"
                                   name="s_pop3_host_port" size="5" maxlength="255"
                                   value="<?php echo $hesk_settings['pop3_host_port']; ?>" <?php echo $onload_status; ?> />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="s_pop3_tls" class="col-sm-3 control-label"><?php echo $hesklang['pop3tls']; ?>
                            <a href="Javascript:void(0)"
                               onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#59','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-9 form-inline">
                            <?php
                            $on = $hesk_settings['pop3_tls'] ? 'checked="checked"' : '';
                            $off = $hesk_settings['pop3_tls'] ? '' : 'checked="checked"';
                            echo '
                            <div class="radio"><label><input type="radio" name="s_pop3_tls" value="0" id="p3" ' . $off . ' ' . $onload_status . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                            <div class="radio"><label><input type="radio" name="s_pop3_tls" value="1" id="p4" ' . $on . ' ' . $onload_status . ' /> ' . $hesklang['on'] . '</label></div>';
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="s_pop3_keep" class="col-sm-3 control-label"><?php echo $hesklang['pop3keep']; ?>
                            <a href="Javascript:void(0)"
                               onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#59','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-9 form-inline">
                            <?php
                            $on = $hesk_settings['pop3_keep'] ? 'checked="checked"' : '';
                            $off = $hesk_settings['pop3_keep'] ? '' : 'checked="checked"';
                            echo '
                            <div class="radio"><label><input type="radio" name="s_pop3_keep" value="0" id="p7" ' . $off . ' ' . $onload_status . ' /> ' . $hesklang['off'] . '</label></div>
                            <div class="radio"><label><input type="radio" name="s_pop3_keep" value="1" id="p8" ' . $on . ' ' . $onload_status . ' /> ' . $hesklang['on'] . '</label></div>';
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="s_pop3_user" class="col-sm-3 control-label"><?php echo $hesklang['pop3u']; ?> <a
                                href="Javascript:void(0)"
                                onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#59','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-9">
                            <input type="text" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($hesklang['pop3u']); ?>" id="p5"
                                   name="s_pop3_user" size="40" maxlength="255"
                                   value="<?php echo $hesk_settings['pop3_user']; ?>" <?php echo $onload_status; ?>
                                   autocomplete="off"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="s_pop3_password"
                               class="col-sm-3 control-label"><?php echo $hesklang['pop3w']; ?> <a
                                href="Javascript:void(0)"
                                onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#59','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-9">
                            <input type="password" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($hesklang['pop3w']); ?>" id="p6"
                                   name="s_pop3_password" size="40" maxlength="255"
                                   value="<?php echo $hesk_settings['pop3_password']; ?>" <?php echo $onload_status; ?>
                                   autocomplete="off"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-9 col-sm-offset-3">
                            <input type="button" class="btn btn-default move-down-4" onclick="hesk_testPOP3()"
                                   value="<?php echo $hesklang['pop3test']; ?>"/>
                        </div>
                    </div>
                    <table border="0" width="100%">
                        <tr>
                            <td class="text-right" width="200"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-right" width="200">&nbsp;</td>
                            <td></td>
                        </tr>
                    </table>

                    <!-- START POP3 TEST -->
                    <div id="pop3_test" style="display:none">
                    </div>

                    <script language="Javascript" type="text/javascript"><!--
                        function hesk_testPOP3() {
                            var element = document.getElementById('pop3_test');
                            element.innerHTML = '<img src="<?php echo HESK_PATH; ?>img/loading.gif" width="24" height="24" alt="" border="0" style="vertical-align:text-bottom" /> <i><?php echo addslashes($hesklang['contest']); ?></i>';
                            element.style.display = 'block';

                            var s_pop3_host_name = document.getElementById('p1').value;
                            var s_pop3_host_port = document.getElementById('p2').value;
                            var s_pop3_user = document.getElementById('p5').value;
                            var s_pop3_password = document.getElementById('p6').value;
                            var s_pop3_tls = document.getElementById('p4').checked ? 1 : 0;

                            var params = "test=pop3" +
                                "&s_pop3_host_name=" + encodeURIComponent(s_pop3_host_name) +
                                "&s_pop3_host_port=" + encodeURIComponent(s_pop3_host_port) +
                                "&s_pop3_user=" + encodeURIComponent(s_pop3_user) +
                                "&s_pop3_password=" + encodeURIComponent(s_pop3_password) +
                                "&s_pop3_tls=" + encodeURIComponent(s_pop3_tls);

                            xmlHttp = GetXmlHttpObject();
                            if (xmlHttp == null) {
                                return;
                            }

                            xmlHttp.open('POST', 'test_connection.php', true);
                            xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                            xmlHttp.setRequestHeader("Content-length", params.length);
                            xmlHttp.setRequestHeader("Connection", "close");

                            xmlHttp.onreadystatechange = function () {
                                if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
                                    element.innerHTML = xmlHttp.responseText;
                                }
                            }

                            xmlHttp.send(params);
                        }
                        //-->
                    </script>
                    <!-- END POP3 TEST -->
                </div>
                <!-- END POP3 SETTINGS DIV -->

                <!-- IMAP Fetching -->
                <h4 class="bold"><?php echo $hesklang['imap']; ?></h4>

                <div class="form-group">
                    <label for="s_pop3" class="col-sm-3 control-label"><?php echo $hesklang['imap']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#67','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9 form-inline">
                        <?php
                        $on = '';
                        $off = '';
                        $onload_div = 'none';
                        $onload_status = '';

                        if ($hesk_settings['imap']) {
                            $on = 'checked';
                            $onload_div = 'block';
                        } else {
                            $off = 'checked';
                            $onload_status = ' disabled ';
                        }

                        // Is IMAP extension loaded?
                        if ( ! function_exists('imap_open')) {
                            echo '<i>'. $hesklang['disabled'] . '</i> - ' . $hesklang['imap_not'];
                            $onload_div = 'none';
                        } else {
                            echo '
                        <div class="radio"><label><input type="radio" name="s_imap" value="0" onclick="hesk_attach_disable(new Array(\'i0\',\'i1\',\'i2\',\'i3\',\'i4\',\'i5\',\'i6\',\'i7\',\'i8\',\'i9\'))" onchange="hesk_toggleLayerDisplay(\'imap_settings\');" ' . $off . '> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                        <div class="radio"><label><input type="radio" name="s_imap" value="1" onclick="hesk_attach_enable(new Array(\'i0\',\'i1\',\'i2\',\'i3\',\'i4\',\'i5\',\'i6\',\'i7\',\'i8\',\'i9\'))" onchange="hesk_toggleLayerDisplay(\'imap_settings\');"  ' . $on . '> ' . $hesklang['on'] . '</label></div>';
                        }
                        ?>
                        <input type="hidden" name="tmp_imap_job_wait" value="<?php echo $hesk_settings['imap_job_wait']; ?>" />
                        <input type="hidden" name="tmp_imap_host_name" value="<?php echo $hesk_settings['imap_host_name']; ?>">
                        <input type="hidden" name="tmp_imap_host_port" value="<?php echo $hesk_settings['imap_host_port']; ?>">
                        <input type="hidden" name="tmp_imap_user" value="<?php echo $hesk_settings['imap_user']; ?>">
                        <input type="hidden" name="tmp_imap_password" value="<?php echo $hesk_settings['imap_password']; ?>">
                        <input type="hidden" name="tmp_imap_enc" value="<?php echo $hesk_settings['imap_enc']; ?>">
                        <input type="hidden" name="tmp_imap_keep" value="<?php echo $hesk_settings['imap_keep']; ?>">
                    </div>
                </div>
                <div id="imap_settings" style="display:<?php echo $onload_div; ?>">
                    <div class="form-group">
                        <label for="s_imap_job_wait" class="col-sm-3 control-label"><?php echo $hesklang['pjt']; ?>
                            <a href="Javascript:void(0)"
                               onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#67','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-9">
                            <input type="text" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($hesklang['pjt']); ?>" id="i0"
                                   name="s_imap_job_wait" size="5" maxlength="5"
                                   value="<?php echo $hesk_settings['imap_job_wait']; ?>" <?php echo $onload_status; ?>> <?php echo $hesklang['pjt2']; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="s_imap_host_name" class="col-sm-3 control-label"><?php echo $hesklang['imaph']; ?>
                            <a href="Javascript:void(0)"
                               onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#67','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-9">
                            <input type="text" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($hesklang['imaph']); ?>" id="i1"
                                   name="s_imap_host_name" size="40" maxlength="255"
                                   value="<?php echo $hesk_settings['imap_host_name']; ?>" <?php echo $onload_status; ?>>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="s_imap_host_port" class="col-sm-3 control-label"><?php echo $hesklang['imapp']; ?>
                            <a href="Javascript:void(0)"
                               onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#67','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-9">
                            <input type="text" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($hesklang['imapp']); ?>" id="i2"
                                   name="s_imap_host_port" size="5" maxlength="255"
                                   value="<?php echo $hesk_settings['imap_host_port']; ?>" <?php echo $onload_status; ?>>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="s_imap_enc" class="col-sm-3 control-label"><?php echo $hesklang['enc']; ?>
                            <a href="Javascript:void(0)"
                               onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#67','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-9 form-inline">
                            <?php
                            $none = $hesk_settings['imap_enc'] == '' ? 'checked' : '';
                            $ssl = $hesk_settings['imap_enc'] == 'ssl' ? 'checked' : '';
                            $tls = $hesk_settings['imap_enc'] == 'tls' ? 'checked' : '';
                            echo '
		<div class="radio"><label><input type="radio" name="s_imap_enc" value="ssl" id="i9" '.$ssl.' '.$onload_status.'> '.$hesklang['ssl'].'</label></div>&nbsp;&nbsp;&nbsp;
		<div class="radio"><label><input type="radio" name="s_imap_enc" value="tls" id="i4" '.$tls.' '.$onload_status.'> '.$hesklang['tls'].'</label></div>&nbsp;&nbsp;&nbsp;
		<div class="radio"><label><input type="radio" name="s_imap_enc" value="" id="i3" '.$none.' '.$onload_status.'> '.$hesklang['none'].'</label></div>
		';
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="s_imap_keep" class="col-sm-3 control-label"><?php echo $hesklang['pop3keep']; ?>
                            <a href="Javascript:void(0)"
                               onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#67','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-9 form-inline">
                            <?php
                            $on = $hesk_settings['imap_keep'] ? 'checked="checked"' : '';
                            $off = $hesk_settings['imap_keep'] ? '' : 'checked="checked"';
                            echo '
		<div class="radio"><label><input type="radio" name="s_imap_keep" value="0" id="i7" '.$off.' '.$onload_status.'> '.$hesklang['off'].'</label></div>&nbsp;&nbsp;&nbsp;
		<div class="radio"><label><input type="radio" name="s_imap_keep" value="1" id="i8" '.$on.' '.$onload_status.'> '.$hesklang['on'].'</label></div>
		';
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="s_imap_user" class="col-sm-3 control-label"><?php echo $hesklang['imapu']; ?>
                            <a href="Javascript:void(0)"
                               onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#67','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-9">
                            <input type="text" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($hesklang['imapu']); ?>" id="i5"
                                   name="s_imap_user" size="40" maxlength="255"
                                   value="<?php echo $hesk_settings['imap_user']; ?>" <?php echo $onload_status; ?> autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="s_imap_password" class="col-sm-3 control-label"><?php echo $hesklang['imapw']; ?>
                            <a href="Javascript:void(0)"
                               onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#67','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-9">
                            <input type="password" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($hesklang['imapw']); ?>" id="i6"
                                   name="s_imap_password" size="40" maxlength="255"
                                   value="<?php echo $hesk_settings['imap_password']; ?>" <?php echo $onload_status; ?> autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-9 col-sm-offset-3">
                            <input type="button" class="btn btn-default"
                                   onclick="hesk_testIMAP()" value="<?php echo $hesklang['imaptest']; ?>">
                        </div>
                    </div>

                    <!-- START IMAP TEST -->
                    <div id="imap_test" style="display:none">
                    </div>

                    <script language="Javascript" type="text/javascript"><!--
                        function hesk_testIMAP()
                        {
                            var element = document.getElementById('imap_test');
                            element.innerHTML = '<img src="<?php echo HESK_PATH; ?>img/loading.gif" width="24" height="24" alt="" border="0" style="vertical-align:text-bottom" /> <i><?php echo addslashes($hesklang['contest']); ?></i>';
                            element.style.display = 'block';

                            var s_imap_host_name = document.getElementById('i1').value;
                            var s_imap_host_port = document.getElementById('i2').value;
                            var s_imap_user      = document.getElementById('i5').value;
                            var s_imap_password  = document.getElementById('i6').value;
                            var s_imap_enc       = document.getElementById('i4').checked ? 'tls' : (document.getElementById('i9').checked ? 'ssl' : '');

                            var params = "test=imap" +
                                "&s_imap_host_name="  + encodeURIComponent( s_imap_host_name ) +
                                "&s_imap_host_port=" + encodeURIComponent( s_imap_host_port ) +
                                "&s_imap_user="      + encodeURIComponent( s_imap_user ) +
                                "&s_imap_password="  + encodeURIComponent( s_imap_password ) +
                                "&s_imap_enc="       + encodeURIComponent( s_imap_enc );

                            xmlHttp=GetXmlHttpObject();
                            if (xmlHttp==null)
                            {
                                return;
                            }

                            xmlHttp.open('POST','test_connection.php',true);
                            xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                            xmlHttp.setRequestHeader("Content-length", params.length);
                            xmlHttp.setRequestHeader("Connection", "close");

                            xmlHttp.onreadystatechange = function()
                            {
                                if (xmlHttp.readyState == 4 && xmlHttp.status == 200)
                                {
                                    element.innerHTML = xmlHttp.responseText;
                                }
                            }

                            xmlHttp.send(params);
                        }
                        //-->
                    </script>
                    <!-- END IMAP TEST -->

                </div> <!-- END IMAP SETTINGS DIV -->

                <h4 class="bold"><?php echo $hesklang['loops']; ?></h4>
                <div class="form-group">
                    <label for="s_loop_hits" class="col-sm-3 control-label"><?php echo $hesklang['looph']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#60','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-3">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['looph']); ?>" name="s_loop_hits"
                               size="5" maxlength="5" value="<?php echo $hesk_settings['loop_hits']; ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_loop_time" class="col-sm-3 control-label"><?php echo $hesklang['loopt']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#60','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-3">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['loopt']); ?>" name="s_loop_time"
                               size="5" maxlength="5" value="<?php echo $hesk_settings['loop_time']; ?>"/>
                    </div>
                    <div class="col-sm-6 pad-right-0">
                        <p class="form-control-static"><?php echo $hesklang['ss']; ?></p>
                    </div>
                </div>

                <h4 class="bold"><?php echo $hesklang['suge']; ?></h4>
                <div class="form-group">
                    <label for="s_detect_typos" class="col-sm-3 control-label"><?php echo $hesklang['suge']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#62','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9 form-inline">
                        <?php
                        $on = '';
                        $off = '';
                        $onload_div = 'none';
                        $onload_status = '';

                        if ($hesk_settings['detect_typos']) {
                            $on = 'checked="checked"';
                            $onload_div = 'block';
                        } else {
                            $off = 'checked="checked"';
                            $onload_status = ' disabled="disabled" ';
                        }

                        echo '
                            <div class="radio"><label><input type="radio" name="s_detect_typos" value="0" onclick="hesk_attach_disable(new Array(\'d1\'))" onchange="hesk_toggleLayerDisplay(\'detect_typos\');" ' . $off . ' /> ' . $hesklang['off'] . '</label></div>&nbsp;&nbsp;&nbsp;
                            <div class="radio"><label><input type="radio" name="s_detect_typos" value="1" onclick="hesk_attach_enable(new Array(\'d1\'))" onchange="hesk_toggleLayerDisplay(\'detect_typos\');"  ' . $on . ' /> ' . $hesklang['on'] . '</label></div>';
                        ?>
                    </div>
                </div>
                <div id="detect_typos" style="display:<?php echo $onload_div; ?>">
                    <div class="form-group">
                        <label for="s_email_providers"
                               class="col-sm-3 control-label"><?php echo $hesklang['epro']; ?> <a
                                href="Javascript:void(0)"
                                onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#63','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                        <div class="col-sm-9">
                                <textarea name="s_email_providers" class="form-control"
                                          placeholder="<?php echo htmlspecialchars($hesklang['epro']); ?>" id="d1"
                                          rows="5"
                                          cols="40"><?php echo implode("\n", $hesk_settings['email_providers']); ?></textarea>
                        </div>
                    </div>
                    <table border="0" width="100%">
                        <tr>
                            <td class="text-right" style="vertical-align:top" width="200"></td>
                            <td></td>
                        </tr>
                    </table>
                </div>

                <h4 class="bold"><?php echo $hesklang['custnot']; ?> <a href="Javascript:void(0)"
                                                                        onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#65','400','500')"><i
                            class="fa fa-question-circle settingsquestionmark"></i></a></h4>
                <div class="form-group">
                    <label for="s_notify_new"
                           class="col-sm-3 control-label"><?php echo $hesklang['notnew']; ?></label>

                    <div class="col-sm-9">
                        <div class="checkbox">
                            <label><input type="checkbox" name="s_notify_new" value="1"
                                          onchange="hesk_toggleLayerDisplay('skip_notify');" <?php if ($hesk_settings['notify_new']) {
                                    echo 'checked="checked"';
                                } ?>/> <?php echo $hesklang['enable']; ?></label>
                        </div>
                    </div>
                </div>
                <div id="skip_notify"
                     style="display:<?php echo $hesk_settings['notify_new'] ? 'block' : 'none'; ?>">
                    <div class="form-group">
                        <div class="col-sm-9 col-sm-offset-3">
                            <div class="checkbox">
                                <label><input type="checkbox" name="s_notify_skip_spam"
                                              value="1" <?php if ($hesk_settings['notify_skip_spam']) {
                                        echo 'checked="checked"';
                                    } ?>/> <?php echo $hesklang['enn']; ?></label>
                            </div>
                                <textarea name="s_notify_spam_tags" rows="5" cols="40"
                                          class="form-control"><?php echo hesk_htmlspecialchars(implode("\n", $hesk_settings['notify_spam_tags'])); ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_notify_closed"
                           class="col-sm-3 control-label"><?php echo $hesklang['notclo']; ?></label>

                    <div class="col-sm-9">
                        <div class="checkbox">
                            <label><input type="checkbox" name="s_notify_closed"
                                          value="1" <?php if ($hesk_settings['notify_closed']) {
                                    echo 'checked="checked"';
                                } ?>/> <?php echo $hesklang['enable']; ?></label>
                        </div>
                    </div>
                </div>

                <h4 class="bold"><?php echo $hesklang['other']; ?></h4>
                <div class="form-group">
                    <label for="s_strip_quoted" class="col-sm-3 control-label"><?php echo $hesklang['remqr']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#61','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <div class="checkbox">
                            <label><input type="checkbox" name="s_strip_quoted"
                                          value="1" <?php if ($hesk_settings['strip_quoted']) {
                                    echo 'checked="checked"';
                                } ?>/> <?php echo $hesklang['remqr2']; ?></label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_eml_req_msg" class="col-sm-3 control-label"><?php echo $hesklang['emlreqmsg']; ?>
                        <a href="Javascript:void(0)"
                           onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#66','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <div class="checkbox">
                            <label><input type="checkbox" name="s_eml_req_msg"
                                          value="1" <?php if ($hesk_settings['eml_req_msg']) {
                                    echo 'checked="checked"';
                                } ?>/> <?php echo $hesklang['emlreqmsg2']; ?></label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_save_embedded" class="col-sm-3 control-label"><?php echo $hesklang['embed']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#64','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <div class="checkbox">
                            <label><input type="checkbox" name="s_save_embedded"
                                          value="1" <?php if ($hesk_settings['save_embedded']) {
                                    echo 'checked="checked"';
                                } ?>/> <?php echo $hesklang['embed2']; ?></label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_multi_eml" class="col-sm-3 control-label"><?php echo $hesklang['meml']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#57','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <div class="checkbox">
                            <?php
                            if ($modsForHesk_settings['customer_email_verification_required']) {
                                ?>
                                <label>
                                    <i class="fa fa-ban bold red font-size-120" style="margin-left: -20px;"
                                       data-toggle="popover"
                                       title="<?php echo $hesklang['feature_disabled']; ?>"
                                       data-content="<?php echo $hesklang['multi_eml_disabled']; ?>"></i> <?php echo $hesklang['meml2']; ?>
                                </label>
                                <input type="hidden" name="s_multi_eml" value="0">
                                <?php
                            } else {
                                ?>
                                <label><input type="checkbox" name="s_multi_eml"
                                              value="1" <?php if ($hesk_settings['multi_eml']) {
                                        echo 'checked="checked"';
                                    } ?>/> <?php echo $hesklang['meml2']; ?></label>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_confirm_email" class="col-sm-3 control-label"><?php echo $hesklang['sconfe']; ?>
                        <a href="Javascript:void(0)"
                           onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#50','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <div class="checkbox">
                            <label><input type="checkbox" name="s_confirm_email"
                                          value="1" <?php if ($hesk_settings['confirm_email']) {
                                    echo 'checked="checked"';
                                } ?>/> <?php echo $hesklang['sconfe2']; ?></label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_open_only" class="col-sm-3 control-label"><?php echo $hesklang['oo']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>email.html#58','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-9">
                        <div class="checkbox">
                            <label><input type="checkbox" name="s_open_only"
                                          value="1" <?php if ($hesk_settings['open_only']) {
                                    echo 'checked="checked"';
                                } ?>/> <?php echo $hesklang['ool']; ?></label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="html_emails" class="col-sm-3 col-xs-12 control-label">
                            <span class="label label-primary"
                                  data-toggle="tooltip"
                                  title="<?php echo $hesklang['added_in_mods_for_hesk']; ?>"><?php echo $hesklang['mods_for_hesk_acronym']; ?></span>
                        <?php echo $hesklang['html_emails']; ?>
                        <i class="fa fa-question-circle settingsquestionmark" data-toggle="htmlpopover"
                           title="<?php echo $hesklang['html_emails']; ?>"
                           data-content="<?php echo $hesklang['html_emails_help']; ?>"></i>
                    </label>

                    <div class="col-sm-9 col-xs-12">
                        <div class="checkbox">
                            <label>
                                <input id="html_emails" name="html_emails"
                                       type="checkbox" <?php if ($modsForHesk_settings['html_emails']) {
                                    echo 'checked';
                                } ?>> <?php echo $hesklang['html_emails_text']; ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email_attachments" class="col-sm-3 col-xs-12 control-label">
                            <span class="label label-primary"
                                  data-toggle="tooltip"
                                  title="<?php echo $hesklang['added_in_mods_for_hesk']; ?>"><?php echo $hesklang['mods_for_hesk_acronym']; ?></span>
                        <?php echo $hesklang['email_attachments']; ?>
                        <i class="fa fa-question-circle settingsquestionmark" data-toggle="htmlpopover"
                           title="<?php echo $hesklang['email_attachments']; ?>"
                           data-content="<?php echo $hesklang['email_attachments_help']; ?>"></i>
                    </label>

                    <div class="col-sm-9 col-xs-12">
                        <div class="radio">
                            <label>
                                <input type="radio" name="email_attachments"
                                       value="0" <?php echo $modsForHesk_settings['attachments'] == 0 ? 'checked' : ''; ?>>
                                <?php echo $hesklang['show_attachments_as_links']; ?>
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="email_attachments"
                                       value="1" <?php echo $modsForHesk_settings['attachments'] == 1 ? 'checked' : ''; ?>>
                                <?php echo $hesklang['attach_directly_to_email']; ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ticket List Settings -->
        <a class="anchor" id="ticket-list">&nbsp;</a>
        <div class="box">
            <div class="box-header with-border">
                <h1 class="box-title">
                    <?php echo $hesklang['tab_7']; ?>
                </h1>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label for="s_open_only" class="col-sm-4 control-label"><?php echo $hesklang['fitl']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>ticket_list.html#1','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8">
                        <?php
                        // List available fields
                        foreach ($hesk_settings['possible_ticket_list'] as $key => $title) {
                            echo '
                              <div class="checkbox">
                                  <label><input type="checkbox" name="s_tl_' . $key . '" value="1" ' . (in_array($key, $hesk_settings['ticket_list']) ? 'checked="checked"' : '') . '/> ' . $title . '</label>
                              </div>
                              ';
                        }
                        ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="show_number_merged" class="col-sm-4 control-label">
                          <span class="label label-primary"
                                data-toggle="tooltip"
                                title="<?php echo $hesklang['added_in_mods_for_hesk'] ?>"><?php echo $hesklang['mods_for_hesk_acronym']; ?></span>
                        <?php echo $hesklang['show_number_merged']; ?>
                        <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                           title="<?php echo $hesklang['show_number_merged']; ?>"
                           data-content="<?php echo $hesklang['show_number_merged_help']; ?>"></i>
                    </label>

                    <div class="col-sm-8">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox"
                                       name="show_number_merged" <?php if ($modsForHesk_settings['show_number_merged']) {
                                    echo 'checked';
                                } ?>> <?php echo $hesklang['show_number_merged_descr']; ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="show_number_merged" class="col-sm-4 control-label">
                          <span class="label label-primary"
                                data-toggle="tooltip"
                                title="<?php echo $hesklang['added_in_mods_for_hesk'] ?>"><?php echo $hesklang['mods_for_hesk_acronym']; ?></span>
                        <?php echo $hesklang['highlight_ticket_rows_based_on_priority']; ?>
                        <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                           title="<?php echo $hesklang['highlight_ticket_rows_based_on_priority']; ?>"
                           data-content="<?php echo $hesklang['highlight_ticket_rows_based_on_priority_help']; ?>"></i>
                    </label>
                    <div class="col-sm-8">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox"
                                       name="highlight_ticket_rows_based_on_priority" <?php if ($modsForHesk_settings['highlight_ticket_rows_based_on_priority']) {
                                    echo 'checked';
                                } ?>> <?php echo $hesklang['highlight_ticket_rows_based_on_priority_descr']; ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_submittedformat" class="col-sm-4 control-label"><?php echo $hesklang['sdf']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>ticket_list.html#2','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8 form-inline">
                        <?php
                        $off = $hesk_settings['new_top'] ? '' : 'checked="checked"';
                        echo '
                            <div class="radio"><label><input type="radio" name="s_submittedformat" value="2" ' . ($hesk_settings['submittedformat'] == 2 ? 'checked="checked"' : '') . ' /> ' . $hesklang['lcf2'] . '</label></div><br>
                            <div class="radio"><label><input type="radio" name="s_submittedformat" value="1" ' . ($hesk_settings['submittedformat'] == 1 ? 'checked="checked"' : '') . ' /> ' . $hesklang['lcf1'] . '</label></div><br>
                            <div class="radio"><label><input type="radio" name="s_submittedformat" value="0" ' . ($hesk_settings['submittedformat'] == 0 ? 'checked="checked"' : '') . ' /> ' . $hesklang['lcf0'] . '</label></div>';
                        ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_updatedformat" class="col-sm-4 control-label"><?php echo $hesklang['lcf']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>ticket_list.html#2','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8 form-inline">
                        <?php

                        echo '
                          <div class="radio"><label><input type="radio" name="s_updatedformat" value="2" ' . ($hesk_settings['updatedformat'] == 2 ? 'checked="checked"' : '') . ' /> ' . $hesklang['lcf2'] . '</label></div><br>
                          <div class="radio"><label><input type="radio" name="s_updatedformat" value="1" ' . ($hesk_settings['updatedformat'] == 1 ? 'checked="checked"' : '') . ' /> ' . $hesklang['lcf1'] . '</label></div><br>
                          <div class="radio"><label><input type="radio" name="s_updatedformat" value="0" ' . ($hesk_settings['updatedformat'] == 0 ? 'checked="checked"' : '') . ' /> ' . $hesklang['lcf0'] . '</label></div>';
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Misc Settings -->
        <a class="anchor" id="miscellaneous">&nbsp;</a>
        <div class="box">
            <div class="box-header with-border">
                <h1 class="box-title">
                    <?php echo $hesklang['tab_5']; ?>
                </h1>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <h4 class="bold"><?php echo $hesklang['dat']; ?></h4>
                <div class="form-group">
                    <label for="s_timezone" class="col-sm-4 control-label"><?php echo $hesklang['TZ']; ?> <a
                                href="Javascript:void(0)"
                                onclick="Javascript:hesk_window('<?php echo $help_folder; ?>misc.html#63','400','500')"><i
                                    class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8">
                        <?php
                        // Get list of supported timezones
                        $timezone_list = hesk_generate_timezone_list();

                        // Do we need to localize month names?
                        if ($hesk_settings['language'] != 'English') {
                            $timezone_list = hesk_translate_timezone_list($timezone_list);
                        }
                        ?>
                        <select class="form-control" name="s_timezone">
                            <?php foreach ($timezone_list as $timezone => $description): ?>
                                <option value="<?php echo $timezone; ?>" <?php if ($hesk_settings['timezone'] == $timezone) {echo 'selected';} ?>>
                                    <?php echo $description; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_timeformat" class="col-sm-4 control-label"><?php echo $hesklang['tfor']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>misc.html#20','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8">
                        <input type="text" class="form-control"
                               placeholder="<?php echo htmlspecialchars($hesklang['tfor']); ?>" name="s_timeformat"
                               size="40" maxlength="255" value="<?php echo $hesk_settings['timeformat']; ?>"/>
                    </div>
                </div>

                <h4 class="bold"><?php echo $hesklang['other']; ?></h4>
                <div class="form-group">
                    <label for="s_ip_whois_url" class="col-sm-4 control-label"><?php echo $hesklang['ip_whois']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>misc.html#61','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="s_ip_whois_url" size="40" maxlength="255"
                               value="<?php echo $hesk_settings['ip_whois']; ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_maintenance_mode" class="col-sm-4 control-label"><?php echo $hesklang['mms']; ?>
                        <a href="Javascript:void(0)"
                           onclick="Javascript:hesk_window('<?php echo $help_folder; ?>misc.html#62','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8">
                        <div class="checkbox">
                            <label><input type="checkbox" name="s_maintenance_mode"
                                          value="1" <?php if ($hesk_settings['maintenance_mode']) {
                                    echo 'checked="checked"';
                                } ?>/> <?php echo $hesklang['mmd']; ?></label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_alink" class="col-sm-4 control-label"><?php echo $hesklang['al']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>misc.html#21','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8">
                        <div class="checkbox">
                            <label><input type="checkbox" name="s_alink"
                                          value="1" <?php if ($hesk_settings['alink']) {
                                    echo 'checked="checked"';
                                } ?>/> <?php echo $hesklang['dap']; ?></label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_submit_notice" class="col-sm-4 control-label"><?php echo $hesklang['subnot']; ?>
                        <a href="Javascript:void(0)"
                           onclick="Javascript:hesk_window('<?php echo $help_folder; ?>misc.html#48','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8">
                        <div class="checkbox">
                            <label><input type="checkbox" name="s_submit_notice"
                                          value="1" <?php if ($hesk_settings['submit_notice']) {
                                    echo 'checked="checked"';
                                } ?>/> <?php echo $hesklang['subnot2']; ?></label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_online" class="col-sm-4 control-label"><?php echo $hesklang['sonline']; ?> <a
                            href="Javascript:void(0)"
                            onclick="Javascript:hesk_window('<?php echo $help_folder; ?>misc.html#56','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label><input type="checkbox" name="s_online"
                                          value="1" <?php if ($hesk_settings['online']) {
                                    echo 'checked="checked"';
                                } ?>/> <?php echo $hesklang['sonline2']; ?></label>
                        </div>
                    </div>
                    <div class="col-sm-2 pad-right-0">
                        <input type="text" class="form-control" name="s_online_min" size="5" maxlength="4"
                               value="<?php echo $hesk_settings['online_min']; ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="show-icons" class="col-sm-4 col-xs-12 control-label">
                        <span class="label label-primary"
                              data-toggle="tooltip"
                              title="<?php echo $hesklang['added_in_mods_for_hesk'] ?>"><?php echo $hesklang['mods_for_hesk_acronym']; ?></span>
                        <?php echo $hesklang['showIcons']; ?>
                        <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                           title="<?php echo $hesklang['showIcons']; ?>"
                           data-content="<?php echo $hesklang['showIconsHelp']; ?>"></i>
                    </label>

                    <div class="col-sm-8 col-xs-12">
                        <div class="checkbox">
                            <label>
                                <input id="show-icons" name="show-icons"
                                       type="checkbox" <?php if ($modsForHesk_settings['show_icons']) {
                                    echo 'checked';
                                } ?>> <?php echo $hesklang['show_icons_navigation']; ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="use_bootstrap_theme" class="col-sm-4 col-xs-12 control-label">
                        <span class="label label-primary"
                              data-toggle="tooltip"
                              title="<?php echo $hesklang['added_in_mods_for_hesk'] ?>"><?php echo $hesklang['mods_for_hesk_acronym']; ?></span>
                        <?php echo $hesklang['use_bootstrap_theme']; ?>
                        <i class="fa fa-question-circle settingsquestionmark" data-toggle="htmlpopover"
                           title="<?php echo $hesklang['use_bootstrap_theme']; ?>"
                           data-content="<?php echo $hesklang['use_bootstrap_theme_help']; ?>"></i>
                    </label>

                    <div class="col-sm-8 col-xs-12">
                        <div class="checkbox">
                            <label>
                                <input id="use_boostrap_theme" name="use_bootstrap_theme"
                                       type="checkbox" <?php if ($modsForHesk_settings['use_bootstrap_theme']) {
                                    echo 'checked';
                                } ?>> <?php echo $hesklang['use_bootstrap_theme']; ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="s_check_updates" class="col-sm-4 control-label"><?php echo $hesklang['updates']; ?>
                        <a href="Javascript:void(0)"
                           onclick="Javascript:hesk_window('<?php echo $help_folder; ?>misc.html#59','400','500')"><i
                                class="fa fa-question-circle settingsquestionmark"></i></a></label>

                    <div class="col-sm-8">
                        <div class="checkbox">
                            <label><input type="checkbox" name="s_check_updates"
                                          value="1" <?php if ($hesk_settings['check_updates']) {
                                    echo 'checked="checked"';
                                } ?>/> <?php echo $hesklang['updates2']; ?></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- UI Colors -->
        <a class="anchor" id="ui-colors">&nbsp;</a>
        <div class="box">
            <div class="box-header with-border">
                <h1 class="box-title">
                    <?php echo $hesklang['uiColors']; ?>
                    <span class="label label-primary" data-toggle="tooltip"
                          title="<?php echo $hesklang['added_in_mods_for_hesk']; ?>">
                        <?php echo $hesklang['mods_for_hesk_acronym']; ?>
                    </span>
                </h1>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <h4><?php echo $hesklang['common_properties']; ?></h4>
                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="questionMarkColor"
                                   class="col-sm-7 col-xs-12 control-label"><?php echo $hesklang['questionMarkColor']; ?>
                                <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                                   data-placement="left"
                                   title="<?php echo $hesklang['questionMarkColor']; ?>"
                                   data-content="<?php echo $hesklang['questionMarkColorHelp']; ?>"></i>
                            </label>

                            <div class="col-sm-5 col-xs-12">
                                <input type="text" id="questionMarkColor" name="questionMarkColor"
                                       class="form-control"
                                       value="<?php echo $modsForHesk_settings['questionMarkColor']; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <h4><?php echo $hesklang['customer_view']; ?></h4>
                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="custom-customer-color-scheme" class="control-label col-sm-7 col-xs-12">
                                <?php echo $hesklang['use_theme_from_bootswatch_dot_com']; ?>
                                <i class="fa fa-question-circle settingsquestionmark" data-toggle="htmlpopover"
                                   data-placement="bottom"
                                   title="<?php echo $hesklang['use_theme_from_bootswatch_dot_com']; ?>"
                                   data-content="<?php echo $hesklang['use_theme_from_bootswatch_dot_com_help']; ?>"></i>
                            </label>
                            <div class="col-sm-5 col-xs-12 form-inline">
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="use-bootswatch-theme" value="1" data-show="bootswatch" data-hide="custom-colors" <?php if ($modsForHesk_settings['use_bootswatch_theme']) { echo 'checked';} ?>>
                                        <?php echo $hesklang['yes']; ?>
                                    </label>
                                </div><br>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="use-bootswatch-theme" value="0" data-show="custom-colors" data-hide="bootswatch" <?php if ($modsForHesk_settings['use_bootswatch_theme'] == 0) { echo 'checked';} ?>>
                                        <?php echo $hesklang['no']; ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" id="bootswatch" <?php if ($modsForHesk_settings['use_bootswatch_theme'] == 0) { echo ' style="display:none"'; } ?>>
                    <div id="loading-block">
                        <i class="fa fa-spinner fa-spin"></i> <?php echo $hesklang['loading_themes_from_bootswatch_dot_com']; ?>
                    </div>
                    <div id="templates"></div>
                    <div id="bootswatch-template" style="display: none">
                        <div class="col-sm-4 col-xs-12">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <img src="{{0}}" class="img-responsive" alt="Theme Thumbnail">
                                </div>
                                <div class="panel-footer">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="bootswatch-theme" value="{{1}}">
                                            <span>{{2}}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="current-bootswatch-theme" value="<?php echo $modsForHesk_settings['bootswatch_theme']; ?>">
                </div>
                <div id="custom-colors" <?php if ($modsForHesk_settings['use_bootswatch_theme']) { echo ' style="display:none"'; } ?>>
                    <div class="row">
                        <div class="col-sm-6 col-xs-12">
                            <?php
                            buildColorSchemeColorpicker('navbarBackgroundColor', 'navbarBackgroundColor', $modsForHesk_settings['navbarBackgroundColor'], 'Help');
                            ?>
                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <?php
                            buildColorSchemeColorpicker('navbarBrandColor', 'navbarBrandColor', $modsForHesk_settings['navbarBrandColor'], 'Help');
                            ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-xs-12">
                            <?php
                            buildColorSchemeColorpicker('navbarBrandHoverColor', 'navbarBrandHoverColor', $modsForHesk_settings['navbarBrandHoverColor'], 'Help');
                            ?>
                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <?php
                            buildColorSchemeColorpicker('navbarItemTextColor', 'navbarItemTextColor', $modsForHesk_settings['navbarItemTextColor'], 'Help');
                            ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-xs-12">
                            <?php
                            buildColorSchemeColorpicker('navbarItemTextHoverColor', 'navbarItemTextHoverColor', $modsForHesk_settings['navbarItemTextHoverColor'], 'Help');
                            ?>
                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <?php
                            buildColorSchemeColorpicker('navbarItemTextSelectedColor', 'navbarItemTextSelectedColor', $modsForHesk_settings['navbarItemTextSelectedColor'], 'Help');
                            ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-xs-12">
                            <?php
                            buildColorSchemeColorpicker('navbarItemSelectedBackgroundColor', 'navbarItemSelectedBackgroundColor', $modsForHesk_settings['navbarItemSelectedBackgroundColor'], 'Help');
                            ?>
                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <?php
                            buildColorSchemeColorpicker('dropdownItemTextColor', 'dropdownItemTextColor', $modsForHesk_settings['dropdownItemTextColor'], 'Help');
                            ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-xs-12">
                            <?php
                            buildColorSchemeColorpicker('dropdownItemTextHoverColor', 'dropdownItemTextHoverColor', $modsForHesk_settings['dropdownItemTextHoverColor'], 'Help');
                            ?>
                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <?php
                            buildColorSchemeColorpicker('dropdownBackgroundColor', 'dropdownBackgroundColor', $modsForHesk_settings['dropdownBackgroundColor'], 'Help');
                            ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-xs-12">
                            <?php
                            buildColorSchemeColorpicker('dropdownItemTextHoverBackgroundColor', 'dropdownItemTextHoverBackgroundColor', $modsForHesk_settings['dropdownItemTextHoverBackgroundColor'], 'Help');
                            ?>
                        </div>
                    </div>
                </div>
                <h4><?php echo $hesklang['admin_panel']; ?></h4>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="form-group">
                            <label for="admin-color-scheme"
                                   class="col-sm-3 col-xs-5 control-label"><?php echo $hesklang['color_preset']; ?>
                                <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                                   data-placement="left"
                                   title="<?php echo $hesklang['color_preset']; ?>"
                                   data-content="<?php echo $hesklang['color_preset_help']; ?>"></i>
                            </label>

                            <div class="col-sm-9 col-xs-7">
                                <select name="admin-color-scheme" id="admin-color-scheme" class="form-control">
                                    <option value="SELECT"><?php echo $hesklang['select_a_preset']; ?></option>
                                    <option value="blue"><?php echo $hesklang['preset_blue']; ?></option>
                                    <option value="blue-light"><?php echo $hesklang['preset_blue_light']; ?></option>
                                    <option value="yellow"><?php echo $hesklang['preset_yellow']; ?></option>
                                    <option value="yellow-light"><?php echo $hesklang['preset_yellow_light']; ?></option>
                                    <option value="green"><?php echo $hesklang['preset_green']; ?></option>
                                    <option value="green-light"><?php echo $hesklang['preset_green_light']; ?></option>
                                    <option value="purple"><?php echo $hesklang['preset_purple']; ?></option>
                                    <option value="purple-light"><?php echo $hesklang['preset_purple_light']; ?></option>
                                    <option value="red"><?php echo $hesklang['preset_red']; ?></option>
                                    <option value="red-light"><?php echo $hesklang['preset_red_light']; ?></option>
                                    <option value="black"><?php echo $hesklang['preset_black']; ?></option>
                                    <option value="black-light"><?php echo $hesklang['preset_black_light']; ?></option>
                                </select>
                            </div>
                            <script>
                                $('select[name="admin-color-scheme"]').change(function() {
                                    var val = $(this).val();

                                    if (val === 'SELECT') {
                                        return;
                                    }

                                    var lightTheme = val.match(/.+-light/i);

                                    $('#cpadmin-sidebar-background-color').colorpicker('setValue', lightTheme ? '#f9fafc' : '#222d32');
                                    $('#cpadmin-sidebar-header-background-color').colorpicker('setValue', lightTheme ? '#f9fafc' : '#1a2226');
                                    $('#cpadmin-sidebar-text-color').colorpicker('setValue', lightTheme ? '#444' : '#b8c7ce');
                                    $('#cpadmin-sidebar-header-text-color').colorpicker('setValue', lightTheme ? '#848484' : '#4b646f');
                                    $('#cpadmin-sidebar-text-hover-color').colorpicker('setValue', lightTheme ? '#444' : '#fff');
                                    $('#cpadmin-sidebar-background-hover-color').colorpicker('setValue', lightTheme ? '#f4f4f5' : '#1e282c');
                                    $('input[name="admin-sidebar-font-weight"]').val(lightTheme ? ['bold'] : ['normal']);

                                    $('#cpadmin-navbar-text-color').colorpicker('setValue', '#fff');
                                    $('#cpadmin-navbar-text-hover-color').colorpicker('setValue', '#fff');
                                    $('#cpadmin-navbar-brand-text-color').colorpicker('setValue', '#fff');
                                    $('#cpadmin-navbar-brand-text-hover-color').colorpicker('setValue', '#fff');
                                    if (val.match(/blue.*/i)) {
                                        $('#cpadmin-navbar-background-color').colorpicker('setValue', '#3c8dbc');
                                        $('#cpadmin-navbar-background-hover-color').colorpicker('setValue', '#367fa9');

                                        $('#cpadmin-navbar-brand-background-color').colorpicker('setValue', lightTheme ? '#3c8dbc' : '#367fa9');
                                        $('#cpadmin-navbar-brand-background-hover-color').colorpicker('setValue', lightTheme ? '#3b8ab8' : '#357ca5');
                                    } else if (val.match(/yellow.*/i)) {
                                        $('#cpadmin-navbar-background-color').colorpicker('setValue', '#f39c12');
                                        $('#cpadmin-navbar-background-hover-color').colorpicker('setValue', '#da8c10');

                                        $('#cpadmin-navbar-brand-background-color').colorpicker('setValue', lightTheme ? '#f39c12' : '#e08e0b');
                                        $('#cpadmin-navbar-brand-background-hover-color').colorpicker('setValue', lightTheme ? '#f39a0d' : '#db8b0b');
                                    } else if (val.match(/green.*/i)) {
                                        $('#cpadmin-navbar-background-color').colorpicker('setValue', '#00a65a');
                                        $('#cpadmin-navbar-background-hover-color').colorpicker('setValue', '#009551');

                                        $('#cpadmin-navbar-brand-background-color').colorpicker('setValue', lightTheme ? '#00a65a' : '#008d4c');
                                        $('#cpadmin-navbar-brand-background-hover-color').colorpicker('setValue', lightTheme ? '#00a157' : '#008749');
                                    } else if (val.match(/purple.*/i)) {
                                        $('#cpadmin-navbar-background-color').colorpicker('setValue', '#605ca8');
                                        $('#cpadmin-navbar-background-hover-color').colorpicker('setValue', '#565397');

                                        $('#cpadmin-navbar-brand-background-color').colorpicker('setValue', lightTheme ? '#605ca8' : '#555299');
                                        $('#cpadmin-navbar-brand-background-hover-color').colorpicker('setValue', lightTheme ? '#5d59a6' : '#545096');
                                    } else if (val.match(/red.*/i)) {
                                        $('#cpadmin-navbar-background-color').colorpicker('setValue', '#dd4b39');
                                        $('#cpadmin-navbar-background-hover-color').colorpicker('setValue', '#c64333');

                                        $('#cpadmin-navbar-brand-background-color').colorpicker('setValue', lightTheme ? '#dd4b39' : '#d73925');
                                        $('#cpadmin-navbar-brand-background-hover-color').colorpicker('setValue', lightTheme ? '#dc4735' : '#d33724');
                                    } else {
                                        //-- Black
                                        $('#cpadmin-navbar-background-color').colorpicker('setValue', '#fff');
                                        $('#cpadmin-navbar-background-hover-color').colorpicker('setValue', '#eee');

                                        $('#cpadmin-navbar-brand-background-color').colorpicker('setValue', '#fff');
                                        $('#cpadmin-navbar-brand-background-hover-color').colorpicker('setValue', '#fcfcfc');
                                    }
                                });
                            </script>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <div class="col-sm-5 col-sm-offset-7 col-xs-12">
                            <h4><?php echo $hesklang['navbar']; ?></h4>
                        </div>
                        <?php
                        buildColorSchemeColorpicker('admin-navbar-background-color', 'background_color', $modsForHesk_settings['admin_navbar_background']);
                        ?>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <div class="col-sm-5 col-sm-offset-7 col-xs-12">
                            <h4><?php echo $hesklang['navbar_brand']; ?></h4>
                        </div>
                        <?php
                        buildColorSchemeColorpicker('admin-navbar-brand-background-color', 'background_color', $modsForHesk_settings['admin_navbar_brand_background']);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <?php
                        buildColorSchemeColorpicker('admin-navbar-text-color', 'text_color', $modsForHesk_settings['admin_navbar_text']);
                        ?>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <?php
                        buildColorSchemeColorpicker('admin-navbar-brand-text-color', 'text_color', $modsForHesk_settings['admin_navbar_brand_text']);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <?php
                        buildColorSchemeColorpicker('admin-navbar-text-hover-color', 'text_hover_color', $modsForHesk_settings['admin_navbar_text_hover']);
                        ?>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <?php
                        buildColorSchemeColorpicker('admin-navbar-brand-text-hover-color', 'text_hover_color', $modsForHesk_settings['admin_navbar_brand_text_hover']);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <?php
                        buildColorSchemeColorpicker('admin-navbar-background-hover-color', 'background_hover_color', $modsForHesk_settings['admin_navbar_background_hover']);
                        ?>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <?php
                        buildColorSchemeColorpicker('admin-navbar-brand-background-hover-color', 'background_hover_color', $modsForHesk_settings['admin_navbar_brand_background_hover']);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <div class="col-sm-5 col-sm-offset-7 col-xs-12">
                            <h4><?php echo $hesklang['sidebar']; ?></h4>
                        </div>
                        <?php
                        buildColorSchemeColorpicker('admin-sidebar-background-color', 'background_color', $modsForHesk_settings['admin_sidebar_background']);
                        ?>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <div class="col-sm-5 col-sm-offset-7 col-xs-12">
                            <h4><?php echo $hesklang['sidebar_header']; ?></h4>
                        </div>
                        <?php
                        buildColorSchemeColorpicker('admin-sidebar-header-background-color', 'background_color', $modsForHesk_settings['admin_sidebar_header_background']);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <?php
                        buildColorSchemeColorpicker('admin-sidebar-text-color', 'text_color', $modsForHesk_settings['admin_sidebar_text']);
                        ?>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <?php
                        buildColorSchemeColorpicker('admin-sidebar-header-text-color', 'text_color', $modsForHesk_settings['admin_sidebar_header_text']);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <?php
                        buildColorSchemeColorpicker('admin-sidebar-text-hover-color', 'text_hover_color', $modsForHesk_settings['admin_sidebar_text_hover']);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <?php
                        buildColorSchemeColorpicker('admin-sidebar-background-hover-color', 'background_hover_color', $modsForHesk_settings['admin_sidebar_background_hover']);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="admin-sidebar-font-weight"
                                   class="col-sm-7 col-xs-12 control-label"><?php echo $hesklang['font_weight']; ?>
                                <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                                   data-placement="top"
                                   title="<?php echo $hesklang['font_weight']; ?>"
                                   data-content="<?php echo $hesklang['font_weight_help']; ?>"></i>
                            </label>

                            <div class="col-sm-5 col-xs-12 form-inline">
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="admin-sidebar-font-weight" value="normal"
                                            <?php echo $modsForHesk_settings['admin_sidebar_font_weight'] == 'normal' ? 'checked' : ''; ?>>
                                        <?php echo $hesklang['normal']; ?>
                                    </label>
                                </div><br>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="admin-sidebar-font-weight" value="bold"
                                            <?php echo $modsForHesk_settings['admin_sidebar_font_weight'] == 'bold' ? 'checked' : ''; ?>>
                                        <?php echo $hesklang['bold']; ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <h4><?php echo $hesklang['login_page']; ?></h4>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="form-group">
                            <label for="login-background" class="col-sm-3 col-xs-5 control-label">
                                <?php echo $hesklang['login_background']; ?>
                            </label>
                            <div class="col-sm-9 col-xs-7 form-inline">
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="login-background"
                                               data-activate="input[name='login-background-color']" data-deactivate="input[name='login-background-image']"
                                               value="color" <?php if ($modsForHesk_settings['login_background_type'] == 'color') { echo 'checked'; } ?>>
                                        <?php echo $hesklang['solid_color']; ?>
                                    </label>
                                </div>&nbsp;&nbsp;&nbsp;
                                <input title="<?php echo $hesklang['login_background_color']; ?>" type="text"
                                       name="login-background-color" class="form-control"
                                    <?php if ($modsForHesk_settings['login_background_type'] == 'image') { echo 'disabled'; } ?>>
                                <br>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="login-background"
                                               data-activate="input[name='login-background-image']" data-deactivate="input[name='login-background-color']"
                                               value="image" <?php if ($modsForHesk_settings['login_background_type'] == 'image') { echo 'checked'; } ?>>
                                        <?php echo $hesklang['image']; ?>
                                    </label>
                                </div>
                                <input title="<?php echo $hesklang['login_background_image']; ?>" type="file" name="login-background-image" style="display: inline;vertical-align: bottom" <?php if ($modsForHesk_settings['login_background_type'] == 'color') { echo 'disabled'; } ?>>
                                <?php if ($modsForHesk_settings['login_background_type'] == 'image'): ?>
                                    <br>
                                    <img src="<?php echo HESK_PATH . $hesk_settings['cache_dir']; ?>/lb_<?php echo $modsForHesk_settings['login_background']; ?>" alt="<?php echo $hesklang['login_background']; ?>" title="<?php echo $hesklang['login_background']; ?>" height="125" width="125" class="push-down-10">
                                <?php endif; ?>
                                <script type="text/javascript">
                                    $('input[name="login-background-color"]').colorpicker({
                                        format: 'hex',
                                        color: <?php if ($modsForHesk_settings['login_background_type'] == 'color') { echo "'{$modsForHesk_settings['login_background']}'"; } else { echo 'false'; } ?>
                                    });
                                </script>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="form-group">
                            <label for="login-box-header" class="col-sm-3 col-xs-5 control-label">
                                <?php echo $hesklang['login_box_header']; ?>
                            </label>
                            <div class="col-sm-9 col-xs-7 form-inline">
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="login-box-header" value="helpdesk-title" data-deactivate="input[name='login-box-header-image']" <?php if ($modsForHesk_settings['login_box_header'] == 'helpdesk-title') { echo 'checked'; } ?>>
                                        <?php echo $hesklang['hesk_title']; ?>
                                    </label>
                                </div><br>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="login-box-header" value="image" data-activate="input[name='login-box-header-image']" <?php if ($modsForHesk_settings['login_box_header'] == 'image') { echo 'checked'; } ?>>
                                        <?php echo $hesklang['image']; ?>
                                    </label>
                                    <input title="<?php echo $hesklang['login_header_image']; ?>" type="file" name="login-box-header-image" style="display: inline;vertical-align: bottom" <?php if ($modsForHesk_settings['login_box_header'] == 'helpdesk-title') { echo 'disabled'; } ?>>
                                    <?php if ($modsForHesk_settings['login_box_header'] == 'image'): ?>
                                        <br>
                                        <img src="<?php echo HESK_PATH . $hesk_settings['cache_dir']; ?>/lbh_<?php echo $modsForHesk_settings['login_box_header_image']; ?>" title="<?php echo $modsForHesk_settings['login_box_header_image']; ?>" alt="<?php echo $modsForHesk_settings['login_box_header_image']; ?>" style="height: 75px" class="push-down-10">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group" style="margin-left: 10px">
            <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>"/>
            <?php
            if ($enable_save_settings) {
                echo '<input type="submit" id="submitbutton" value="' . $hesklang['save_changes'] . '" class="btn btn-default" />';
            } else {
                echo '<input type="button" value="' . $hesklang['save_changes'] . ' (' . $hesklang['disabled'] . ')" class="btn btn-default"  disabled="disabled" /><br /><font class="error">' . $hesklang['e_save_settings'] . '</font>';
            }
            ?>
        </div>
    </form>
</section>
</div>
<script>
    $.ajax({
        url: 'https://bootswatch.com/api/3.json',
        method: 'GET',
        success: function(data) {
            for (var i in data.themes) {
                var template = $('#bootswatch-template').html();
                var theme = data.themes[i];
                template = template.replace('{{0}}', theme.thumbnail);
                template = template.replace('{{1}}', theme.cssCdn);
                template = template.replace('{{2}}', theme.name);

                $('#templates').append(template);
            }

            if ($('input[name="current-bootswatch-theme"]').val() !== '') {
                var value = $('input[name="current-bootswatch-theme"]').val();
                $('input[name="bootswatch-theme"][value="' + value + '"]').attr('checked', true);
            }
        },
        error: function(data) {
            console.error(data);
            var template = $($('#bootswatch-template').html());
            template.html(<?php echo json_encode($hesklang['unable_to_load_themes_from_bootswatch_dot_com']); ?>);
            $('#templates').append(template.html());
        },
        complete: function() {
            $('#bootswatch').find('#loading-block').hide();
        }
    });
</script>

    <?php
    require_once(HESK_PATH . 'inc/footer.inc.php');
    exit();

    function buildColorSchemeColorpicker($field_name, $label_key, $color, $help_suffix = '_help') {
        global $hesklang;

        echo '
        <div class="form-group">
            <label for="admin-navbar-background-color"
                   class="col-sm-7 col-xs-12 control-label">'. $hesklang[$label_key] . '
                <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                   data-placement="top"
                   title="' . htmlspecialchars($hesklang[$label_key]) . '"
                   data-content="' . htmlspecialchars($hesklang[$label_key . $help_suffix]) . '"></i>
            </label>

            <div class="col-sm-5 col-xs-12">
                <div id="cp' . $field_name . '" class="input-group">
                    <input type="text" id="' . $field_name . '" name="' . $field_name . '"
                       class="form-control"
                       value="' . $color . '">
                    <span class="input-group-addon"><i></i></span>
                </div>
            </div>
        </div>
        <script>
            $("#cp' . $field_name . '").colorpicker({
                color: "' . $color . '",
                format: "hex"
            });
        </script>
        ';
    }


    function hesk_testLanguage($return_options = 0)
    {
        global $hesk_settings, $hesklang, $modsForHesk_settings;

        /* Get a list of valid emails */
        include_once(HESK_PATH . 'inc/email_functions.inc.php');
        $valid_emails = array_keys(hesk_validEmails());

        $dir = HESK_PATH . 'language/';
        $path = opendir($dir);

        $text = '';
        $html = '';

        $text .= "/language\n";

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
                $text .= "   |-> /$subdir\n";
                $text .= "        |-> text.php: ";
                if (file_exists($langu)) {
                    $tmp = file_get_contents($langu);

                    // Some servers add slashes to file_get_contents output
                    if (strpos($tmp, '[\\\'LANGUAGE\\\']') !== false) {
                        $tmp = stripslashes($tmp);
                    }

                    $err = '';
                    if (!preg_match('/\$hesklang\[\'LANGUAGE\'\]\=\'(.*)\'\;/', $tmp, $l)) {
                        $err .= "              |---->  MISSING: \$hesklang['LANGUAGE']\n";
                    }

                    if (strpos($tmp, '$hesklang[\'ENCODING\']') === false) {
                        $err .= "              |---->  MISSING: \$hesklang['ENCODING']\n";
                    }

                    if (strpos($tmp, '$hesklang[\'_COLLATE\']') === false) {
                        $err .= "              |---->  MISSING: \$hesklang['_COLLATE']\n";
                    }

                    if (strpos($tmp, '$hesklang[\'EMAIL_HR\']') === false) {
                        $err .= "              |---->  MISSING: \$hesklang['EMAIL_HR']\n";
                    }

                    /* Check if language file is for current version */
                    if (strpos($tmp, '$hesklang[\'LANGUAGE_EN\']') === false) {
                        $err .= "              |---->  WRONG VERSION (not " . $hesk_settings['hesk_version'] . ")\n";
                    }

                    if ($err) {
                        $text .= "ERROR\n" . $err;
                        $add = 0;
                    } else {
                        $l[1] = hesk_input($l[1]);
                        $l[1] = str_replace('|', ' ', $l[1]);
                        $text .= "OK ($l[1])\n";
                    }
                } else {
                    $text .= "ERROR\n";
                    $text .= "              |---->  MISSING: text.php\n";
                    $add = 0;
                }

                /* Check emails folder */
                $text .= "        |-> /emails:  ";
                if (file_exists($email) && filetype($email) == 'dir') {
                    $err = '';
                    foreach ($valid_emails as $eml) {
                        if (!file_exists($email . '/' . $eml . '.txt')) {
                            $err .= "              |---->  MISSING: $eml.txt\n";
                        }
                    }

                    if ($err) {
                        $text .= "ERROR\n" . $err;
                        $add = 0;
                    } else {
                        $text .= "OK\n";
                    }
                } else {
                    $text .= "ERROR\n";
                    $text .= "              |---->  MISSING: /emails folder\n";
                    $add = 0;
                }

                $text .= "\n";

                /* Add an option for the <select> if needed */
                if ($add) {
                    if ($l[1] == $hesk_settings['language']) {
                        $html .= '<option value="' . $subdir . '|' . $l[1] . '" selected="selected">' . $l[1] . '</option>';
                    } else {
                        $html .= '<option value="' . $subdir . '|' . $l[1] . '">' . $l[1] . '</option>';
                    }
                }
            }
        }

        closedir($path);

        /* Output select options or the test log for debugging */
        if ($return_options) {
            return $html;
        } else {
            ?>
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML; 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml" lang="en">
            <head>
                <title><?php echo $hesklang['s_inl']; ?></title>
                <meta http-equiv="Content-Type" content="text/html;charset=<?php echo $hesklang['ENCODING']; ?>"/>
                <style type="text/css">
                    body {
                        margin: 5px 5px;
                        padding: 0;
                        background: #fff;
                        color: black;
                        font: 68.8%/1.5 Verdana, Geneva, Arial, Helvetica, sans-serif;
                        text-align: left;
                    }

                    p {
                        color: black;
                        font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
                        font-size: 1.0em;
                    }

                    h3 {
                        color: #AF0000;
                        font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
                        font-weight: bold;
                        font-size: 1.0em;
                        text-align: center;
                    }

                    .title {
                        color: black;
                        font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
                        font-weight: bold;
                        font-size: 1.0em;
                    }

                    .wrong {
                        color: red;
                    }

                    .correct {
                        color: green;
                    }

                    pre {
                        font-size: 1.2em;
                    }
                </style>
            </head>
            <body>

            <h3><?php echo $hesklang['s_inl']; ?></h3>

            <p><i><?php echo $hesklang['s_inle']; ?></i></p>

            <pre><?php echo $text; ?></pre>

            <p>&nbsp;</p>

            <p align="center"><a
                    href="admin_settings.php?test_languages=1&amp;<?php echo rand(10000, 99999); ?>"><?php echo $hesklang['ta']; ?></a>
                | <a href="#" onclick="Javascript:window.close()"><?php echo $hesklang['cwin']; ?></a></p>

            <p>&nbsp;</p>

            </body>

            </html>
            <?php
            exit();
        }
    } // END hesk_testLanguage()
    ?>
