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
define('PAGE_TITLE', 'ADMIN_MAIL');
define('MFH_PAGE_LAYOUT', 'TOP_AND_SIDE');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/mail_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
require(HESK_PATH . 'inc/email_functions.inc.php');
hesk_isLoggedIn();

$modsForHesk_settings = mfh_getSettings();

/* List of staff */
$admins = array();
$res = hesk_dbQuery("SELECT `id`,`name` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ORDER BY `name` ASC");
while ($row = hesk_dbFetchAssoc($res)) {
    $admins[$row['id']] = $row['name'];
}

/* What folder are we in? */
$hesk_settings['mailtmp']['inbox'] = '<a href="mail.php"><i class="fa fa-fw fa-download"></i>' . $hesklang['inbox'] . '</a>';
$hesk_settings['mailtmp']['outbox'] = '<a href="mail.php?folder=outbox"><i class="fa fa-fw fa-upload"></i>' . $hesklang['outbox'] . '</a>';
$hesk_settings['mailtmp']['new'] = '<a href="mail.php?a=new"><i class="fa fa-fw fa-pencil-square-o"></i>' . $hesklang['m_new'] . '</a>';

/* Get action */
if ($action = hesk_REQUEST('a')) {
    if (defined('HESK_DEMO') && $action != 'new' && $action != 'read') {
        hesk_process_messages($hesklang['ddemo'], 'mail.php', 'NOTICE');
    }
}

/* Sub-page specific settings */
$inbox_active = '';
$outbox_active = '';
$new_active = '';
if (isset($_GET['folder']) && hesk_GET('folder') == 'outbox') {
    $outbox_active = ' class="active"';
    $hesk_settings['mailtmp']['this'] = 'from';
    $hesk_settings['mailtmp']['other'] = 'to';
    $hesk_settings['mailtmp']['m_from'] = $hesklang['m_to'];
    $hesk_settings['mailtmp']['folder'] = 'outbox';
} elseif ($action == 'new') {
    $new_active = ' class="active"';
    $_SESSION['hide']['list'] = 1;

    /* Do we have a recipient selected? */
    if (!isset($_SESSION['mail']['to']) && isset($_GET['id'])) {
        $_SESSION['mail']['to'] = intval(hesk_GET('id'));
    }
} else {
    $inbox_active = ' class="active"';
    $hesk_settings['mailtmp']['this'] = 'to';
    $hesk_settings['mailtmp']['other'] = 'from';
    $hesk_settings['mailtmp']['m_from'] = $hesklang['m_from'];
    if ($action != 'read') {
        $hesk_settings['mailtmp']['folder'] = '';
    }
}

/* What should we do? */
switch ($action) {
    case 'send':
        mail_send();
        break;
    case 'mark_read':
        mail_mark_read();
        break;
    case 'mark_unread':
        mail_mark_unread();
        break;
    case 'delete':
        mail_delete();
        break;
}

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>

<script language="javascript" type="text/javascript"><!--
    function confirm_delete() {
        if (confirm('<?php echo addslashes($hesklang['delete_saved']); ?>')) {
            return true;
        }
        else {
            return false;
        }
    }
    //-->
</script>
<aside class="main-sidebar">
    <section class="sidebar" style="height: auto">
        <ul class="sidebar-menu">
            <li class="header text-uppercase"><?php echo $hesklang['navigation']; ?></li>
            <li<?php echo $inbox_active; ?>>
                <?php echo $hesk_settings['mailtmp']['inbox']; ?>
            </li>
            <li<?php echo $outbox_active; ?>>
                <?php echo $hesk_settings['mailtmp']['outbox']; ?>
            </li>
            <li<?php echo $new_active; ?>>
                <?php echo $hesk_settings['mailtmp']['new']; ?>
            </li>
        </ul>
    </section>
</aside>
<div class="content-wrapper">
    <section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php
            hesk_handle_messages();
            /* Show a message? */
            if ($action == 'read') {
                show_message();
            }
            if (!isset($_SESSION['hide']['list'])):
            ?>
                <div class="box">
                    <div class="box-header with-border">
                        <h1 class="box-title">
                            <?php echo $hesklang['m_h']; ?>
                        </h1>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php mail_list_messages(); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            /* Show new message form */
            show_new_form();

            /* Clean unneeded session variables */
            hesk_cleanSessionVars('hide');
            hesk_cleanSessionVars('mail');
            ?>
        </div>
    </div>
</section>
</div>
<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


/*** START FUNCTIONS ***/


function mail_delete()
{
    global $hesk_settings, $hesklang;

    /* A security check */
    hesk_token_check();

    $ids = mail_get_ids();

    if ($ids) {
        foreach ($ids as $id) {
            /* If both correspondents deleted the mail remove it from database, otherwise mark as deleted by this user */
            hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mail` SET `deletedby`='" . intval($_SESSION['id']) . "' WHERE `id`='" . intval($id) . "' AND (`to`='" . intval($_SESSION['id']) . "' OR `from`='" . intval($_SESSION['id']) . "') AND `deletedby`=0");

            if (hesk_dbAffectedRows() != 1) {
                hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mail` WHERE `id`='" . intval($id) . "' AND (`to`='" . intval($_SESSION['id']) . "' OR `from`='" . intval($_SESSION['id']) . "') AND `deletedby`!=0");
            }
        }

        hesk_process_messages($hesklang['smdl'], 'NOREDIRECT', 'SUCCESS');
    }

    return true;
} // END mail_mark_unread()


function mail_mark_unread()
{
    global $hesk_settings, $hesklang;

    /* A security check */
    hesk_token_check();

    $ids = mail_get_ids();

    if ($ids) {
        foreach ($ids as $id) {
            hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mail` SET `read`='0' WHERE `id`='" . intval($id) . "' AND `to`='" . intval($_SESSION['id']) . "'");
        }

        hesk_process_messages($hesklang['smmu'], 'NOREDIRECT', 'SUCCESS');
    }

    return true;
} // END mail_mark_unread()


function mail_mark_read()
{
    global $hesk_settings, $hesklang;

    /* A security check */
    hesk_token_check('POST');

    $ids = mail_get_ids();

    if ($ids) {
        foreach ($ids as $id) {
            hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mail` SET `read`='1' WHERE `id`='" . intval($id) . "' AND `to`='" . intval($_SESSION['id']) . "'");
        }

        hesk_process_messages($hesklang['smmr'], 'NOREDIRECT', 'SUCCESS');
    }

    return true;
} // END mail_mark_read()


function mail_get_ids()
{
    global $hesk_settings, $hesklang;

    // Mail id as a query parameter?
    if ($id = hesk_GET('id', false)) {
        return array($id);
    } // Mail id as a post array?
    elseif (isset($_POST['id']) && is_array($_POST['id'])) {
        return array_map('intval', $_POST['id']);
    } // No valid ID parameter
    else {
        hesk_process_messages($hesklang['nms'], 'NOREDIRECT', 'NOTICE');
        return false;
    }

} // END mail_get_ids()


function mail_send()
{
    global $hesk_settings, $hesklang, $modsForHesk_settings;

    /* A security check */
    hesk_token_check('POST');

    $hesk_error_buffer = '';

    /* Recipient */
    $_SESSION['mail']['to'] = intval(hesk_POST('to'));

    /* Valid recipient? */
    if (empty($_SESSION['mail']['to'])) {
        $hesk_error_buffer .= '<li>' . $hesklang['m_rec'] . '</li>';
    } elseif ($_SESSION['mail']['to'] == $_SESSION['id']) {
        $hesk_error_buffer .= '<li>' . $hesklang['m_inr'] . '</li>';
    } else {
        $res = hesk_dbQuery("SELECT `name`,`email`,`notify_pm` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `id`='" . intval($_SESSION['mail']['to']) . "' LIMIT 1");
        $num = hesk_dbNumRows($res);
        if (!$num) {
            $hesk_error_buffer .= '<li>' . $hesklang['m_inr'] . '</li>';
        } else {
            $pm_recipient = hesk_dbFetchAssoc($res);
        }
    }

    /* Subject */
    $_SESSION['mail']['subject'] = hesk_input(hesk_POST('subject')) or $hesk_error_buffer .= '<li>' . $hesklang['m_esu'] . '</li>';

    /* Message */
    $_SESSION['mail']['message'] = hesk_input(hesk_POST('message')) or $hesk_error_buffer .= '<li>' . $hesklang['enter_message'] . '</li>';

    // Attach signature to the message?
    if (!empty($_POST['signature'])) {
        $_SESSION['mail']['message'] .= "\n\n" . addslashes($_SESSION['signature']) . "\n";
    }

    /* Any errors? */
    if (strlen($hesk_error_buffer)) {
        $_SESSION['hide']['list'] = 1;
        $hesk_error_buffer = $hesklang['rfm'] . '<br /><br /><ul>' . $hesk_error_buffer . '</ul>';
        hesk_process_messages($hesk_error_buffer, 'NOREDIRECT');
    } else {
        $_SESSION['mail']['message'] = hesk_makeURL($_SESSION['mail']['message']);
        $_SESSION['mail']['message'] = nl2br($_SESSION['mail']['message']);

        hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mail` (`from`,`to`,`subject`,`message`,`dt`,`read`) VALUES ('" . intval($_SESSION['id']) . "','" . intval($_SESSION['mail']['to']) . "','" . hesk_dbEscape($_SESSION['mail']['subject']) . "','" . hesk_dbEscape($_SESSION['mail']['message']) . "',NOW(),'0')");

        /* Notify receiver via e-mail? */
        if (isset($pm_recipient) && $pm_recipient['notify_pm']) {
            $pm_id = hesk_dbInsertID();

            $pm = array(
                'name' => hesk_msgToPlain(addslashes($_SESSION['name']), 1, 1),
                'subject' => hesk_msgToPlain($_SESSION['mail']['subject'], 1, 1),
                'message' => hesk_msgToPlain($_SESSION['mail']['message'], 1, 1),
                'id' => $pm_id,
            );

            /* Format email subject and message for recipient */
            $subject = hesk_getEmailSubject('new_pm', $pm, 0);
            $message = hesk_getEmailMessage('new_pm', $pm, $modsForHesk_settings, 1, 0);
            $htmlMessage = hesk_getHtmlMessage('new_pm', $pm, $modsForHesk_settings, 1, 0);
            $hasMessage = hesk_doesTemplateHaveTag('new_pm', '%%MESSAGE%%', $modsForHesk_settings);

            /* Send e-mail */
            hesk_mail($pm_recipient['email'], $subject, $message, $htmlMessage, $modsForHesk_settings, array(), array(), $hasMessage);
        }

        unset($_SESSION['mail']);

        hesk_process_messages($hesklang['m_pms'], './mail.php', 'SUCCESS');
    }
} // END mail_send()


function show_message()
{
	global $hesk_settings, $hesklang, $admins;

		$id = intval( hesk_GET('id') );

		/* Get the message details */
		$res = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."mail` WHERE `id`='".intval($id)."' AND `deletedby`!='".intval($_SESSION['id'])."' LIMIT 1");
		$num = hesk_dbNumRows($res);

	    if ($num)
	    {
	    	$pm = hesk_dbFetchAssoc($res);

	        /* Allowed to read the message? */
	        if ($pm['to'] == $_SESSION['id'])
	        {

			    if (!isset($_SESSION['mail']['subject']))
			    {
			    	$_SESSION['mail']['subject'] = $hesklang['m_re'] . ' ' . $pm['subject'];
			    }

			    if (!isset($_SESSION['mail']['to']))
			    {
			    	$_SESSION['mail']['to'] = $pm['from'];
			    }

	        }
	        elseif ($pm['from'] == $_SESSION['id'])
	        {

			    if (!isset($_SESSION['mail']['subject']))
			    {
			    	$_SESSION['mail']['subject'] = $hesklang['m_fwd'] . ' ' . $pm['subject'];
			    }

			    if (!isset($_SESSION['mail']['to']))
			    {
			    	$_SESSION['mail']['to'] = $pm['to'];
			    }

				$hesk_settings['mailtmp']['this']   = 'from';
				$hesk_settings['mailtmp']['other']  = 'to';
				$hesk_settings['mailtmp']['m_from'] = $hesklang['m_to'];
				$hesk_settings['mailtmp']['outbox'] = '<b>'.$hesklang['outbox'].'</b>';
				$hesk_settings['mailtmp']['inbox']  = '<a href="mail.php">'.$hesklang['inbox'].'</a>';
				$hesk_settings['mailtmp']['outbox'] = '<a href="mail.php?folder=outbox">'.$hesklang['outbox'].'</a>';

	        }
	        else
	        {
	        	hesk_process_message($hesklang['m_ena'],'mail.php');
	        }

	        /* Mark as read */
	        if ($hesk_settings['mailtmp']['this'] == 'to' && !$pm['read'])
	        {
				hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."mail` SET `read`='1' WHERE `id`='".intval($id)."'");
	        }

	        $pm['name'] = isset($admins[$pm[$hesk_settings['mailtmp']['other']]]) ? '<a href="mail.php?a=new&amp;id='.$pm[$hesk_settings['mailtmp']['other']].'">'.$admins[$pm[$hesk_settings['mailtmp']['other']]].'</a>' : (($pm['from'] == 9999) ? '<a href="https://www.hesk.com" target="_blank">HESK.com</a>' : $hesklang['e_udel']);
            
            $pm['dt'] = hesk_dateToString($pm['dt'],0,1,0,true);
			?>
            <div class="box">
                <div class="box-header with-border">
                    <h1 class="box-title">
                        <?php echo $hesklang['private_message_header']; ?>
                    </h1>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="callout callout-info">
                        <div class="row">
                            <div class="col-md-4 col-sm-6">
                                <b><?php echo $hesk_settings['mailtmp']['m_from']; ?></b>
                                <?php echo $pm['name']; ?>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <b><?php echo $hesklang['date_colon']; ?></b>
                                <?php echo $pm['dt']; ?>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <b><?php echo $hesklang['m_sub']; ?></b>
                                <?php echo $pm['subject']; ?>
                            </div>
                        </div>
                    </div>
                    <table border="0" cellspacing="0" cellpadding="0" width="100%">
                        <tr>
                            <td class="text-right" style="vertical-align:top;">



                            </td>
                        </tr>
                    </table>
                    <p><?php echo $pm['message']; ?></p>
                </div>
                <div class="box-footer">
                    <div class="pull-right">
                        <?php
                        $folder = '&amp;folder=outbox';
                        if ($pm['to'] == $_SESSION['id'])
                        {
                            echo '<a class="btn btn-default" href="mail.php?a=mark_unread&amp;id='.$id.'&amp;token='.hesk_token_echo(0).'"><i class="fa fa-envelope-o icon-link"></i> '.$hesklang['mau'].'</a> ';
                            $folder = '';
                        }
                        echo '<a class="btn btn-danger" href="mail.php?a=delete&amp;id='.$id.'&amp;token='.hesk_token_echo(0).$folder.'" onclick="return hesk_confirmExecute(\''.hesk_makeJsString($hesklang['delm']).'?\');"><i class="fa fa-times icon-link"></i> '.$hesklang['delm'].'</a>';
                        ?>
                    </div>
                </div>
            </div>
			<?php
	    } // END if $num

		$_SESSION['hide']['list'] = 1;

} // END show_message()


function mail_list_messages()
{
    global $hesk_settings, $hesklang, $admins;

    $href = 'mail.php';
    $query = '';
    if ($hesk_settings['mailtmp']['folder'] == 'outbox') {
        $query .= 'folder=outbox&amp;';
    }
    $query .= 'page=';

    $maxresults = 30;

    $tmp = intval(hesk_GET('page', 1));
    $page = ($tmp > 1) ? $tmp : 1;

    /* List of private messages */
    $res = hesk_dbQuery("SELECT COUNT(*) FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mail` WHERE `" . hesk_dbEscape($hesk_settings['mailtmp']['this']) . "`='" . intval($_SESSION['id']) . "' AND `deletedby`!='" . intval($_SESSION['id']) . "'");
    $total = hesk_dbResult($res, 0, 0);

    if ($total > 0) {

        $pages = ceil($total / $maxresults) or $pages = 1;
        if ($page > $pages) {
            $page = $pages;
        }
        $limit_down = ($page * $maxresults) - $maxresults;

        $prev_page = ($page - 1 <= 0) ? 0 : $page - 1;
        $next_page = ($page + 1 > $pages) ? 0 : $page + 1;

        if ($pages > 1) {
            echo $hesklang['pg'] . ': ';

            /* List pages */
            if ($pages >= 7) {
                if ($page > 2) {
                    echo '<a href="' . $href . '?' . $query . '1"><b>&laquo;</b></a> &nbsp; ';
                }

                if ($prev_page) {
                    echo '<a href="' . $href . '?' . $query . $prev_page . '"><b>&lsaquo;</b></a> &nbsp; ';
                }
            }

            for ($i = 1; $i <= $pages; $i++) {
                if ($i <= ($page + 5) && $i >= ($page - 5)) {
                    if ($i == $page) {
                        echo ' <b>' . $i . '</b> ';
                    } else {
                        echo ' <a href="' . $href . '?' . $query . $i . '">' . $i . '</a> ';
                    }
                }
            }

            if ($pages >= 7) {
                if ($next_page) {
                    echo ' &nbsp; <a href="' . $href . '?' . $query . $next_page . '"><b>&rsaquo;</b></a> ';
                }

                if ($page < ($pages - 1)) {
                    echo ' &nbsp; <a href="' . $href . '?' . $query . $pages . '"><b>&raquo;</b></a>';
                }
            }

            echo '<br />&nbsp;';

        } // end PAGES > 1

        // Get messages from the database
        $res = hesk_dbQuery("SELECT `id`, `from`, `to`, `subject`, `dt`, `read` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mail` WHERE `" . hesk_dbEscape($hesk_settings['mailtmp']['this']) . "`='" . intval($_SESSION['id']) . "' AND `deletedby`!='" . intval($_SESSION['id']) . "' ORDER BY `id` DESC LIMIT " . intval($limit_down) . " , " . intval($maxresults) . " ");
        ?>

        <form action="mail.php<?php if ($hesk_settings['mailtmp']['folder'] == 'outbox') {
            echo '?folder=outbox';
        } ?>" name="form1" method="post">

            <div align="center">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th><input type="checkbox" name="checkall" value="2" onclick="hesk_changeAll(this)"/></th>
                        <th><?php echo $hesklang['m_sub']; ?></th>
                        <th><?php echo $hesk_settings['mailtmp']['m_from']; ?></th>
                        <th><?php echo $hesklang['date_colon']; ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $i = 0;
                    while ($pm = hesk_dbFetchAssoc($res)) {
                        if ($i) {
                            $i = 0;
                        } else {
                            $i = 1;
                        }

                        $pm['subject'] = '<a href="mail.php?a=read&amp;id=' . $pm['id'] . '">' . $pm['subject'] . '</a>';
                        if ($hesk_settings['mailtmp']['this'] == 'to' && !$pm['read']) {
                            $pm['subject'] = '<b>' . $pm['subject'] . '</b>';
                        }
                        $pm['name'] = isset($admins[$pm[$hesk_settings['mailtmp']['other']]]) ? '<a href="mail.php?a=new&amp;id=' . $pm[$hesk_settings['mailtmp']['other']] . '">' . $admins[$pm[$hesk_settings['mailtmp']['other']]] . '</a>' : (($pm['from'] == 9999) ? '<a href="https://www.hesk.com" target="_blank">HESK.com</a>' : $hesklang['e_udel']);
                        $pm['dt'] = hesk_dateToString($pm['dt'], 0, 0, 0, true)
                    ?>
                    <tr>
                        <td><input type="checkbox" name="id[]" value="<?php echo $pm['id']; ?>" />&nbsp;</td>
                        <td><?php echo $pm['subject']; ?></td>
                        <td><?php echo $pm['name']; ?></td>
                        <td><?php echo $pm['dt']; ?></td>
                    </tr>
                    <?php
                    } // End while
                    ?>
                </table>
            </div>

            <div class="form-group">
                <div class="col-sm-6">
                    <select class="form-control" name="a">
                        <?php
                        if ($hesk_settings['mailtmp']['this'] == 'to') {
                            ?>
                            <option value="mark_read" selected="selected"><?php echo $hesklang['mo1']; ?></option>
                            <option value="mark_unread"><?php echo $hesklang['mo2']; ?></option>
                            <?php
                        }
                        ?>
                        <option value="delete"><?php echo $hesklang['mo3']; ?></option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>"/>
                    <input type="submit" value="<?php echo $hesklang['execute']; ?>"
                           onclick="Javascript:if (document.form1.a.value=='delete') return hesk_confirmExecute('<?php echo hesk_makeJsString($hesklang['mo3']); ?>?');"
                           class="btn btn-default"/>
                </div>
            </div>

        </form>

        <p>&nbsp;</p>
        <?php

    } // END if total > 0
    else {
        echo '<i>' . $hesklang['npm'] . '</i> <p>&nbsp;</p>';
    }

} // END mail_list_messages()


function show_new_form()
{
global $hesk_settings, $hesklang, $admins;
?>

<form action="mail.php" method="post" name="form2" class="form-horizontal" role="form" data-toggle="validator">
    <div class="box">
        <div class="box-header with-border">
            <h1 class="box-title">
                <?php echo $hesklang['new_mail']; ?>
            </h1>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="form-group">
                <label for="to" class="col-sm-3 control-label"><?php echo $hesklang['m_to']; ?></label>

                <div class="col-sm-9">
                    <select class="form-control" name="to" type="number"
                            data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>" required>
                        <option value="" selected="selected"><?php echo $hesklang['select']; ?></option>
                        <?php
                        foreach ($admins as $k => $v) {
                            if ($k != $_SESSION['id']) {
                                if (isset($_SESSION['mail']) && $k == $_SESSION['mail']['to']) {
                                    echo '<option value="' . $k . '" selected="selected">' . $v . '</option>';
                                } else {
                                    echo '<option value="' . $k . '">' . $v . '</option>';
                                }
                            }
                        }
                        ?>
                    </select>
                    <div class="help-block with-errors"></div>
                </div>
            </div>
            <div class="form-group">
                <label for="subject" class="col-sm-3 control-label"><?php echo $hesklang['m_sub']; ?></label>

                <div class="col-sm-9">
                    <input type="text" class="form-control" placeholder="<?php echo htmlspecialchars($hesklang['subject']); ?>"
                           name="subject" size="40" maxlength="50"
                        <?php
                        if (isset($_SESSION['mail']['subject'])) {
                            echo ' value="' . stripslashes($_SESSION['mail']['subject']) . '" ';
                        }
                        ?> data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>" required>
                    <div class="help-block with-errors"></div>
                </div>
            </div>
            <div class="form-group">
                <label for="message" class="col-sm-3 control-label"><?php echo $hesklang['message']; ?>:</label>

                <div class="col-sm-9">
            <textarea name="message" class="form-control" data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                      placeholder="<?php echo htmlspecialchars($hesklang['message']); ?>" rows="15" cols="70" required><?php
                if (isset($_SESSION['mail']['message'])) {
                    echo stripslashes($_SESSION['mail']['message']);
                }
                ?></textarea>
                    <div class="help-block with-errors"></div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-9 col-sm-offset-3">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="signature" value="1" checked>
                            <?php echo $hesklang['attach_sign']; ?>
                        </label> (<a href="profile.php"><?php echo $hesklang['profile_settings']; ?></a>)
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-9 col-sm-offset-3">
                    <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>"/>
                    <input type="hidden" name="a" value="send"/>
                    <input type="submit" value="<?php echo $hesklang['m_send']; ?>" class="btn btn-default"/>
                </div>
            </div>
        </div>
    </div>
</form>
    <?php
    } // END show_new_form()
    ?>
