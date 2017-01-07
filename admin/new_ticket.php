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
define('MFH_PAGE_LAYOUT', 'TOP_ONLY');
define('WYSIWYG', 1);
define('VALIDATOR', 1);

// Auto-focus first empty or error field
define('AUTOFOCUS', true);

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/view_attachment_functions.inc.php');
require(HESK_PATH . 'inc/mail_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

// Load custom fields
require_once(HESK_PATH . 'inc/custom_fields.inc.php');

// Pre-populate fields
// Customer name
if (isset($_REQUEST['name'])) {
    $_SESSION['as_name'] = $_REQUEST['name'];
}

// Customer email address
if (isset($_REQUEST['email'])) {
    $_SESSION['as_email'] = $_REQUEST['email'];
    $_SESSION['as_email2'] = $_REQUEST['email'];
}

// Category ID
if (isset($_REQUEST['catid'])) {
    $_SESSION['as_category'] = intval($_REQUEST['catid']);
}
if (isset($_REQUEST['category'])) {
    $_SESSION['as_category'] = intval($_REQUEST['category']);
}

// Priority
if (isset($_REQUEST['priority'])) {
    $_SESSION['as_priority'] = intval($_REQUEST['priority']);
}

// Subject
if (isset($_REQUEST['subject'])) {
    $_SESSION['as_subject'] = $_REQUEST['subject'];
}

// Message
if (isset($_REQUEST['message'])) {
    $_SESSION['as_message'] = $_REQUEST['message'];
}

// Custom fields
foreach ($hesk_settings['custom_fields'] as $k => $v) {
    if ($v['use'] && isset($_REQUEST[$k])) {
        $_SESSION['as_' . $k] = $_REQUEST[$k];
    }
}

/* Varibles for coloring the fields in case of errors */
if (!isset($_SESSION['iserror'])) {
    $_SESSION['iserror'] = array();
}

if (!isset($_SESSION['isnotice'])) {
    $_SESSION['isnotice'] = array();
}

/* List of users */
$admins = array();
$result = hesk_dbQuery("SELECT `id`,`name`,`isadmin`,`categories`,`heskprivileges` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `active` = '1' ORDER BY `name` ASC");
while ($row = hesk_dbFetchAssoc($result)) {
    /* Is this an administrator? */
    if ($row['isadmin']) {
        $admins[$row['id']] = $row['name'];
        continue;
    }

    /* Not admin, is user allowed to view tickets? */
    if (strpos($row['heskprivileges'], 'can_view_tickets') !== false) {
        $admins[$row['id']] = $row['name'];
        continue;
    }
}

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print admin navigation */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');

// Get categories
$hesk_settings['categories'] = array();

if (hesk_checkPermission('can_submit_any_cat', 0)) {
    $res = hesk_dbQuery("SELECT `id`, `name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` ORDER BY `cat_order` ASC");
} else {
    $res = hesk_dbQuery("SELECT `id`, `name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` WHERE ".hesk_myCategories('id')." ORDER BY `cat_order` ASC");
}

while ($row = hesk_dbFetchAssoc($res)) {
    $hesk_settings['categories'][$row['id']] = $row['name'];
}

$number_of_categories = count($hesk_settings['categories']);

if ($number_of_categories == 0) {
    $category = 1;
} elseif ($number_of_categories == 1) {
    $category = current(array_keys($hesk_settings['categories']));
} else {
    $category = isset($_GET['catid']) ? hesk_REQUEST('catid'): hesk_REQUEST('category');

    // Force the customer to select a category?
    if (!isset($hesk_settings['categories'][$category])) {
        return print_select_category($number_of_categories);
    }
}


$showRs = hesk_dbQuery("SELECT `show` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` WHERE `id` = 5");
$show = hesk_dbFetchAssoc($showRs);
$show_quick_help = $show['show'];
?>
<div class="content-wrapper">
    <ol class="breadcrumb">
        <li><a href="admin_main.php"><?php echo $hesk_settings['hesk_title']; ?></a></li>
        <?php if ($number_of_categories > 1): ?>
            <li><a href="new_ticket.php"><?php echo $hesklang['nti2']; ?></a></li>
            <li class="active"><?php echo $hesk_settings['categories'][$category]; ?></li>
        <?php else: ?>
            <li class="active"><?php echo $hesklang['nti2']; ?></li>
        <?php endif; ?>
    </ol>
    <section class="content">
    <?php
    /* This will handle error, success and notice messages */
    hesk_handle_messages();

    if ($show_quick_help): ?>
    <div class="box">
        <div class="box-header with-border">
            <h1 class="box-title">
                <?php echo $hesklang['quick_help']; ?>
            </h1>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <p><?php echo $hesklang['nti3']; ?></p>
            <br>

            <p><?php echo $hesklang['req_marked_with']; ?> <span class="important">*</span></p>
        </div>
    </div>
    <?php endif; ?>
    <div class="box">
        <div class="box-header with-border">
            <h1 class="box-title">
                <?php echo $hesklang['nti2']; ?>
            </h1>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <!-- START FORM -->
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
            <?php endif;

            $onsubmit = '';
            if ($modsForHesk_settings['rich_text_for_tickets']) {
                $onsubmit = 'onsubmit="return validateRichText(\'message-help-block\', \'message-group\', \'message\', \''.htmlspecialchars($hesklang['this_field_is_required']).'\')"';
            }
            ?>
            <form role="form" class="form-horizontal" method="post" action="admin_submit_ticket.php" name="form1"
                  enctype="multipart/form-data" <?php echo $onsubmit; ?>>
                <?php if ($hesk_settings['can_sel_lang']) { ?>
                    <div class="form-group">
                        <label for="customerLanguage" class="col-sm-3 control-label"><?php echo $hesklang['chol']; ?>:&nbsp;<span
                                class="important">*</span></label>

                        <div class="col-sm-9">
                            <select name="customerLanguage" id="customerLanguage" class="form-control">
                                <?php hesk_listLanguages(); ?>
                            </select>
                        </div>
                    </div>
                <?php } ?>
                <!-- Contact info -->
                <?php
                $has_error = '';
                if (in_array('name', $_SESSION['iserror'])) {
                    $has_error = 'has-error';
                }?>
                <div class="form-group <?php echo $has_error; ?>">
                    <label for="name" class="col-sm-3 control-label"><?php echo $hesklang['name']; ?><span
                            class="important">*</span></label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="name" size="40" maxlength="30"
                               value="<?php if (isset($_SESSION['as_name'])) {
                                   echo stripslashes(hesk_input($_SESSION['as_name']));
                               } else if (isset($_GET['name'])) {
                                   echo hesk_GET('name');
                               } ?>"
                               data-error="<?php echo htmlspecialchars($hesklang['enter_your_name']); ?>"
                               placeholder="<?php echo htmlspecialchars($hesklang['name']); ?>" required>
                        <div class="help-block with-errors"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class="col-sm-3 control-label">
                        <?php
                        echo $hesklang['email'];
                        if ($hesk_settings['require_email']) {
                            echo '<span class="important">*</span>';
                        }
                        ?>
                    </label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="email" size="40" maxlength="1000" id="email"
                               value="<?php if (isset($_SESSION['as_email'])) {
                                   echo stripslashes(hesk_input($_SESSION['as_email']));
                               } else if (isset($_GET['email'])) {
                                   echo hesk_GET('email');
                               } ?>" <?php if ($hesk_settings['detect_typos']) {
                            echo ' onblur="Javascript:Javascript:hesk_suggestEmail(\'email\', \'email_suggestions\', 1, 1)"';
                        } ?>
                               placeholder="<?php echo htmlspecialchars($hesklang['email']); ?>"
                               onkeyup="disableIfEmpty('email','notify-email')"
                                <?php if ($hesk_settings['require_email']) {echo 'data-error="'.htmlspecialchars($hesklang['enter_valid_email']).'" required';} ?>>
                        <div class="help-block with-errors"></div>
                    </div>

                </div>
                <div id="email_suggestions"></div>
                <!-- Priority -->
                <?php
                $has_error = '';
                if (in_array('priority', $_SESSION['iserror'])) {
                    $has_error = 'has-error';
                } ?>
                <div class="form-group <?php echo $has_error; ?>">
                    <label for="priority" class="col-sm-3 control-label"><?php echo $hesklang['priority']; ?><span
                            class="important">*</span></label>
                    <div class="col-sm-9">
                        <select name="priority" class="form-control"
                                pattern="[0-9]+"
                                data-error="<?php echo htmlspecialchars($hesklang['sel_app_priority']); ?>"
                                required>
                            <?php
                            // Show the "Click to select"?
                            if ($hesk_settings['select_pri']) {
                                echo '<option value="">' . $hesklang['select'] . '</option>';
                            }
                            ?>
                            <option value="3" <?php
                            if ((isset($_SESSION['as_priority']) && $_SESSION['as_priority'] == 3)
                                || (isset($_GET['priority']) && $_GET['priority'] == 3)
                            ) {
                                echo 'selected="selected"';
                            } ?>><?php echo $hesklang['low']; ?></option>
                            <option value="2" <?php
                            if ((isset($_SESSION['as_priority']) && $_SESSION['as_priority'] == 2)
                                || (isset($_GET['priority']) && $_GET['priority'] == 2)
                            ) {
                                echo 'selected="selected"';
                            } ?>><?php echo $hesklang['medium']; ?></option>
                            <option value="1" <?php
                            if ((isset($_SESSION['as_priority']) && $_SESSION['as_priority'] == 1)
                                || (isset($_GET['priority']) && $_GET['priority'] == 1)
                            ) {
                                echo 'selected="selected"';
                            } ?>><?php echo $hesklang['high']; ?></option>
                            <option value="0" <?php
                            if ((isset($_SESSION['as_priority']) && $_SESSION['as_priority'] == 0)
                                || (isset($_GET['priority']) && $_GET['priority'] == 0)
                            ) {
                                echo 'selected="selected"';
                            } ?>><?php echo $hesklang['critical']; ?></option>
                        </select>
                        <div class="help-block with-errors"></div>
                    </div>
                </div>
                <?php
                /* custom fields BEFORE comments */
                foreach ($hesk_settings['custom_fields'] as $k => $v) {
                    if ($v['use'] && $v['place'] == 0 && hesk_is_custom_field_in_category($k, $category)) {
                        if ($v['req'] == 2) {
                            $v['req']=  '<span class="important">*</span>';
                            $required_attribute = 'data-error="' . $hesklang['this_field_is_required'] . '" required';
                        } else {
                            $v['req'] = '';
                            $required_attribute = '';
                        }

                        if ($v['type'] == 'checkbox') {
                            $k_value = array();
                            if (isset($_SESSION["as_$k"]) && is_array($_SESSION["as_$k"])) {
                                foreach ($_SESSION["as_$k"] as $myCB) {
                                    $k_value[] = stripslashes(hesk_input($myCB));
                                }
                            }
                        } elseif (isset($_SESSION["as_$k"])) {
                            $k_value  = stripslashes(hesk_input($_SESSION["as_$k"]));
                        } else {
                            $k_value  = '';
                        }

                        switch ($v['type']) {
                            /* Radio box */
                            case 'radio':
                                $cls = in_array($k, $_SESSION['iserror']) ? ' isError' : '';
                                echo '<div class="form-group' . $cls . '"><label class="col-sm-3 control-label">' . $v['name'] . ' ' . $v['req'] .'</label><div align="left" class="col-sm-9">';

                                foreach ($v['value']['radio_options'] as $option) {

                                    if (strlen($k_value) == 0) {
                                        $k_value = $option;
                                        $checked = empty($v['value']['no_default']) ? 'checked' : '';
                                    } elseif ($k_value == $option) {
                                        $k_value = $option;
                                        $checked = 'checked';
                                    } else {
                                        $checked = '';
                                    }

                                    echo '<div class="radio">
                                            <label>
                                                <input type="radio" name="' . $k . '" value="' . $option . '" ' . $checked . $required_attribute . '>
                                                ' . $option . '
                                            </label>
                                        </div>';
                                }

                                echo '
                                    <div class="help-block with-errors"></div>
                                    </div>
                                </div>';
                                break;

                            /* Select drop-down box */
                            case 'select':
                                $cls = in_array($k, $_SESSION['iserror']) ? ' isError' : '';

                                echo '<div class="form-group' . $cls . '"><label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . ' ' . $v['req'] . '</label>
                        <div class="col-sm-9"><select class="form-control" name="' . $k . '" ' . $required_attribute . '>';

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
                                    <div class="help-block with-errors"></div></div></div>';
                                break;

                            /* Checkbox */
                            case 'checkbox':
                                $cls = in_array($k, $_SESSION['iserror']) ? ' isError' : '';

                                $validator = $v['req'] == '<span class="important">*</span>' ? 'data-checkbox="' . $k . '"' : '';
                                $required_attribute = $validator == '' ? '' : ' data-error="' . $hesklang['this_field_is_required'] . '"';

                                echo '<div class="form-group' . $cls . '"><label class="col-sm-3 control-label">' . $v['name'] . ' ' . $v['req'] . '</label><div align="left" class="col-sm-9">';

                                foreach ($v['value']['checkbox_options'] as $option) {
                                    if (in_array($option, $k_value)) {
                                        $checked = 'checked';
                                    } else {
                                        $checked = '';
                                    }

                                    echo '<div class="checkbox"><label><input ' . $validator . ' type="checkbox" name="' . $k . '[]" value="' . $option . '" ' . $checked . $required_attribute . '> ' . $option . '</label></div>';
                                }
                                echo '
                                    <div class="help-block with-errors"></div></div></div>';
                                break;

                            /* Large text box */
                            case 'textarea':
                                $cls = in_array($k, $_SESSION['iserror']) ? ' isError' : '';

                                echo '<div class="form-group' . $cls . '">
                        <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . ' ' . $v['req'] . '</label>
                        <div class="col-sm-9"><textarea class="form-control" placeholder="' . $v['name'] . '" name="' . $k . '" rows="' . intval($v['value']['rows']) . '" cols="' . intval($v['value']['cols']) . '" ' . $required_attribute . '>' . $k_value . '</textarea>
                                    <div class="help-block with-errors"></div></div></div>';
                                break;

                            case 'date':
                                if ($required_attribute != '') {
                                    $required_attribute .= ' pattern="[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])"';
                                }

                                $cls = in_array($k, $_SESSION['iserror']) ? ' isError' : '';

                                echo '
                        <div class="form-group' . $cls . '">
                            <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'].' '.$v['req'] . '</label>
                            <div class="col-sm-9">
                                <input type="text" class="datepicker form-control" placeholder="' . $v['name'] . '" name="' . $k . '" size="40"
                                    value="' . $k_value . '" ' . $required_attribute . '>
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>';
                                break;
                            case 'email':
                                $suggest = $hesk_settings['detect_typos'] ? 'onblur="Javascript:hesk_suggestEmail(\''.$k.'\', \''.$k.'_suggestions\', 0, 1'.($v['value']['multiple'] ? ',1' : '').')"' : '';

                                $cls = in_array($k, $_SESSION['iserror']) ? ' isError' : '';

                                echo '<div class="form-group' . $cls . '">
                        <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'].' '.$v['req'] . '</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" placeholder="' . $v['name'] . '" name="' . $k . '" size="40" value="' . $k_value . '" '.$suggest.$required_attribute.'>
                            <div class="help-block with-errors"></div>
                        </div>
                        </div><div id="'.$k.'_suggestions"></div>';

                                break;

                            // Hidden and read-only should work the same as text
                            case 'hidden':
                            case 'readonly':
                            default:
                                if (strlen($k_value) != 0 || isset($_SESSION["as_$k"])) {
                                    $v['value']['default_value'] = $k_value;
                                }

                                $cls = in_array($k, $_SESSION['iserror']) ? ' isError' : '';

                                echo '<div class="form-group' . $cls . '">
                        <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'].' '.$v['req'] . '</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" placeholder="' . $v['name'] . '" name="' . $k . '" size="40" maxlength="' . intval($v['value']['max_length']) . '" value="' . $v['value']['default_value'] . '" ' . $cls . $required_attribute . '>
                            <div class="help-block with-errors"></div>
                        </div>
                        </div>';
                        }
                    }
                }

                // Lets handle ticket templates
                $can_options = '';

                // Get ticket templates from the database
                $res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "ticket_templates` ORDER BY `tpl_order` ASC");

                // If we have any templates print them out
                if (hesk_dbNumRows($res)) {
                    ?>
                    <script language="javascript" type="text/javascript"><!--
                        // -->
                        var myMsgTxt = new Array();
                        var mySubjectTxt = new Array();
                        myMsgTxt[0] = '';
                        mySubjectTxt[0] = '';

                        <?php
                        while ($mysaved = hesk_dbFetchRow($res))
                        {
                            $can_options .= '<option value="' . $mysaved[0] . '">' . $mysaved[1]. "</option>\n";
                            if ($modsForHesk_settings['rich_text_for_tickets']) {
                                $theMessage = hesk_html_entity_decode($mysaved[2]);
                                $theMessage = addslashes($theMessage);
                                echo 'myMsgTxt['.$mysaved[0].']=\''.str_replace("\r\n","\\r\\n' + \r\n'", $theMessage)."';\n";
                            } else {
                                echo 'myMsgTxt['.$mysaved[0].']=\''.str_replace("\r\n","\\r\\n' + \r\n'", addslashes($mysaved[2]))."';\n";
                            }
                            echo 'mySubjectTxt['.$mysaved[0].']=\''.str_replace("\r\n","\\r\\n' + \r\n'", addslashes($mysaved[1]))."';\n";
                        }

                        ?>

                        function setMessage(msgid) {
                            var useHtmlEditor = <?php echo $modsForHesk_settings['rich_text_for_tickets']; ?>;
                            var myMsg = myMsgTxt[msgid];
                            var mySubject = mySubjectTxt[msgid];

                            if (myMsg == '') {
                                if (document.form1.mode[1].checked) {
                                    if (useHtmlEditor) {
                                        tinymce.get("message").setContent('');
                                        tinymce.get("message").execCommand('mceInsertRawHTML', false, '');
                                    }
                                    else {
                                        $('#message').val('');
                                    }
                                    $('#subject').val('');
                                }
                                return true;
                            }
                            if (document.getElementById) {
                                if (document.getElementById('moderep').checked) {
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
                                    if (useHtmlEditor) {
                                        var oldMsg = tinymce.get("message").getContent();
                                        tinymce.get("message").setContent('');
                                        tinymce.get("message").execCommand('mceInsertRawHTML', false, oldMsg + myMsg);
                                    } else {
                                        var oldMsg = document.getElementById('message').value;
                                        var theMsg = $('<textarea />').html(oldMsg + myMsg).text();
                                        $('#message').val(theMsg).trigger('input');
                                    }
                                    if (document.getElementById('subject').value == '') {
                                        mySubject = $('<textarea />').html(mySubject).text();
                                        $('#subject').val(mySubject).trigger('input');
                                    }
                                }
                            }
                            else {
                                if (document.form1.mode[0].checked) {
                                    document.form1.message.value = myMsg;
                                    document.form1.subject.value = mySubject;
                                }
                                else {
                                    var oldMsg = document.form1.message.value;
                                    document.form1.message.value = oldMsg + myMsg;
                                    if (document.form1.subject.value == '') {
                                        document.form1.subject.value = mySubject;
                                    }
                                }
                            }

                        }
                        //-->
                    </script>
                    <?php
                } // END fetchrows

                // Print templates
                if (strlen($can_options)) {
                    ?>
                    <div class="form-group">
                        <label for="modeadd" class="col-sm-3 control-label"><?php echo $hesklang['ticket_tpl']; ?></label>

                        <div class="col-sm-9">
                            <div class="radio">
                                <label><input type="radio" name="mode" id="modeadd" value="1"
                                              checked="checked"> <?php echo $hesklang['madd']; ?></label>
                            </div>
                            <div class="radio">
                                <label><input type="radio" name="mode" id="moderep" value="0"/> <?php echo $hesklang['mrep']; ?></label>
                            </div>
                            <?php echo hesk_checkPermission('can_man_ticket_tpl', 0) ? '(<a href="manage_ticket_templates.php">' . $hesklang['ticket_tpl_man'] . '</a>)' : ''; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="saved_replies" class="col-sm-3 control-label"><?php echo $hesklang['select_ticket_tpl']; ?></label>

                        <div class="col-sm-9">
                            <select class="form-control" name="saved_replies" onchange="setMessage(this.value)">
                                <option value="0"> - <?php echo $hesklang['select_empty']; ?> -</option>
                                <?php echo $can_options; ?>
                            </select>
                        </div>
                    </div>
                    <?php
                } // END printing templates
                elseif (hesk_checkPermission('can_man_ticket_tpl', 0)) {
                    ?>
                    <div class="form-group">
                        <div class="col-sm-9 col-sm-offset-3">
                            <a href="manage_ticket_templates.php"><?php echo $hesklang['ticket_tpl_man']; ?></a>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <div class="form-group">
                    <label for="due-date" class="col-sm-3 control-label"><?php echo $hesklang['due_date']; ?></label>
                    <div class="col-sm-9">
                        <input class="form-control datepicker" name="due-date" placeholder="<?php echo htmlspecialchars($hesklang['due_date']); ?>"
                               value="<?php if (isset($_GET['due_date'])) { echo $_GET['due_date']; } ?>">
                        <span class="help-block"><?php echo $hesklang['date_format']; ?></span>
                    </div>
                </div>
                <?php
                $has_error = '';
                if (in_array('subject', $_SESSION['iserror'])) {
                    $has_error = 'has-error';
                }

                $red_star = '';
                $validator = '';
                if ($hesk_settings['require_subject'] == 1) {
                    $red_star = '<span class="important">*</span>';
                    $validator = 'data-error="' . htmlspecialchars($hesklang['enter_subject']) . '"" required';
                }
                ?>
                <div class="form-group <?php echo $has_error; ?>">
                    <label for="subject" class="col-sm-3 control-label">
                        <?php
                        echo $hesklang['subject'];
                        echo $red_star;
                        ?>
                    </label>
                    <div class="col-sm-9">
                        <span id="HeskSub"><input class="form-control" type="text" name="subject" id="subject" size="40" maxlength="40"
                          value="<?php if (isset($_SESSION['as_subject']) || isset($_GET['subject'])) {
                              echo stripslashes(hesk_input($_SESSION['as_subject']));
                          } ?>" placeholder="<?php echo htmlspecialchars($hesklang['subject']); ?>"
                          <?php echo $validator; ?>></span>
                        <div class="help-block with-errors"></div>
                    </div>
                </div>
                <?php
                $has_error = '';
                if (in_array('message', $_SESSION['iserror'])) {
                    $has_error = 'has-error';
                }

                $red_star = '';
                $validator = '';
                if ($hesk_settings['require_message'] == 1) {
                    $red_star = '<span class="important">*</span>';
                    $validator = 'data-error="' . htmlspecialchars($hesklang['enter_message']) . '"" required';
                }
                ?>
                <div class="form-group <?php echo $has_error; ?>" id="message-group">
                    <label for="subject" class="col-sm-3 control-label">
                        <?php
                        echo $hesklang['message'];
                        echo $red_star;
                        ?>
                    </label>
                    <div class="col-sm-9">
                    <span id="HeskMsg">
                        <textarea class="form-control htmlEditor" name="message" id="message" rows="12" cols="60"
                                  placeholder="<?php echo htmlspecialchars($hesklang['message']); ?>"
                                  <?php echo $validator; ?>><?php if (isset($_SESSION['as_message'])) {
                                echo stripslashes(hesk_input($_SESSION['as_message']));
                            } ?></textarea>
                    </span>
                        <div class="help-block with-errors" id="message-help-block"></div>
                    </div>
                </div>
                <?php

                /* custom fields AFTER comments */

                foreach ($hesk_settings['custom_fields'] as $k => $v) {
                    if ($v['use'] && $v['place'] == 1 && hesk_is_custom_field_in_category($k, $category)) {
                        if ($v['req'] == 2) {
                            $v['req']=  '<span class="important">*</span>';
                            $required_attribute = 'data-error="' . $hesklang['this_field_is_required'] . '" required';
                        } else {
                            $v['req'] = '';
                            $required_attribute = '';
                        }

                        if ($v['type'] == 'checkbox') {
                            $k_value = array();
                            if (isset($_SESSION["as_$k"]) && is_array($_SESSION["as_$k"])) {
                                foreach ($_SESSION["as_$k"] as $myCB) {
                                    $k_value[] = stripslashes(hesk_input($myCB));
                                }
                            }
                        } elseif (isset($_SESSION["as_$k"])) {
                            $k_value  = stripslashes(hesk_input($_SESSION["as_$k"]));
                        } else {
                            $k_value  = '';
                        }

                        switch ($v['type']) {
                            /* Radio box */
                            case 'radio':
                                $cls = in_array($k, $_SESSION['iserror']) ? ' isError' : '';

                                echo '<div class="form-group' . $cls . '"><label class="col-sm-3 control-label">' . $v['name'].' '.$v['req'] . '</label><div align="left" class="col-sm-9">';


                                foreach ($v['value']['radio_options'] as $option) {

                                    if (strlen($k_value) == 0) {
                                        $k_value = $option;
                                        $checked = empty($v['value']['no_default']) ? 'checked' : '';
                                    } elseif ($k_value == $option) {
                                        $k_value = $option;
                                        $checked = 'checked';
                                    } else {
                                        $checked = '';
                                    }

                                    echo '<div class="radio"><label><input type="radio" name="' . $k . '" value="' . $option . '" ' . $checked . ' ' . $required_attribute . '> ' . $option . '</label></div>';
                                }

                                echo '<div class="help-block with-errors"></div></div></div>';
                                break;

                            /* Select drop-down box */
                            case 'select':
                                $cls = in_array($k, $_SESSION['iserror']) ? ' isError' : '';

                                echo '<div class="form-group' . $cls . '"><label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'].' '.$v['req'] . '</label>
                        <div class="col-sm-9"><select class="form-control" name="' . $k . '" ' . $required_attribute . '>';

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

                                echo '</select><div class="help-block with-errors"></div></div></div>';
                                break;

                            /* Checkbox */
                            case 'checkbox':
                                $cls = in_array($k, $_SESSION['iserror']) ? ' isError' : '';

                                $validator = $v['req'] == '<span class="important">*</span>' ? 'data-checkbox="' . $k . '"' : '';
                                $required_attribute = $validator == '' ? '' : ' data-error="' . $hesklang['this_field_is_required'] . '"';

                                echo '<div class="form-group' . $cls . '"><label class="col-sm-3 control-label">' . $v['name'].' '.$v['req'] . '</label><div align="left" class="col-sm-9">';

                                foreach ($v['value']['checkbox_options'] as $option) {
                                    if (in_array($option, $k_value)) {
                                        $checked = 'checked';
                                    } else {
                                        $checked = '';
                                    }

                                    echo '<div class="checkbox"><label><input ' . $validator . ' type="checkbox" name="' . $k . '[]" value="' . $option . '" ' . $checked . $required_attribute .'> ' . $option . '</label></div>';
                                }
                                echo '<div class="help-block with-errors"></div></div></div>';
                                break;

                            /* Large text box */
                            case 'textarea':
                                $cls = in_array($k, $_SESSION['iserror']) ? ' isError' : '';

                                echo '<div class="form-group' . $cls . '">
                        <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'].' '.$v['req'] . '</label>
                        <div class="col-sm-9"><textarea class="form-control" placeholder="' . $v['name'] . '" name="' . $k . '" rows="' . intval($v['value']['rows']) . '" cols="' . intval($v['value']['cols']) . '" ' . $required_attribute . '>' . $k_value . '</textarea>
                        <div class="help-block with-errors"></div></div>
                        </div>';
                                break;

                            case 'date':
                                if ($required_attribute != '') {
                                    $required_attribute .= ' pattern="[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])"';
                                }

                                $cls = in_array($k, $_SESSION['iserror']) ? ' isError' : '';

                                echo '
                        <div class="form-group' . $cls . '">
                            <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'].' '.$v['req'] . '</label>
                            <div class="col-sm-9">
                                <input type="text" class="datepicker form-control" placeholder="' . $v['name'] . '" name="' . $k . '" size="40"
                                    value="' . $k_value . '" ' . $required_attribute . '>
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>';
                                break;
                            case 'email':
                                $suggest = $hesk_settings['detect_typos'] ? 'onblur="Javascript:hesk_suggestEmail(\''.$k.'\', \''.$k.'_suggestions\', 0, 1'.($v['value']['multiple'] ? ',1' : '').')"' : '';

                                $cls = in_array($k, $_SESSION['iserror']) ? ' isError' : '';

                                echo '<div class="form-group">
                        <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'].' '.$v['req'] . '</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" placeholder="' . $v['name'] . '" name="' . $k . '" size="40" value="' . $k_value . '" '.$suggest.' ' . $required_attribute . '>
                            <div class="help-block with-errors"></div>
                        </div>
                        </div><div id="'.$k.'_suggestions"></div>';

                                break;

                            case 'hidden':
                            case 'readonly':
                            default:
                                if (strlen($k_value) != 0 || isset($_SESSION["as_$k"])) {
                                    $v['value']['default_value'] = $k_value;
                                }

                                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                                echo '<div class="form-group">
                        <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'].' '.$v['req'] . '</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" placeholder="' . $v['name'] . '" name="' . $k . '" size="40" maxlength="' . intval($v['value']['max_length']) . '" value="' . $v['value']['default_value'] . '" ' . $required_attribute . '>
                            <div class="help-block with-errors"></div>
                        </div>
                        </div>';
                        }
                    }
                }
                /* end custom after */
                /* attachments */
                if ($hesk_settings['attachments']['use']) {

                    ?>
                    <div class="form-group">
                        <label for="attachments" class="control-label col-sm-3"><?php echo $hesklang['attachments']; ?>:</label>

                        <div class="col-sm-9">
                            <?php build_dropzone_markup(true); ?>
                        </div>
                    </div>
                    <?php
                    display_dropzone_field($hesk_settings['hesk_url'] . '/internal-api/ticket/upload-attachment.php');
                }

                if (!isset($_SESSION['as_notify'])) {
                    $_SESSION['as_notify'] = $_SESSION['notify_customer_new'] ? 1 : 0;
                }
                ?>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo $hesklang['addop']; ?>:</label>

                    <div class="col-sm-9">
                        <label><input type="checkbox" id="notify-email" name="notify"
                                      value="1" <?php echo empty($_SESSION['as_notify']) ? '' : 'checked="checked"'; ?> /> <?php echo $hesklang['seno']; ?>
                        </label><br>
                        <label><input type="checkbox" name="show"
                                      value="1" <?php echo (!isset($_SESSION['as_show']) || !empty($_SESSION['as_show'])) ? 'checked="checked"' : ''; ?> /> <?php echo $hesklang['otas']; ?>
                        </label>
                    </div>
                </div>
                <?php
                if (hesk_checkPermission('can_assign_others',0))
                {
                    $has_error = '';
                    if (in_array('owner',$_SESSION['iserror'])) {
                        $has_error = 'has-error';
                    }
                    ?>
                    <div class="form-group <?php echo $has_error; ?>">
                        <label for="owner" class="col-sm-3 control-label"><?php echo $hesklang['asst2']; ?>:</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="owner" >
                                <option value="-1"> &gt; <?php echo $hesklang['unas']; ?> &lt; </option>
                                <?php

                                if ($hesk_settings['autoassign'])
                                {
                                    echo '<option value="-2"> &gt; ' . $hesklang['aass'] . ' &lt; </option>';
                                }

                                $owner = isset($_SESSION['as_owner']) ? intval($_SESSION['as_owner']) : 0;

                                foreach ($admins as $k=>$v)
                                {
                                    if ($k == $owner)
                                    {
                                        echo '<option value="'.$k.'" selected="selected">'.$v.'</option>';
                                    }
                                    else
                                    {
                                        echo '<option value="'.$k.'">'.$v.'</option>';
                                    }

                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <?php
                }
                elseif (hesk_checkPermission('can_assign_self',0))
                {
                    $checked = (!isset($_SESSION['as_owner']) || !empty($_SESSION['as_owner'])) ? 'checked="checked"' : '';
                    ?>
                    <div class="form-group">
                        <div class="col-sm-9 col-sm-offset-3">
                            <label><input type="checkbox" name="assing_to_self" value="1" <?php echo $checked; ?> /> <?php echo $hesklang['asss2']; ?></label>
                        </div>
                    </div>
                    <?php
                }

                if ($modsForHesk_settings['request_location']):
                    ?>
                    <div class="form-group">
                        <label for="location" class="col-md-3 control-label"><?php echo $hesklang['location_colon']; ?></label>

                        <div class="col-sm-9">
                            <p id="console"><?php echo $hesklang['requesting_location_ellipsis']; ?></p>

                            <div id="map" style="height: 300px; display:none">
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <!-- Submit -->
                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-3">
                        <input type="hidden" id="latitude" name="latitude" value="E-0">
                        <input type="hidden" id="longitude" name="longitude" value="E-0">
                        <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>">
                        <input type="hidden" name="category" value="<?php echo $category; ?>">
                        <input type="submit" value="<?php echo $hesklang['sub_ticket']; ?>" class="btn btn-default">
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
</div>
<script>
    buildValidatorForTicketSubmission("form1",
        "<?php echo addslashes($hesklang['select_at_least_one_value']); ?>");
</script>
<?php

// Request for the users location if enabled
if ($modsForHesk_settings['request_location']) {
    echo '
    <script>
        requestUserLocation("' . $hesklang['your_current_location'] . '", "' . $hesklang['unable_to_determine_location'] . '");
    </script>
    ';
}

// Set the message in the actual text box if rich text is enabled
if ($modsForHesk_settings['rich_text_for_tickets']) {
    $message = hesk_SESSION('as_message', '');
    echo "
    <script>
        tinymce.get('message').setContent('');
        tinymce.get('message').execCommand('mceInsertRawHTML', false, '".$message."');
    </script>
    ";
}

hesk_cleanSessionVars('iserror');
hesk_cleanSessionVars('isnotice');

require_once(HESK_PATH . 'inc/footer.inc.php');
exit();

/*** START FUNCTIONS ***/


function print_select_category($number_of_categories) {
    global $hesk_settings, $hesklang;

    // A category needs to be selected
    if (isset($_GET['category']) && empty($_GET['category'])) {
        hesk_process_messages($hesklang['sel_app_cat'],'NOREDIRECT','NOTICE');
    }

    /* This will handle error, success and notice messages */
    hesk_handle_messages();
    ?>
<div class="content-wrapper">
    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h1 class="box-title">
                    <?php echo $hesklang['select_category_staff']; ?>
                </h1>
            </div>
            <div class="box-body">
                <div class="select_category">
                    <?php
                    // Print a select box if number of categories is large
                    if ($number_of_categories > $hesk_settings['cat_show_select'])
                    {
                        ?>
                        <form action="new_ticket.php" method="get">
                            <select name="category" id="select_category" class="form-control">
                                <?php
                                if ($hesk_settings['select_cat'])
                                {
                                    echo '<option value="">'.$hesklang['select'].'</option>';
                                }
                                foreach ($hesk_settings['categories'] as $k=>$v)
                                {
                                    echo '<option value="'.$k.'">'.$v.'</option>';
                                }
                                ?>
                            </select>

                            &nbsp;<br />

                            <div style="text-align:center">
                                <input type="submit" value="<?php echo $hesklang['c2c']; ?>" class="btn btn-default">
                            </div>
                        </form>
                        <?php
                    }
                    // Otherwise print quick links
                    else
                    {
                        // echo '<li><a href="new_ticket.php?a=add&amp;category='.$k.'">&raquo; '.$v.'</a></li>';
                        $new_row = 1;

                        foreach ($hesk_settings['categories'] as $k=>$v):
                            if ($new_row == 1) {
                                echo '<div class="row">';
                                $new_row = -1;
                            }
                            ?>
                            <div class="col-md-5 col-sm-12 <?php if ($new_row == -1) {echo 'col-md-offset-1';} ?>">
                                <a href="new_ticket.php?a=add&category=<?php echo $k; ?>" class="button-link">
                                    <div class="panel panel-default">
                                        <div class="panel-body">
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <?php echo $v; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php
                            $new_row++;
                            if ($new_row == 1) {
                                echo '</div>';
                            }
                        endforeach;
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>
</div>

    <?php

    hesk_cleanSessionVars('iserror');
    hesk_cleanSessionVars('isnotice');

    require_once(HESK_PATH . 'inc/footer.inc.php');
    exit();
} // END print_select_category()
