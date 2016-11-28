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
define('WYSIWYG', 1);
define('VALIDATOR', 1);
define('MFH_PAGE_LAYOUT', 'TOP_ONLY');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/mail_functions.inc.php');
require(HESK_PATH . 'inc/custom_fields.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

/* Check permissions for this feature */
if (!isset($_REQUEST['isManager']) || !$_REQUEST['isManager']) {
    hesk_checkPermission('can_view_tickets');
    hesk_checkPermission('can_edit_tickets');
}
$modsForHesk_settings = mfh_getSettings();

/* Ticket ID */
$trackingID = hesk_cleanID() or die($hesklang['int_error'] . ': ' . $hesklang['no_trackID']);

$is_reply = 0;
$tmpvar = array();

if (!isset($_SESSION['iserror'])) {
    $_SESSION['iserror'] = array();
}

/* Get ticket info */
$result = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `trackid`='" . hesk_dbEscape($trackingID) . "' LIMIT 1");
if (hesk_dbNumRows($result) != 1) {
    hesk_error($hesklang['ticket_not_found']);
}
$ticket = hesk_dbFetchAssoc($result);

// Demo mode
if (defined('HESK_DEMO')) {
    $ticket['email'] = 'hidden@demo.com';
}

/* Is this user allowed to view tickets inside this category? */
if (!isset($_REQUEST['isManager']) || !$_REQUEST['isManager']) {
    hesk_okCategory($ticket['category']);
}

if (hesk_isREQUEST('reply')) {
    $tmpvar['id'] = intval(hesk_REQUEST('reply')) or die($hesklang['id_not_valid']);

    $result = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` WHERE `id`='{$tmpvar['id']}' AND `replyto`='" . intval($ticket['id']) . "' LIMIT 1");
    if (hesk_dbNumRows($result) != 1) {
        hesk_error($hesklang['id_not_valid']);
    }
    $reply = hesk_dbFetchAssoc($result);
    $ticket['message'] = $reply['message'];
    $ticket['html'] = $reply['html'];
    $is_reply = 1;
}

if (isset($_POST['save'])) {
    /* A security check */
    hesk_token_check('POST');

    $hesk_error_buffer = array();

    if ($is_reply) {
        $tmpvar['message'] = hesk_input(hesk_POST('message')) or $hesk_error_buffer[] = $hesklang['enter_message'];

        if (count($hesk_error_buffer)) {
            $myerror = '<ul>';
            foreach ($hesk_error_buffer as $error) {
                $myerror .= "<li>$error</li>\n";
            }
            $myerror .= '</ul>';
            hesk_error($myerror);
        }

        if (!$modsForHesk_settings['rich_text_for_tickets']) {
            $tmpvar['message'] = hesk_makeURL($tmpvar['message']);
            $tmpvar['message'] = nl2br($tmpvar['message']);
        }

        $tmpvar['html'] = hesk_POST('html');

        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` SET `html`='" . $tmpvar['html'] . "', `message`='" . hesk_dbEscape($tmpvar['message']) . "' WHERE `id`='" . intval($tmpvar['id']) . "' AND `replyto`='" . intval($ticket['id']) . "'");
    } else {
        $tmpvar['language'] = hesk_POST('customerLanguage');
        $tmpvar['name'] = hesk_input(hesk_POST('name')) or $hesk_error_buffer[] = $hesklang['enter_your_name'];

        if ($hesk_settings['require_email']) {
            $tmpvar['email'] = hesk_validateEmail( hesk_POST('email'), 'ERR', 0) or $hesk_error_buffer['email']=$hesklang['enter_valid_email'];
        } else {
            $tmpvar['email'] = hesk_validateEmail( hesk_POST('email'), 'ERR', 0);

            // Not required, but must be valid if it is entered
            if ($tmpvar['email'] == '') {
                if (strlen(hesk_POST('email'))) {
                    $hesk_error_buffer['email'] = $hesklang['not_valid_email'];
                }
            }
        }

        $tmpvar['subject'] = hesk_input(hesk_POST('subject')) or $hesk_error_buffer[] = $hesklang['enter_ticket_subject'];
        $tmpvar['message'] = hesk_input( hesk_POST('message') );
        if ($hesk_settings['require_message'] == 1 && $tmpvar['message'] == '') {
            $hesk_error_buffer[] = $hesklang['enter_message'];
        }
        $tmpvar['html'] = hesk_POST('html');

        // Demo mode
        if (defined('HESK_DEMO')) {
            $tmpvar['email'] = 'hidden@demo.com';
        }

        // Custom fields
        foreach ($hesk_settings['custom_fields'] as $k=>$v) {
            if ($v['use'] && hesk_is_custom_field_in_category($k, $ticket['category'])) {
                if ($v['req'] == 2) {
                    $v['req'] = '<span class="important">*</span>';
                    $required_attribute = 'data-error="' . $hesklang['this_field_is_required'] . '" required';
                } else {
                    $v['req'] = '';
                    $required_attribute = '';
                }

                if ($v['type'] == 'checkbox') {
                    $tmpvar[$k]='';

                    if (isset($_POST[$k]) && is_array($_POST[$k])) {
                        foreach ($_POST[$k] as $myCB) {
                            $tmpvar[$k] .= ( is_array($myCB) ? '' : hesk_input($myCB) ) . '<br>';
                        }
                        $tmpvar[$k]=substr($tmpvar[$k],0,-6);
                    } else {
                        if ($v['req'] == 2) {
                            $hesk_error_buffer[$k]=$hesklang['fill_all'].': '.$v['name'];
                        }
                        $_POST[$k] = '';
                    }
                } elseif ($v['type'] == 'date') {
                    $tmpvar[$k] = hesk_POST($k);
                    $_SESSION["as_$k"] = '';

                    if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $tmpvar[$k])) {
                        $date = strtotime($tmpvar[$k] . ' t00:00:00');
                        $dmin = strlen($v['value']['dmin']) ? strtotime($v['value']['dmin'] . ' t00:00:00') : false;
                        $dmax = strlen($v['value']['dmax']) ? strtotime($v['value']['dmax'] . ' t00:00:00') : false;

                        $_SESSION["as_$k"] = $tmpvar[$k];

                        if ($dmin && $dmin > $date) {
                            $hesk_error_buffer[$k] = sprintf($hesklang['d_emin'], $v['name'], hesk_custom_date_display_format($dmin, $v['value']['date_format']));
                        } elseif ($dmax && $dmax < $date) {
                            $hesk_error_buffer[$k] = sprintf($hesklang['d_emax'], $v['name'], hesk_custom_date_display_format($dmax, $v['value']['date_format']));
                        } else {
                            $tmpvar[$k] = $date;
                        }
                    } else {
                        if ($v['req'] == 2) {
                            $hesk_error_buffer[$k]=$hesklang['fill_all'].': '.$v['name'];
                        }
                    }
                } elseif ($v['type'] == 'email') {
                    $tmp = $hesk_settings['multi_eml'];
                    $hesk_settings['multi_eml'] = $v['value']['multiple'];
                    $tmpvar[$k] = hesk_validateEmail( hesk_POST($k), 'ERR', 0);
                    $hesk_settings['multi_eml'] = $tmp;

                    if ($tmpvar[$k] != '') {
                        $_SESSION["as_$k"] = hesk_input($tmpvar[$k]);
                    } else {
                        $_SESSION["as_$k"] = '';

                        if ($v['req'] == 2) {
                            $hesk_error_buffer[$k] = $v['value']['multiple'] ? sprintf($hesklang['cf_noem'], $v['name']) : sprintf($hesklang['cf_noe'], $v['name']);
                        }
                    }
                } elseif ($v['req'] == 2) {
                    $tmpvar[$k]=hesk_makeURL(nl2br(hesk_input( hesk_POST($k) )));
                    if ($tmpvar[$k] == '') {
                        $hesk_error_buffer[$k]=$hesklang['fill_all'].': '.$v['name'];
                    }
                } else {
                    $tmpvar[$k]=hesk_makeURL(nl2br(hesk_input(hesk_POST($k))));
                }
            } else {
                $tmpvar[$k] = '';
            }
        }

        if (count($hesk_error_buffer)) {
            $myerror = '<ul>';
            foreach ($hesk_error_buffer as $error) {
                $myerror .= "<li>$error</li>\n";
            }
            $myerror .= '</ul>';
            hesk_error($myerror);
        }

        if (!$tmpvar['html']) {
            $tmpvar['message'] = hesk_makeURL($tmpvar['message']);
            $tmpvar['message'] = nl2br($tmpvar['message']);
        }

        $custom_SQL = '';
        for ($i = 1; $i <= 50; $i++) {
            $custom_SQL .= '`custom'.$i.'`=' . (isset($tmpvar['custom'.$i]) ? "'".hesk_dbEscape($tmpvar['custom'.$i])."'" : "''") . ',';
        }
        $custom_SQL = rtrim($custom_SQL, ',');

        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET
		`name`='" . hesk_dbEscape($tmpvar['name']) . "',
		`email`='" . hesk_dbEscape($tmpvar['email']) . "',
		`subject`='" . hesk_dbEscape($tmpvar['subject']) . "',
		`message`='" . hesk_dbEscape($tmpvar['message']) . "',
		`language`='" . hesk_dbEscape($tmpvar['language']) . "',
		`html`='" . hesk_dbEscape($tmpvar['html']) . "',
		$custom_SQL
		WHERE `id`='" . intval($ticket['id']) . "' LIMIT 1");
    }

    unset($tmpvar);
    hesk_cleanSessionVars('tmpvar');

    hesk_process_messages($hesklang['edt2'], 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999), 'SUCCESS');
}

$ticket['message'] = hesk_msgToPlain($ticket['message'], 0, 0);

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print admin navigation */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>
<div class="content-wrapper">
    <ol class="breadcrumb">
        <li>
            <a href="admin_ticket.php?track=<?php echo $trackingID; ?>&amp;Refresh=<?php echo mt_rand(10000, 99999); ?>"><?php echo $hesklang['ticket'] . ' ' . $trackingID; ?></a>
        </li>
        <li class="active"><?php echo $hesklang['edtt']; ?></li>
    </ol>
    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h1 class="box-title">
                    <?php echo $hesklang['edtt']; ?>
                </h1>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <?php
                $onsubmit = '';
                if ($modsForHesk_settings['rich_text_for_tickets']) {
                    $onsubmit = 'onsubmit="return validateRichText(\'message-help-block\', \'message-group\', \'message\', \''.htmlspecialchars($hesklang['this_field_is_required']).'\')"';
                }
                ?>
                <form role="form" class="form-horizontal" method="post" action="edit_post.php" name="form1" <?php echo $onsubmit; ?>>
                    <?php
                    /* If it's not a reply edit all the fields */
                    if (!$is_reply) {
                        if ($hesk_settings['can_sel_lang']) {
                            ?>
                            <div class="form-group">
                                <label for="customerLanguage"
                                       class="col-sm-3 control-label"><?php echo $hesklang['chol']; ?></label>

                                <div class="col-sm-9">
                                    <select name="customerLanguage" id="customerLanguage" class="form-control">
                                        <?php hesk_listLanguages(); ?>
                                    </select>
                                </div>
                            </div>
                        <?php } else {
                            echo '<input type="hidden" name="customerLanguage" value="' . $ticket['language'] . '">';
                        } ?>
                        <div class="form-group">
                            <?php
                            $required = '';
                            $required_attribute = '';
                            if ($hesk_settings['require_subject'] == 1) {
                                $required = ' <span class="important">*</span>';
                                $required_attribute = 'data-error="' . $hesklang['this_field_is_required'] . '" required';
                            }
                            ?>
                            <label for="subject" class="col-sm-3 control-label"><?php echo $hesklang['subject'] . $required; ?></label>

                            <div class="col-sm-9">
                                <input class="form-control" type="text" name="subject" size="40" maxlength="40"
                                       value="<?php echo $ticket['subject']; ?>"
                                       placeholder="<?php echo htmlspecialchars($hesklang['subject']); ?>"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name" class="col-sm-3 control-label">
                                <?php echo $hesklang['name']; ?>
                                <span class="important">*</span>
                            </label>

                            <div class="col-sm-9">
                                <input class="form-control" type="text" name="name" size="40" maxlength="30"
                                       value="<?php echo $ticket['name']; ?>"
                                       placeholder="<?php echo htmlspecialchars($hesklang['name']); ?>"
                                       data-error="<?php echo $hesklang['this_field_is_required']; ?>"
                                       required>
                            </div>
                        </div>
                        <div class="form-group">
                            <?php
                            $required = '';
                            $required_attribute = '';
                            if ($hesk_settings['require_email']) {
                                $required = ' <span class="important">*</span>';
                                $required_attribute = 'data-error="' . $hesklang['this_field_is_required'] . '" required';
                            }
                            ?>
                            <label for="email"
                                   class="col-sm-3 control-label"><?php echo $hesklang['email'] . $required; ?></label>

                            <div class="col-sm-9">
                                <input class="form-control" type="text" name="email" size="40" maxlength="1000"
                                       value="<?php echo $ticket['email']; ?>"
                                       placeholder="<?php echo htmlspecialchars($hesklang['email']); ?>"
                                    <?php echo $required_attribute ?>>
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>
                        <?php
                        foreach ($hesk_settings['custom_fields'] as $k => $v) {
                            if ($v['use'] && hesk_is_custom_field_in_category($k, $ticket['category'])) {
                                $k_value = $ticket[$k];

                                if ($v['type'] == 'checkbox') {
                                    $k_value = explode('<br>', $k_value);
                                }

                                if ($v['req'] == 2) {
                                    $v['req'] = '<span class="important">*</span>';
                                    $required_attribute = 'data-error="' . $hesklang['this_field_is_required'] . '" required';
                                } else {
                                    $v['req'] = '';
                                    $required_attribute = '';
                                }

                                switch ($v['type']) {
                                    /* Radio box */
                                    case 'radio':
                                        $cls = in_array($k, $_SESSION['iserror']) ? ' isError' : '';
                                        echo '
                        <div class="form-group' . $cls . '">
                            <label for="' . $k . '" class="col-sm-3 control-label">' . $v['name'] . ' ' . $v['req'] . '</label>
                            <div class="col-sm-9">';
                                        foreach ($v['value']['radio_options'] as $option) {
                                            if (strlen($k_value) == 0) {
                                                $k_value = $option;
                                                $checked = empty($v['value']['no_default']) ? 'checked="checked"' : '';
                                            } elseif ($k_value == $option) {
                                                $k_value = $option;
                                                $checked = 'checked="checked"';
                                            } else {
                                                $checked = '';
                                            }

                                            echo '<div class="radio"><label><input type="radio" name="' . $k . '" value="' . $option . '" ' . $checked . ' ' . $required_attribute . '> ' . $option . '</label></div>';
                                        }
                                        echo '<div class="help-block with-errors"></div></div>
                        </div>';

                                        break;

                                    /* Select drop-down box */
                                    case 'select':

                                        $cls = in_array($k, $_SESSION['iserror']) ? ' isError' : '';

                                        echo '
                        <div class="form-group">
                            <label for="' . $k . '" class="col-sm-3 control-label">' . $v['name'] . ' ' . $v['req'] . '</label>
                            <div class="col-sm-9">
                                <select name="' . $k . '" class="form-control" ' . $required_attribute . '>';
                                        // Show "Click to select"?
                                        if (!empty($v['value']['show_select'])) {
                                            echo '<option value="">' . $hesklang['select'] . '</option>';
                                        }

                                        foreach ($v['value']['select_options'] as $option) {
                                            if ($k_value == $option) {
                                                $k_value = $option;
                                                $selected = 'selected';
                                            } else {
                                                $selected = '';
                                            }

                                            echo '<option ' . $selected . '>' . $option . '</option>';
                                        }

                                        echo '</select>
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>';
                                        break;

                                    /* Checkbox */
                                    case 'checkbox':
                                        $cls = in_array($k, $_SESSION['iserror']) ? ' isError' : '';
                                        echo '
                        <div class="form-group' . $cls . '">
                            <label for="' . $k . '" class="col-sm-3 control-label">' . $v['name'] . ' ' . $v['req'] . '</label>
                            <div class="col-sm-9">';
                                        foreach ($v['value']['checkbox_options'] as $option) {
                                            if (in_array($option, $k_value)) {
                                                $checked = 'checked';
                                            } else {
                                                $checked = '';
                                            }

                                            echo '<div class="checkbox"><label><input type="checkbox" name="' . $k . '[]" value="' . $option . '" ' . $checked . ' ' . $required_attribute . '> ' . $option . '</label></div>';
                                        }
                                        echo '<div class="help-block with-errors"></div>
                            </div>
                        </div>';
                                        break;

                                    /* Large text box */
                                    case 'textarea':
                                        $cls = in_array($k, $_SESSION['iserror']) ? ' isError' : '';
                                        $k_value = hesk_msgToPlain($k_value, 0, 0);

                                        echo '
                        <div class="form-group' . $cls . '">
                            <label for="' . $k . '" class="col-sm-3 control-label">' . $v['name'] . ' ' . $v['req'] . '</label>
                            <div class="col-sm-9">
                                <textarea name="' . $k . '" class="form-control" rows="' . intval($v['value']['rows']) . '" cols="' . intval($v['value']['cols']) . '" ' . $required_attribute . '>' . $k_value . '</textarea>
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>';
                                        break;

                                    // Date
                                    case 'date':
                                        if ($required_attribute !== '') {
                                            $required_attribute .= '  pattern="[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])"';
                                        }

                                        $cls = in_array($k, $_SESSION['iserror']) ? ' isError' : '';

                                        $k_value = hesk_custom_date_display_format($k_value, 'Y-m-d');

                                        echo '
                        <div class="form-group' . $cls . '">
                            <label for="' . $k . '" class="col-sm-3 control-label">' . $v['name'] . ' ' . $v['req'] . '</label>
                            <div class="col-sm-9">
                                <input type="text" name="' . $k . '" value="' . $k_value . '" class="datepicker form-control" size="10" ' . $required_attribute . '>
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>';
                                        break;

                                    // Email
                                    case 'email':
                                        $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                                        $suggest = $hesk_settings['detect_typos'] ? 'onblur="Javascript:hesk_suggestEmail(\'' . $k . '\', \'' . $k . '_suggestions\', 0, 1' . ($v['value']['multiple'] ? ',1' : '') . ')"' : '';

                                        echo '
                        <div class="form-group' . $cls . '">
                            <label for="' . $k . '" class="col-sm-3 control-label">' . $v['name'] . ' ' . $v['req'] . '</label>
                            <div class="col-sm-9">
                                <input class="form-control" type="text" name="' . $k . '" id="' . $k . '" value="' . $k_value . '" size="40" ' . $suggest . ' ' . $required_attribute . '>
                                <div class="help-block with-errors"></div>
                            </div>
                            <div id="' . $k . '_suggestions"></div>
                        </div>
                        ';
                                        break;

                                    // Hidden (same as text for staff)
                                    case 'hidden':
                                    case 'readonly':
                                    default:
                                        if (strlen($k_value) != 0) {
                                            $v['value']['default_value'] = $k_value;
                                        }

                                        $cls = in_array($k, $_SESSION['iserror']) ? ' isError' : '';

                                        echo '
                        <div class="form-group' . $cls . '">
                            <label for="' . $k . '" class="col-sm-3 control-label">' . $v['name'] . ' ' . $v['req'] . '</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="' . $k . '" size="40" maxlength="' . intval($v['value']['max_length']) . '" value="' . $v['value']['default_value'] . '" ' . $required_attribute . '>
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>
                        ';
                                }
                            }
                        }
                    } ?>
                        <div class="form-group" id="message-group">
                            <?php
                            $required = '';
                            $required_attribute = '';
                            if ($hesk_settings['require_message'] == 1) {
                                $required = ' <span class="important">*</span>';
                                $required_attribute = 'data-error="' . $hesklang['this_field_is_required'] . '" required';
                            }

                            ?>
                            <label for="message" class="col-sm-3 control-label"><?php echo $hesklang['message'] . $required; ?></label>

                            <div class="col-sm-9">
                                <?php
                                $message = $ticket['html'] ? hesk_html_entity_decode($ticket['message']) : $ticket['message'];
                                ?>
                                <textarea class="form-control htmlEditor" name="message" rows="12"
                                          placeholder="<?php echo htmlspecialchars($hesklang['message']); ?>"
                                          cols="60" <?php echo $required_attribute; ?>><?php echo $message; ?></textarea>
                                <div class="help-block with-errors" id="message-help-block"></div>
                            </div>
                        </div>
                <div class="form-group">
                    <input type="hidden" name="save" value="1">
                    <input type="hidden" name="track" value="<?php echo $trackingID; ?>">
                    <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>">
                    <?php
                    if ($is_reply) {
                        ?>
                        <input type="hidden" name="reply" value="<?php echo $tmpvar['id']; ?>">
                        <?php
                    }
                    ?>
                </div>
                <div class="form-group" style="text-align: center">
                    <?php
                    $html = $ticket['html'] ? 1 : 0;
                    ?>
                    <input type="hidden" name="html" value="<?php echo $html; ?>">
                    <input type="submit" value="<?php echo $hesklang['save_changes']; ?>" class="btn btn-default">
                    <?php if (isset($_REQUEST['isManager']) && $_REQUEST['isManager']): ?>
                        <input type="hidden" name="isManager" value="1">
                    <?php endif; ?>
                    <a class="btn btn-default" href="javascript:history.go(-1)"><?php echo $hesklang['back']; ?></a>
                </div>
            </form>
        </div>
    </div>
        <script>
            buildValidatorForTicketSubmission('form1', "<?php echo addslashes($hesklang['select_at_least_one_value']); ?>");
        </script>
    <?php if ($ticket['html']): ?>
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
    </section>
</div>
<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();