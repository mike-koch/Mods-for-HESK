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
define('VALIDATOR', 1);
define('PAGE_TITLE', 'ADMIN_TICKET_TPL');
define('MFH_PAGE_LAYOUT', 'TOP_ONLY');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/mail_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

$modsForHesk_settings = mfh_getSettings();

/* Check permissions for this feature */
hesk_checkPermission('can_man_ticket_tpl');

// Define required constants
define('LOAD_TABS', 1);

if ($modsForHesk_settings['rich_text_for_tickets']) {
    define('WYSIWYG', 1);
}

/* What should we do? */
if ($action = hesk_REQUEST('a')) {
    if (defined('HESK_DEMO')) {
        hesk_process_messages($hesklang['ddemo'], 'manage_ticket_templates.php', 'NOTICE');
    } elseif ($action == 'new') {
        new_saved();
    } elseif ($action == 'edit') {
        edit_saved();
    } elseif ($action == 'remove') {
        remove();
    } elseif ($action == 'order') {
        order_saved();
    }
}


/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>

<script language="javascript" type="text/javascript"><!--
    function confirm_delete() {
        if (confirm('<?php echo hesk_makeJsString($hesklang['delete_tpl']); ?>')) {
            return true;
        }
        else {
            return false;
        }
    }
    //-->
</script>

<?php
// Get canned responses from database
$result = hesk_dbQuery('SELECT * FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'ticket_templates` ORDER BY `tpl_order` ASC');
$options = '';
$javascript_messages = '';
$javascript_titles = '';

$i = 1;
$j = 0;
$num = hesk_dbNumRows($result);
?>
<div class="content-wrapper">
    <section class="content">
    <div class="box">
        <div class="box-header with-border">
            <h1 class="box-title">
                <?php echo $hesklang['saved_ticket_tpl']; ?>
            </h1>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <?php if ($num < 1) {
                echo '<p>' . $hesklang['no_ticket_tpl'] . '</p>';
            } else {
                ?>
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th><?php echo $hesklang['ticket_tpl_title']; ?></th>
                        <th><?php echo $hesklang['opt']; ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php

                    while ($mysaved = hesk_dbFetchAssoc($result)) {
                        $j++;
                        $color = '';
                        if (isset($_SESSION['canned']['selcat2']) && $mysaved['id'] == $_SESSION['canned']['selcat2']) {
                            $color = 'success';
                            unset($_SESSION['canned']['selcat2']);
                        }

                        $options .= '<option class="form-control" value="' . $mysaved['id'] . '"';
                        $options .= (isset($_SESSION['canned']['id']) && $_SESSION['canned']['id'] == $mysaved['id']) ? ' selected="selected" ' : '';
                        $options .= '>' . $mysaved['title'] . '</option>';

                        if ($modsForHesk_settings['rich_text_for_tickets']) {
                            $theMessage = html_entity_decode($mysaved['message']);
                            $theMessage = addslashes($theMessage);
                            $javascript_messages .= 'myMsgTxt[' . $mysaved['id'] . ']=\'' . str_replace("\r\n", "\\r\\n' + \r\n'", $theMessage) . "';\n";
                        } else {
                            $javascript_messages .= 'myMsgTxt[' . $mysaved['id'] . ']=\'' . str_replace("\r\n", "\\r\\n' + \r\n'", addslashes($mysaved['message'])) . "';\n";
                        }
                        $javascript_titles .= 'myTitle[' . $mysaved['id'] . ']=\'' . addslashes($mysaved['title']) . "';\n";

                        echo '
                                    <tr>
                                    <td>' . $mysaved['title'] . '</td>
                                    <td class="text-left">
                                    ';

                        if ($num > 1) {
                            if ($j == 1) {
                                echo '<img src="../img/blank.gif" width="16" height="16" alt="" style="padding:3px;border:none;" />
                                        <a href="manage_ticket_templates.php?a=order&amp;replyid=' . $mysaved['id'] . '&amp;move=15&amp;token=' . hesk_token_echo(0) . '">
                                            <i class="fa fa-arrow-down icon-link green" data-toggle="tooltip" data-placement="top" data-original-title="' . $hesklang['move_dn'] . '"></i></a>';
                            } elseif ($j == $num) {
                                echo '<a href="manage_ticket_templates.php?a=order&amp;replyid=' . $mysaved['id'] . '&amp;move=-15&amp;token=' . hesk_token_echo(0) . '"><i class="fa fa-arrow-up icon-link green" data-toggle="tooltip" data-placement="top" data-original-title="' . $hesklang['move_up'] . '"></i></a> <img src="../img/blank.gif" width="16" height="16" alt="" style="padding:3px;border:none;" />';
                            } else {
                                echo '
                                        <a href="manage_ticket_templates.php?a=order&amp;replyid=' . $mysaved['id'] . '&amp;move=-15&amp;token=' . hesk_token_echo(0) . '"><i class="fa fa-arrow-up icon-link green" data-toggle="tooltip" data-placement="top" data-original-title="' . $hesklang['move_up'] . '"></i></a>
                                        <a href="manage_ticket_templates.php?a=order&amp;replyid=' . $mysaved['id'] . '&amp;move=15&amp;token=' . hesk_token_echo(0) . '"><i class="fa fa-arrow-down icon-link green" data-toggle="tooltip" data-placement="top" data-original-title="' . $hesklang['move_dn'] . '"></i></a>
                                        ';
                            }
                        } else {
                            echo '';
                        }

                        echo '
                                    <a href="manage_ticket_templates.php?a=remove&amp;id=' . $mysaved['id'] . '&amp;token=' . hesk_token_echo(0) . '" onclick="return confirm_delete();"><i class="fa fa-times icon-link red" data-toggle="tooltip" data-placement="top" data-original-title="' . $hesklang['delete'] . '"></i></a></td>
                                    </tr>
                                    ';
                    } // End while

                    ?>
                    </tbody>
                </table>
                <?php
            }
            ?>
        </div>
    </div>
    <div class="box">
        <div class="box-header with-border">
            <h1 class="box-title">
                <?php echo $hesklang['new_ticket_tpl']; ?>
                <a href="javascript:void(0)"
                   onclick="javascript:alert('<?php echo hesk_makeJsString($hesklang['ticket_tpl_intro']); ?>')"><i
                        class="fa fa-question-circle settingsquestionmark"></i></a>
            </h1>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <?php
            /* This will handle error, success and notice messages */
            hesk_handle_messages();

            $onsubmit = '';
            if ($modsForHesk_settings['rich_text_for_tickets']) {
                $onsubmit = 'onsubmit="return validateRichText(\'message-help-block\', \'message-group\', \'message\', \''.htmlspecialchars($hesklang['this_field_is_required']).'\')"';
            }
            ?>
            <form class="form-horizontal" action="manage_ticket_templates.php" method="post" name="form1" role="form" data-toggle="validator" <?php echo $onsubmit; ?>>
                <?php
                if ($num > 0) {
                    ?>
                    <div class="form-group">
                        <div class="col-sm-12">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="a"
                                                   value="new" <?php echo (!isset($_SESSION['canned']['what']) || $_SESSION['canned']['what'] != 'EDIT') ? 'checked=' : ''; ?>>
                                            <?php echo $hesklang['ticket_tpl_add']; ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="a"
                                                   value="edit" <?php echo (isset($_SESSION['canned']['what']) && $_SESSION['canned']['what'] == 'EDIT') ? 'checked' : ''; ?>>
                                            <?php echo $hesklang['ticket_tpl_edit']; ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <select class="form-control" name="saved_replies" onchange="setMessage(this.value)">
                                        <option value="0"> - <?php echo $hesklang['select_empty']; ?>-
                                        </option><?php echo $options; ?></select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                } else {
                    echo '<p><input type="hidden" name="a" value="new" /> ' . $hesklang['ticket_tpl_add'] . '</label></p>';
                }
                ?>
                <div class="form-group">
                    <label for="name" class="col-sm-2 control-label"><?php echo $hesklang['ticket_tpl_title']; ?></label>

                    <div class="col-sm-10">
                    <span id="HeskTitle">
                        <input id="subject" class="form-control" type="text" name="name" size="40" maxlength="50"
                               data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                               placeholder="<?php echo htmlspecialchars($hesklang['ticket_tpl_title']); ?>"
                            <?php if (isset($_SESSION['canned']['name'])) {
                                echo ' value="' . stripslashes($_SESSION['canned']['name']) . '" ';
                            } ?> required>
                    </span>
                        <div class="help-block with-errors"></div>
                    </div>
                </div>
                <div class="form-group" id="message-group">
                    <label for="msg" class="col-sm-2 control-label"><?php echo $hesklang['message']; ?></label>

                    <div class="col-sm-10">
                    <span id="HeskMsg">
                        <textarea id="message" class="form-control htmlEditor"
                                  data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                  placeholder="<?php echo htmlspecialchars($hesklang['message']); ?>" name="msg"
                                  rows="15" cols="70" required><?php
                            if (isset($_SESSION['canned']['msg'])) {
                                if ($modsForHesk_settings['rich_text_for_tickets']) {
                                    echo $_SESSION['canned']['msg'];
                                } else {
                                    echo stripslashes($_SESSION['canned']['msg']);
                                }
                            }
                            ?></textarea>
                    </span>
                        <div class="help-block with-errors" id="message-help-block"></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-10 col-sm-offset-2">
                        <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>"/>
                        <input type="submit" value="<?php echo $hesklang['save_ticket_tpl']; ?>" class="btn btn-default">
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
</div>
<?php if ($modsForHesk_settings['rich_text_for_tickets']): ?>
    <script type="text/javascript">
        /* <![CDATA[ */
        tinyMCE.init({
            mode: "textareas",
            editor_selector: "htmlEditor",
            elements: "content",
            theme: "advanced",
            convert_urls: false,

            theme_advanced_buttons1: "cut,copy,paste,|,undo,redo,|,formatselect,fontselect,fontsizeselect,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull",
            theme_advanced_buttons2: "sub,sup,|,charmap,|,bullist,numlist,|,outdent,indent,insertdate,inserttime,preview,|,forecolor,backcolor,|,hr,removeformat,visualaid,|,link,unlink,anchor,image,cleanup,code",
            theme_advanced_buttons3: "",

            theme_advanced_toolbar_location: "top",
            theme_advanced_toolbar_align: "left",
            theme_advanced_statusbar_location: "bottom",
            theme_advanced_resizing: true
        });
        /* ]]> */
    </script>
<?php endif; ?>

<script language="javascript" type="text/javascript"><!--
    // -->
    var myMsgTxt = new Array();
    var myTitle = new Array();
    myMsgTxt[0] = '';
    myTitle[0] = '';

    <?php
    echo $javascript_titles;
    echo $javascript_messages;
    ?>

    function setMessage(msgid) {
        var useHtmlEditor = <?php echo $modsForHesk_settings['rich_text_for_tickets']; ?>;
        var myMsg = myMsgTxt[msgid];
        var mySubject = myTitle[msgid];

        if (myMsg == '') {
            if (useHtmlEditor) {
                tinymce.get("message").setContent('');
                tinymce.get("message").execCommand('mceInsertRawHTML', false, '');
            }
            else {
                $('#message').val('');
            }
            $('#subject').val('');
            return true;
        }
        if (document.getElementById) {
            if (useHtmlEditor) {
                tinymce.get("message").setContent('');
                tinymce.get("message").execCommand('mceInsertRawHTML', false, myMsg);
            } else {
                myMsg = $('<textarea />').html(myMsg).text();
                $('#message').val(myMsg).trigger('input');
            }
            mySubject = $('<textarea />').html(mySubject).text();
            $('#subject').val(mySubject).trigger('input');
        }
        else {
            document.form1.message.value = myMsg;
            document.form1.subject.value = mySubject;
        }

        if (msgid == 0) {
            document.form1.a[0].checked = true;
        } else {
            document.form1.a[1].checked = true;
        }

    }
    //-->
</script>

<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


/*** START FUNCTIONS ***/

function edit_saved()
{
    global $hesk_settings, $hesklang;

    /* A security check */
    hesk_token_check('POST');

    $hesk_error_buffer = '';

    $id = intval(hesk_POST('saved_replies')) or $hesk_error_buffer .= '<li>' . $hesklang['sel_ticket_tpl'] . '</li>';
    $savename = hesk_input(hesk_POST('name')) or $hesk_error_buffer .= '<li>' . $hesklang['ent_ticket_tpl_title'] . '</li>';
    $msg = hesk_input(hesk_POST('msg')) or $hesk_error_buffer .= '<li>' . $hesklang['ent_ticket_tpl_msg'] . '</li>';

    // Avoid problems with utf-8 newline chars in Javascript code, detect and remove them
    $msg = preg_replace('/\R/u', "\r\n", $msg);

    $_SESSION['canned']['what'] = 'EDIT';
    $_SESSION['canned']['id'] = $id;
    $_SESSION['canned']['name'] = $savename;
    $_SESSION['canned']['msg'] = $msg;

    /* Any errors? */
    if (strlen($hesk_error_buffer)) {
        $hesk_error_buffer = $hesklang['rfm'] . '<br /><br /><ul>' . $hesk_error_buffer . '</ul>';
        hesk_process_messages($hesk_error_buffer, 'manage_ticket_templates.php?saved_replies=' . $id);
    }

    $result = hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "ticket_templates` SET `title`='" . hesk_dbEscape($savename) . "',`message`='" . hesk_dbEscape($msg) . "' WHERE `id`='" . intval($id) . "'");

    unset($_SESSION['canned']['what']);
    unset($_SESSION['canned']['id']);
    unset($_SESSION['canned']['name']);
    unset($_SESSION['canned']['msg']);

    hesk_process_messages($hesklang['ticket_tpl_saved'], 'manage_ticket_templates.php?saved_replies=' . $id, 'SUCCESS');
} // End edit_saved()


function new_saved()
{
    global $hesk_settings, $hesklang;

    /* A security check */
    hesk_token_check('POST');

    $hesk_error_buffer = '';
    $savename = hesk_input(hesk_POST('name')) or $hesk_error_buffer .= '<li>' . $hesklang['ent_ticket_tpl_title'] . '</li>';
    $msg = hesk_input(hesk_POST('msg')) or $hesk_error_buffer .= '<li>' . $hesklang['ent_ticket_tpl_msg'] . '</li>';

    // Avoid problems with utf-8 newline chars in Javascript code, detect and remove them
    $msg = preg_replace('/\R/u', "\r\n", $msg);

    $_SESSION['canned']['what'] = 'NEW';
    $_SESSION['canned']['name'] = $savename;
    $_SESSION['canned']['msg'] = $msg;

    /* Any errors? */
    if (strlen($hesk_error_buffer)) {
        $hesk_error_buffer = $hesklang['rfm'] . '<br /><br /><ul>' . $hesk_error_buffer . '</ul>';
        hesk_process_messages($hesk_error_buffer, 'manage_ticket_templates.php');
    }

    /* Get the latest tpl_order */
    $result = hesk_dbQuery('SELECT `tpl_order` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'ticket_templates` ORDER BY `tpl_order` DESC LIMIT 1');
    $row = hesk_dbFetchRow($result);
    $my_order = $row[0] + 10;

    hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "ticket_templates` (`title`,`message`,`tpl_order`) VALUES ('" . hesk_dbEscape($savename) . "','" . hesk_dbEscape($msg) . "','" . intval($my_order) . "')");

    unset($_SESSION['canned']['what']);
    unset($_SESSION['canned']['name']);
    unset($_SESSION['canned']['msg']);

    hesk_process_messages($hesklang['ticket_tpl_saved'], 'manage_ticket_templates.php', 'SUCCESS');
} // End new_saved()


function remove()
{
    global $hesk_settings, $hesklang;

    /* A security check */
    hesk_token_check();

    $mysaved = intval(hesk_GET('id')) or hesk_error($hesklang['id_not_valid']);

    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "ticket_templates` WHERE `id`='" . intval($mysaved) . "'");
    if (hesk_dbAffectedRows() != 1) {
        hesk_error("$hesklang[int_error]: $hesklang[ticket_tpl_not_found].");
    }

    hesk_process_messages($hesklang['ticket_tpl_removed'], 'manage_ticket_templates.php', 'SUCCESS');
} // End remove()


function order_saved()
{
    global $hesk_settings, $hesklang;

    /* A security check */
    hesk_token_check();

    $tplid = intval(hesk_GET('replyid')) or hesk_error($hesklang['ticket_tpl_id']);
    $_SESSION['canned']['selcat2'] = $tplid;

    $tpl_move = intval(hesk_GET('move'));

    hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "ticket_templates` SET `tpl_order`=`tpl_order`+" . intval($tpl_move) . " WHERE `id`='" . intval($tplid) . "'");
    if (hesk_dbAffectedRows() != 1) {
        hesk_error("$hesklang[int_error]: $hesklang[ticket_tpl_not_found].");
    }

    /* Update all category fields with new order */
    $result = hesk_dbQuery('SELECT `id` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'ticket_templates` ORDER BY `tpl_order` ASC');

    $i = 10;
    while ($mytpl = hesk_dbFetchAssoc($result)) {
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "ticket_templates` SET `tpl_order`=" . intval($i) . " WHERE `id`='" . intval($mytpl['id']) . "'");
        $i += 10;
    }

    header('Location: manage_ticket_templates.php');
    exit();
} // End order_saved()

?>
