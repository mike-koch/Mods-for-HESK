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
define('PAGE_TITLE', 'ADMIN_CANNED');
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
define('WYSIWYG', 1);

/* Check permissions for this feature */
hesk_checkPermission('can_man_canned');

// Load custom fields
require_once(HESK_PATH . 'inc/custom_fields.inc.php');

/* What should we do? */
if ($action = hesk_REQUEST('a')) {
    if (defined('HESK_DEMO')) {
        hesk_process_messages($hesklang['ddemo'], 'manage_canned.php', 'NOTICE');
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

$modsForHesk_settings = mfh_getSettings();

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>

<script language="javascript" type="text/javascript"><!--
    function confirm_delete() {
        if (confirm('<?php echo hesk_makeJsString($hesklang['delete_saved']); ?>')) {
            return true;
        }
        else {
            return false;
        }
    }

    function hesk_insertTag(tag) {
        var text_to_insert = '%%' + tag + '%%';
        var msg = '';
        <?php
        if ($modsForHesk_settings['rich_text_for_tickets']) { ?>
        msg = tinymce.get("message").getContent();
        tinymce.get("message").setContent('');
        tinymce.get("message").execCommand('mceInsertRawHTML', false, msg + text_to_insert);
        <?php } else { ?>
        msg = document.getElementById('message').value;
        document.getElementById('message').value = msg + text_to_insert;
        <?php }
        ?>
        document.form1.msg.focus();
    }

    function hesk_insertAtCursor(myField, myValue) {
        if (document.selection) {
            myField.focus();
            sel = document.selection.createRange();
            sel.text = myValue;
        } else if (myField.selectionStart || myField.selectionStart == '0') {
            var startPos = myField.selectionStart;
            var endPos = myField.selectionEnd;
            myField.value = myField.value.substring(0, startPos)
                + myValue
                + myField.value.substring(endPos, myField.value.length);
        } else {
            myField.value += myValue;
        }
    }
    //-->
</script>

<?php
$result = hesk_dbQuery('SELECT * FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'std_replies` ORDER BY `reply_order` ASC');
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
                <?php echo $hesklang['savedResponses']; ?>
            </h1>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <?php if ($num < 1)
            {
                echo '<p>' . $hesklang['no_saved'] . '</p>';
            }
            else
            { ?>
            <table class="table table-hover">
                <tr>
                    <th><?php echo $hesklang['saved_title']; ?></th>
                    <th><?php echo $hesklang['opt']; ?></th>
                </tr>
                <?php

                while ($mysaved = hesk_dbFetchAssoc($result)) {
                    $j++;

                    if (isset($_SESSION['canned']['selcat2']) && $mysaved['id'] == $_SESSION['canned']['selcat2']) {
                        $color = 'admin_green';
                        unset($_SESSION['canned']['selcat2']);
                    } else {
                        $color = $i ? 'admin_white' : 'admin_gray';
                    }

                    $tmp = $i ? 'White' : 'Blue';
                    $style = 'class="option' . $tmp . 'OFF" onmouseover="this.className=\'option' . $tmp . 'ON\'" onmouseout="this.className=\'option' . $tmp . 'OFF\'"';
                    $i = $i ? 0 : 1;

                    $options .= '<option value="' . $mysaved['id'] . '"';
                    $options .= (isset($_SESSION['canned']['id']) && $_SESSION['canned']['id'] == $mysaved['id']) ? ' selected="selected" ' : '';
                    $options .= '>' . $mysaved['title'] . '</option>';


                    $javascript_titles .= 'myTitle[' . $mysaved['id'] . ']=\'' . addslashes($mysaved['title']) . "';\n";
                    if ($modsForHesk_settings['rich_text_for_tickets']) {
                        $theMessage = hesk_html_entity_decode($mysaved['message']);
                        $theMessage = addslashes($theMessage);
                        $javascript_messages .= 'myMsgTxt[' . $mysaved['id'] . ']=\'' . str_replace("\r\n", "\\r\\n' + \r\n'", $theMessage) . "';\n";
                    } else {
                        $javascript_messages .= 'myMsgTxt[' . $mysaved['id'] . ']=\'' . str_replace("\r\n", "\\r\\n' + \r\n'", addslashes($mysaved['message'])) . "';\n";
                    }

                    echo '
                        <tr>
                        <td>' . $mysaved['title'] . '</td>
                        <td>
                        ';

                    if ($num > 1) {
                        if ($j == 1) {
                            echo '<img src="../img/blank.gif" width="16" height="16" alt="" style="padding:3px;border:none;" /> <a href="manage_canned.php?a=order&amp;replyid=' . $mysaved['id'] . '&amp;move=15&amp;token=' . hesk_token_echo(0) . '"><i class="fa fa-fw fa-arrow-down icon-link green" data-toggle="tooltip" data-placement="top" title="' . $hesklang['move_dn'] . '"></i></a>';
                        } elseif ($j == $num) {
                            echo '<a href="manage_canned.php?a=order&amp;replyid=' . $mysaved['id'] . '&amp;move=-15&amp;token=' . hesk_token_echo(0) . '"><i class="fa fa-arrow-up icon-link green" data-toggle="tooltip" data-placement="top" title="' . $hesklang['move_up'] . '"></i></a> <img src="../img/blank.gif" width="16" height="16" alt="" style="padding:3px;border:none;" />';
                        } else {
                            echo '
                                <a href="manage_canned.php?a=order&amp;replyid=' . $mysaved['id'] . '&amp;move=-15&amp;token=' . hesk_token_echo(0) . '"><i class="fa fa-arrow-up icon-link green" data-toggle="tooltip" data-placement="top" title="' . $hesklang['move_up'] . '"></i></a>
                                <a href="manage_canned.php?a=order&amp;replyid=' . $mysaved['id'] . '&amp;move=15&amp;token=' . hesk_token_echo(0) . '"><i class="fa fa-arrow-down icon-link green" data-toggle="tooltip" data-placement="top" title="' . $hesklang['move_dn'] . '"></i></a>
                                ';
                        }
                    } else {
                        echo '';
                    }

                    echo '
                        <a href="manage_canned.php?a=remove&amp;id=' . $mysaved['id'] . '&amp;token=' . hesk_token_echo(0) . '" onclick="return confirm_delete();"><i class="fa fa-times icon-link red" data-toggle="tooltip" data-placement="top" title="' . $hesklang['delete'] . '"></i></a>&nbsp;</td>
                        </tr>
                        ';
                } // End while
            }
            ?>
            </table>
        </div>
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
    <div class="box">
        <div class="box-header with-border">
            <h1 class="box-title">
                <?php echo $hesklang['manage_saved']; ?> <a href="javascript:void(0)"
                                                            onclick="javascript:alert('<?php echo hesk_makeJsString($hesklang['manage_intro']); ?>')"><i
                        class="fa fa-question-circle settingsquestionmark"></i></a>
            </h1>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
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
            /* This will handle error, success and notice messages */
            hesk_handle_messages();
            ?>
            <?php
            $onsubmit = '';
            if ($modsForHesk_settings['rich_text_for_tickets']) {
                $onsubmit = 'onsubmit="return validateRichText(\'message-help-block\', \'message-group\', \'message\', \''.htmlspecialchars($hesklang['this_field_is_required']).'\')"';
            }
            ?>
            <form action="manage_canned.php" method="post" name="form1" class="form-horizontal" role="form" data-toggle="validator" <?php echo $onsubmit; ?>>
                <div class="form-group">
                    <div class="col-sm-12">
                        <?php
                        if ($num > 0) {
                            ?>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="radio">
                                        <label><input type="radio" name="a"
                                                      value="new" <?php echo (!isset($_SESSION['canned']['what']) || $_SESSION['canned']['what'] != 'EDIT') ? 'checked="checked"' : ''; ?> /> <?php echo $hesklang['canned_add']; ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="radio">
                                        <label><input type="radio" name="a"
                                                      value="edit" <?php echo (isset($_SESSION['canned']['what']) && $_SESSION['canned']['what'] == 'EDIT') ? 'checked="checked"' : ''; ?> /> <?php echo $hesklang['canned_edit']; ?>
                                        </label>:
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <select class="form-control" name="saved_replies" onchange="setMessage(this.value)">
                                        <option value="0"> - <?php echo $hesklang['select_empty']; ?>-
                                        </option><?php echo $options; ?></select>
                                </div>
                            </div>
                            <?php
                        } else {
                            echo '<input type="hidden" name="a" value="new" /><label> ' . $hesklang['canned_add'] . '</label>';
                        }
                        ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="name" class="col-sm-2 control-label"><?php echo $hesklang['saved_title']; ?></label>

                    <div class="col-sm-10">
                    <span id="HeskTitle"><input id="subject" class="form-control"
                                                placeholder="<?php echo htmlspecialchars($hesklang['saved_title']); ?>"
                                                type="text" name="name" size="40"
                                                data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                                maxlength="50" <?php if (isset($_SESSION['canned']['name'])) {
                            echo ' value="' . stripslashes($_SESSION['canned']['name']) . '" ';
                        } ?> required></span>
                        <div class="help-block with-errors"></div>
                    </div>
                </div>
                <div class="form-group" id="message-group">
                    <label for="msg" class="col-sm-2 control-label"><?php echo $hesklang['message']; ?></label>

                    <div class="col-sm-10">
                    <span id="HeskMsg">
                        <textarea id="message" class="htmlEditor form-control"
                                  placeholder="<?php echo htmlspecialchars($hesklang['message']); ?>" name="msg"
                                  data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                  rows="15" cols="70" required><?php
                            if (isset($_SESSION['canned']['msg'])) {
                                echo stripslashes($_SESSION['canned']['msg']);
                            }
                            ?></textarea>
                    </span>
                        <div class="help-block with-errors" id="message-help-block"></div>
                        <?php echo $hesklang['insert_special']; ?>:
                        <a href="javascript:void(0)"
                           onclick="hesk_insertTag('HESK_ID')"><?php echo $hesklang['seqid']; ?></a> |
                        <a href="javascript:void(0)"
                           onclick="hesk_insertTag('HESK_TRACK_ID')"><?php echo $hesklang['trackID']; ?></a> |
                        <a href="javascript:void(0)"
                           onclick="hesk_insertTag('HESK_NAME')"><?php echo $hesklang['name']; ?></a> |
                        <a href="javascript:void(0)"
                           onclick="hesk_insertTag('HESK_EMAIL')"><?php echo $hesklang['email']; ?></a> |
                        <a href="javascript:void(0)"
                           onclick="hesk_insertTag('HESK_OWNER')"><?php echo $hesklang['owner']; ?></a>
                        <?php
                        foreach ($hesk_settings['custom_fields'] as $k => $v) {
                            if ($v['use']) {
                                if ($modsForHesk_settings['custom_field_setting']) {
                                    $v['name'] = $hesklang[$v['name']];
                                }

                                echo '| <a href="javascript:void(0)" onclick="hesk_insertTag(\'HESK_' . $k . '\')">' . $v['name'] . '</a> ';
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="form-group text-center">
                    <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>"/>
                    <input type="submit" value="<?php echo $hesklang['save_changes']; ?>" class="btn btn-default"/>
                </div>
            </form>
        </div>
    </div>
</section>
</div>
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

    $id = intval(hesk_POST('saved_replies')) or $hesk_error_buffer .= '<li>' . $hesklang['selcan'] . '</li>';
    $savename = hesk_input(hesk_POST('name')) or $hesk_error_buffer .= '<li>' . $hesklang['ent_saved_title'] . '</li>';
    $msg = hesk_input(hesk_POST('msg')) or $hesk_error_buffer .= '<li>' . $hesklang['ent_saved_msg'] . '</li>';

    // Avoid problems with utf-8 newline chars in Javascript code, detect and remove them
    $msg = preg_replace('/\R/u', "\r\n", $msg);

    $_SESSION['canned']['what'] = 'EDIT';
    $_SESSION['canned']['id'] = $id;
    $_SESSION['canned']['name'] = $savename;
    $_SESSION['canned']['msg'] = $msg;

    /* Any errors? */
    if (strlen($hesk_error_buffer)) {
        $hesk_error_buffer = $hesklang['rfm'] . '<br /><br /><ul>' . $hesk_error_buffer . '</ul>';
        hesk_process_messages($hesk_error_buffer, 'manage_canned.php?saved_replies=' . $id);
    }

    $result = hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "std_replies` SET `title`='" . hesk_dbEscape($savename) . "',`message`='" . hesk_dbEscape($msg) . "' WHERE `id`='" . intval($id) . "'");

    unset($_SESSION['canned']['what']);
    unset($_SESSION['canned']['id']);
    unset($_SESSION['canned']['name']);
    unset($_SESSION['canned']['msg']);

    hesk_process_messages($hesklang['your_saved'], 'manage_canned.php?saved_replies=' . $id, 'SUCCESS');
} // End edit_saved()


function new_saved()
{
    global $hesk_settings, $hesklang;

    /* A security check */
    hesk_token_check('POST');

    $hesk_error_buffer = '';
    $savename = hesk_input(hesk_POST('name')) or $hesk_error_buffer .= '<li>' . $hesklang['ent_saved_title'] . '</li>';
    $msg = hesk_input(hesk_POST('msg')) or $hesk_error_buffer .= '<li>' . $hesklang['ent_saved_msg'] . '</li>';

    // Avoid problems with utf-8 newline chars in Javascript code, detect and remove them
    $msg = preg_replace('/\R/u', "\r\n", $msg);

    $_SESSION['canned']['what'] = 'NEW';
    $_SESSION['canned']['name'] = $savename;
    $_SESSION['canned']['msg'] = $msg;

    /* Any errors? */
    if (strlen($hesk_error_buffer)) {
        $hesk_error_buffer = $hesklang['rfm'] . '<br /><br /><ul>' . $hesk_error_buffer . '</ul>';
        hesk_process_messages($hesk_error_buffer, 'manage_canned.php');
    }

    /* Get the latest reply_order */
    $result = hesk_dbQuery('SELECT `reply_order` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'std_replies` ORDER BY `reply_order` DESC LIMIT 1');
    $row = hesk_dbFetchRow($result);
    $my_order = $row[0] + 10;

    hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "std_replies` (`title`,`message`,`reply_order`) VALUES ('" . hesk_dbEscape($savename) . "','" . hesk_dbEscape($msg) . "','" . intval($my_order) . "')");

    unset($_SESSION['canned']['what']);
    unset($_SESSION['canned']['name']);
    unset($_SESSION['canned']['msg']);

    hesk_process_messages($hesklang['your_saved'], 'manage_canned.php', 'SUCCESS');
} // End new_saved()


function remove()
{
    global $hesk_settings, $hesklang;

    /* A security check */
    hesk_token_check();

    $mysaved = intval(hesk_GET('id')) or hesk_error($hesklang['id_not_valid']);

    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "std_replies` WHERE `id`='" . intval($mysaved) . "'");
    if (hesk_dbAffectedRows() != 1) {
        hesk_error("$hesklang[int_error]: $hesklang[reply_not_found].");
    }

    hesk_process_messages($hesklang['saved_rem_full'], 'manage_canned.php', 'SUCCESS');
} // End remove()


function order_saved()
{
    global $hesk_settings, $hesklang;

    /* A security check */
    hesk_token_check();

    $replyid = intval(hesk_GET('replyid')) or hesk_error($hesklang['reply_move_id']);
    $_SESSION['canned']['selcat2'] = $replyid;

    $reply_move = intval(hesk_GET('move'));

    hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "std_replies` SET `reply_order`=`reply_order`+" . intval($reply_move) . " WHERE `id`='" . intval($replyid) . "'");
    if (hesk_dbAffectedRows() != 1) {
        hesk_error("$hesklang[int_error]: $hesklang[reply_not_found].");
    }

    /* Update all category fields with new order */
    $result = hesk_dbQuery('SELECT `id` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'std_replies` ORDER BY `reply_order` ASC');

    $i = 10;
    while ($myreply = hesk_dbFetchAssoc($result)) {
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "std_replies` SET `reply_order`=" . intval($i) . " WHERE `id`='" . intval($myreply['id']) . "'");
        $i += 10;
    }

    header('Location: manage_canned.php');
    exit();
} // End order_saved()

?>
