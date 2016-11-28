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
define('PAGE_TITLE', 'ADMIN_PROFILE');
define('MFH_PAGE_LAYOUT', 'TOP_ONLY');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/profile_functions.inc.php');
require(HESK_PATH . 'inc/mail_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

/* Check permissions */
$can_view_tickets = hesk_checkPermission('can_view_tickets', 0);
$can_reply_tickets = hesk_checkPermission('can_reply_tickets', 0);
$can_view_unassigned = hesk_checkPermission('can_view_unassigned', 0);

/* Update profile? */
if (!empty($_POST['action'])) {
    // Demo mode
    if (defined('HESK_DEMO')) {
        hesk_process_messages($hesklang['sdemo'], 'profile.php', 'NOTICE');
    }

    // Update profile
    update_profile();
} else {
    $res = hesk_dbQuery('SELECT * FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `id` = '" . intval($_SESSION['id']) . "' LIMIT 1");
    $tmp = hesk_dbFetchAssoc($res);

    foreach ($tmp as $k => $v) {
        if ($k == 'pass') {
            if ($v == '499d74967b28a841c98bb4baaabaad699ff3c079') {
                define('WARN_PASSWORD', true);
            }
            continue;
        } elseif ($k == 'categories') {
            continue;
        }
        $_SESSION['new'][$k] = $v;
    }
}

if (!isset($_SESSION['new']['username'])) {
    $_SESSION['new']['username'] = '';
}

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print admin navigation */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>
<div class="content-wrapper">
    <section class="content">
    <div class="box">
        <div class="box-header with-border">
            <h1 class="box-title">
                <?php echo $hesklang['profile_for']; ?> <b><?php echo $_SESSION['new']['user']; ?></b>
            </h1>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <?php echo $hesklang['req_marked_with']; ?> <span class="important">*</span>
            <?php
            /* This will handle error, success and notice messages */
            hesk_handle_messages();

            if (defined('WARN_PASSWORD')) {
                hesk_show_notice($hesklang['chdp2'], $hesklang['security']);
            }

            if ($hesk_settings['can_sel_lang']) {
                /* Update preferred language in the database? */
                if (isset($_GET['save_language'])) {
                    $newlang = hesk_input(hesk_GET('language'));

                    /* Only update if it's a valid language */
                    if (isset($hesk_settings['languages'][$newlang])) {
                        $newlang = ($newlang == HESK_DEFAULT_LANGUAGE) ? "NULL" : "'" . hesk_dbEscape($newlang) . "'";
                        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `language`=$newlang WHERE `id`='" . intval($_SESSION['id']) . "'");
                    }
                }

                $str = '<form class="form-horizontal" role="form" method="get" action="profile.php">';
                $str .= '<input type="hidden" name="save_language" value="1" />';
                $str .= '<div class="form-group">';
                $str .= '<label for="language" class="col-sm-3 control-label">' . $hesklang['chol'] . ':</label>';

                if (!isset($_GET)) {
                    $_GET = array();
                }

                foreach ($_GET as $k => $v) {
                    if ($k == 'language' || $k == 'save_language') {
                        continue;
                    }
                    $str .= '<input type="hidden" name="' . htmlentitieshesk_htmlentities($k) . '" value="' . hesk_htmlentities($v) . '" />';
                }

                $str .= '<div class="col-sm-9"><select class="form-control" name="language" onchange="this.form.submit()">';
                $str .= hesk_listLanguages(0);
                $str .= '</select></div>';
                $str .= '</div>'

                ?>
                <script language="javascript" type="text/javascript">
                    document.write('<?php echo str_replace(array('"','<','=','>',"'"),array('\42','\74','\75','\76','\47'),$str . '</form>'); ?>');
                </script>
                <noscript>
                    <?php
                    echo $str . '<input type="submit" value="' . $hesklang['go'] . '" /></form>';
                    ?>
                </noscript>
                <?php
            }
            ?>

            <form role="form" class="form-horizontal" method="post" action="profile.php" name="form1" data-toggle="validator">
                <?php hesk_profile_tab('new'); ?>
            </form>
        </div>
    </div>
</section>
</div>

<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


/*** START FUNCTIONS ***/

function update_profile()
{
    global $hesk_settings, $hesklang, $can_view_unassigned;

    /* A security check */
    hesk_token_check('POST');

    $sql_pass = '';
    $sql_username = '';

    $hesk_error_buffer = '';

    $_SESSION['new']['name'] = hesk_input(hesk_POST('name')) or $hesk_error_buffer .= '<li>' . $hesklang['enter_your_name'] . '</li>';
    $_SESSION['new']['email'] = hesk_validateEmail(hesk_POST('email'), 'ERR', 0) or $hesk_error_buffer = '<li>' . $hesklang['enter_valid_email'] . '</li>';
    $_SESSION['new']['signature'] = hesk_input(hesk_POST('signature'));

    /* Signature */
    if (strlen($_SESSION['new']['signature']) > 1000) {
        $hesk_error_buffer .= '<li>' . $hesklang['signature_long'] . '</li>';
    }

    /* Admins can change username */
    if ($_SESSION['isadmin']) {
        $_SESSION['new']['user'] = hesk_input(hesk_POST('user')) or $hesk_error_buffer .= '<li>' . $hesklang['enter_username'] . '</li>';

        /* Check for duplicate usernames */
        $result = hesk_dbQuery("SELECT `id` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `user`='" . hesk_dbEscape($_SESSION['new']['user']) . "' AND `id`!='" . intval($_SESSION['id']) . "' LIMIT 1");
        if (hesk_dbNumRows($result) != 0) {
            $hesk_error_buffer .= '<li>' . $hesklang['duplicate_user'] . '</li>';
        } else {
            $sql_username = ",`user`='" . hesk_dbEscape($_SESSION['new']['user']) . "'";
        }
    }

    /* Change password? */
    $newpass = hesk_input(hesk_POST('newpass'));
    $passlen = strlen($newpass);
    if ($passlen > 0) {
        /* At least 5 chars? */
        if ($passlen < 5) {
            $hesk_error_buffer .= '<li>' . $hesklang['password_not_valid'] . '</li>';
        } /* Check password confirmation */
        else {
            $newpass2 = hesk_input(hesk_POST('newpass2'));

            if ($newpass != $newpass2) {
                $hesk_error_buffer .= '<li>' . $hesklang['passwords_not_same'] . '</li>';
            } else {
                $newpass_hash = hesk_Pass2Hash($newpass);
                if ($newpass_hash == '499d74967b28a841c98bb4baaabaad699ff3c079') {
                    define('WARN_PASSWORD', true);
                }
                $sql_pass = ',`pass`=\'' . $newpass_hash . '\'';
            }
        }
    }

    /* After reply */
    $_SESSION['new']['afterreply'] = intval(hesk_POST('afterreply'));
    if ($_SESSION['new']['afterreply'] != 1 && $_SESSION['new']['afterreply'] != 2) {
        $_SESSION['new']['afterreply'] = 0;
    }
    $_SESSION['new']['notify_customer_new'] = isset($_POST['notify_customer_new']) ? 1 : 0;
    $_SESSION['new']['notify_customer_reply'] = isset($_POST['notify_customer_reply']) ? 1 : 0;
    $_SESSION['new']['show_suggested'] = isset($_POST['show_suggested']) ? 1 : 0;
    $_SESSION['new']['autoreload'] = isset($_POST['autoreload']) ? 1 : 0;

    if ($_SESSION['new']['autoreload']) {
        $_SESSION['new']['autoreload'] = intval(hesk_POST('reload_time'));

        if (hesk_POST('secmin') == 'min') {
            $_SESSION['new']['autoreload'] *= 60;
        }

        if ($_SESSION['new']['autoreload'] < 0 || $_SESSION['new']['autoreload'] > 65535) {
            $_SESSION['new']['autoreload'] = 30;
        }
    } else {
        hesk_setcookie('autorefresh', '');
    }

    /* Auto-start ticket timer */
    $_SESSION['new']['autostart'] = isset($_POST['autostart']) ? 1 : 0;

    /* Default calendar view */
    $_SESSION['new']['default_calendar_view'] = hesk_POST('default-calendar-view', 0);

    /* Notifications */
    if (!(!$_SESSION[$session_array]['isadmin'] && isset($_SESSION[$session_array]['heskprivileges'])
        && strpos($_SESSION[$session_array]['heskprivileges'], 'can_change_notification_settings') === false)) {
        $_SESSION['new']['notify_new_unassigned'] = empty($_POST['notify_new_unassigned']) || !$can_view_unassigned ? 0 : 1;
        $_SESSION['new']['notify_new_my'] = empty($_POST['notify_new_my']) ? 0 : 1;
        $_SESSION['new']['notify_reply_unassigned'] = empty($_POST['notify_reply_unassigned']) || !$can_view_unassigned ? 0 : 1;
        $_SESSION['new']['notify_reply_my'] = empty($_POST['notify_reply_my']) ? 0 : 1;
        $_SESSION['new']['notify_assigned'] = empty($_POST['notify_assigned']) ? 0 : 1;
        $_SESSION['new']['notify_note'] = empty($_POST['notify_note']) ? 0 : 1;
        $_SESSION['new']['notify_note_unassigned'] = empty($_POST['notify_note_unassigned']) ? 0 : 1;
        $_SESSION['new']['notify_pm'] = empty($_POST['notify_pm']) ? 0 : 1;
        $_SESSION['new']['notify_overdue_unassigned'] = empty($_POST['notify_overdue_unassigned']) ? 0 : 1;
    }

    /* Any errors? */
    if (strlen($hesk_error_buffer)) {
        /* Process the session variables */
        $_SESSION['new'] = hesk_stripArray($_SESSION['new']);

        $hesk_error_buffer = $hesklang['rfm'] . '<br /><br /><ul>' . $hesk_error_buffer . '</ul>';
        hesk_process_messages($hesk_error_buffer, 'NOREDIRECT');
    } else {
        /* Update database */
        hesk_dbQuery(
            "UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET
	    `name`='" . hesk_dbEscape($_SESSION['new']['name']) . "',
	    `email`='" . hesk_dbEscape($_SESSION['new']['email']) . "',
		`signature`='" . hesk_dbEscape($_SESSION['new']['signature']) . "'
        $sql_username
		$sql_pass ,
	    `afterreply`='" . intval($_SESSION['new']['afterreply']) . "' ,
        `autostart`='" . intval($_SESSION['new']['autostart']) . "' ,
        `autoreload`='".($_SESSION['new']['autoreload'])."' ,
	    `notify_new_unassigned`='" . intval($_SESSION['new']['notify_new_unassigned']) . "' ,
        `notify_new_my`='" . intval($_SESSION['new']['notify_new_my']) . "' ,
        `notify_reply_unassigned`='" . intval($_SESSION['new']['notify_reply_unassigned']) . "' ,
        `notify_reply_my`='" . intval($_SESSION['new']['notify_reply_my']) . "' ,
        `notify_assigned`='" . intval($_SESSION['new']['notify_assigned']) . "' ,
        `notify_pm`='" . intval($_SESSION['new']['notify_pm']) . "',
        `notify_note`='" . intval($_SESSION['new']['notify_note']) . "',
        `notify_note_unassigned`='" . intval($_SESSION['new']['notify_note_unassigned']) . "',
        `notify_customer_new`='" . $_SESSION['new']['notify_customer_new'] . "',
        `notify_customer_reply`='" . $_SESSION['new']['notify_customer_reply'] . "',
        `notify_overdue_unassigned`='" . $_SESSION['new']['notify_overdue_unassigned'] . "',
        `show_suggested`='" . $_SESSION['new']['show_suggested'] . "',
        `default_calendar_view`=" . intval($_SESSION['new']['default_calendar_view']) . "
	    WHERE `id`='" . intval($_SESSION['id']) . "'"
        );

        /* Process the session variables */
        $_SESSION['new'] = hesk_stripArray($_SESSION['new']);

        // Do we need a new session_verify tag?
        if (strlen($sql_username) && strlen($sql_pass)) {
            $_SESSION['session_verify'] = hesk_activeSessionCreateTag($_SESSION['new']['user'], $newpass_hash);
        } elseif (strlen($sql_pass)) {
            $_SESSION['session_verify'] = hesk_activeSessionCreateTag($_SESSION['user'], $newpass_hash);
        } elseif (strlen($sql_username)) {
            $res = hesk_dbQuery('SELECT `pass` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `id` = '" . intval($_SESSION['id']) . "' LIMIT 1");
            $_SESSION['session_verify'] = hesk_activeSessionCreateTag($_SESSION['new']['user'], hesk_dbResult($res));
        }

        /* Update session variables */
        foreach ($_SESSION['new'] as $k => $v) {
            $_SESSION[$k] = $v;
        }
        unset($_SESSION['new']);

        hesk_process_messages($hesklang['profile_updated_success'], 'profile.php', 'SUCCESS');
    }
} // End update_profile()

?>
