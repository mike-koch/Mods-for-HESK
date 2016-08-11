<?php
/*******************************************************************************
 *  Title: Help Desk Software HESK
 *  Version: 2.6.8 from 10th August 2016
 *  Author: Klemen Stirn
 *  Website: http://www.hesk.com
 ********************************************************************************
 *  COPYRIGHT AND TRADEMARK NOTICE
 *  Copyright 2005-2015 Klemen Stirn. All Rights Reserved.
 *  HESK is a registered trademark of Klemen Stirn.
 *  The HESK may be used and modified free of charge by anyone
 *  AS LONG AS COPYRIGHT NOTICES AND ALL THE COMMENTS REMAIN INTACT.
 *  By using this code you agree to indemnify Klemen Stirn from any
 *  liability that might arise from it's use.
 *  Selling the code for this program, in part or full, without prior
 *  written consent is expressly forbidden.
 *  Using this code, in part or full, to create derivate work,
 *  new scripts or products is expressly forbidden. Obtain permission
 *  before redistributing this software over the Internet or in
 *  any other medium. In all cases copyright and header must remain intact.
 *  This Copyright is in full effect in any country that has International
 *  Trade Agreements with the United States of America or
 *  with the European Union.
 *  Removing any of the copyright notices without purchasing a license
 *  is expressly forbidden. To remove HESK copyright notice you must purchase
 *  a license for this script. For more information on how to obtain
 *  a license please visit the page below:
 *  https://www.hesk.com/buy.php
 *******************************************************************************/

define('IN_SCRIPT', 1);
define('HESK_PATH', '../');
define('WYSIWYG', 1);
define('VALIDATOR', 1);

// Auto-focus first empty or error field
define('AUTOFOCUS', true);

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/view_attachment_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

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

$columnWidth = 'col-md-8';
$showRs = hesk_dbQuery("SELECT `show` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` WHERE `id` = 5");
$show = hesk_dbFetchAssoc($showRs);
if (!$show['show']) {
    $columnWidth = 'col-md-10 col-md-offset-1';
}
?>

<ol class="breadcrumb">
    <li><a href="admin_main.php"><?php echo $hesk_settings['hesk_title']; ?></a></li>
    <li class="active"><?php echo $hesklang['nti2']; ?></li>
</ol>
<div class="row move-down-20">
    <?php if ($columnWidth == 'col-md-8'): ?>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading"><?php echo $hesklang['quick_help']; ?></div>
                <div class="panel-body">
                    <p><?php echo $hesklang['nti3']; ?></p>
                    <br/>

                    <p><?php echo $hesklang['req_marked_with']; ?> <font class="important">*</font></p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="<?php echo $columnWidth; ?>">
        <?php
        /* This will handle error, success and notice messages */
        hesk_handle_messages();
        ?>
        <h3><?php echo $hesklang['nti2']; ?></h3>

        <div class="footerWithBorder blankSpace"></div>

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
            <?php if (in_array('name', $_SESSION['iserror'])) {
                echo '<div class="form-group has-error">';
            } else {
                echo '<div class="form-group">';
            } ?>
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
        <label for="email" class="col-sm-3 control-label"><?php echo $hesklang['email']; ?></label>

        <div class="col-sm-9">
            <input type="text" class="form-control" name="email" size="40" maxlength="1000" id="email-input"
                   value="<?php if (isset($_SESSION['as_email'])) {
                       echo stripslashes(hesk_input($_SESSION['as_email']));
                   } else if (isset($_GET['email'])) {
                       echo hesk_GET('email');
                   } ?>" <?php if ($hesk_settings['detect_typos']) {
                echo ' onblur="Javascript:hesk_suggestEmail(1)"';
            } ?>
                   placeholder="<?php echo htmlspecialchars($hesklang['email']); ?>"
                   onkeyup="disableIfEmpty('email-input','notify-email')">
        </div>

    </div>
    <div id="email_suggestions"></div>
    <hr/>
    <!-- Department and Priority -->
    <?php if (in_array('category', $_SESSION['iserror'])) {
        echo '<div class="form-group has-error">';
    } elseif (in_array('category', $_SESSION['isnotice'])) {
        echo '<div class="form-group has-warning">';
    } else {
        echo '<div class="form-group">';
    } ?>
    <label for="category" class="col-sm-3 control-label"><?php echo $hesklang['category']; ?><span
            class="important">*</span></label>

    <div class="col-sm-9">
        <select name="category" class="form-control"
            pattern="[0-9]+"
            data-error="<?php echo htmlspecialchars($hesklang['sel_app_cat']); ?>"
            required>
            <?php
            // Show the "Click to select"?
            if ($hesk_settings['select_cat']) {
                echo '<option value="">' . $hesklang['select'] . '</option>';
            }

            // List categories
            $orderByColumn = $modsForHesk_settings['category_order_column'];
            $result = hesk_dbQuery('SELECT * FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'categories` WHERE `usage` <> 2 ORDER BY `' . $orderByColumn . '` ASC');
            while ($row = hesk_dbFetchAssoc($result)) {
                if (isset($_SESSION['as_category']) && $_SESSION['as_category'] == $row['id']) {
                    $selected = ' selected="selected"';
                } else {
                    $selected = '';
                }
                echo '<option value="' . $row['id'] . '"' . $selected . '>' . $row['name'] . '</option>';
            }
            ?>
        </select>
        <div class="help-block with-errors"></div>
    </div>
</div>
<?php if (in_array('priority', $_SESSION['iserror'])) {
    echo '<div class="form-group has-error">';
} else {
    echo '<div class="form-group">';
} ?>
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
<!-- Start Custom Before -->
<?php

/* custom fields BEFORE comments */

foreach ($hesk_settings['custom_fields'] as $k => $v) {
    if ($v['use'] && $v['place'] == 0) {
        if ($modsForHesk_settings['custom_field_setting']) {
            $v['name'] = $hesklang[$v['name']];
        }

        // $v['req'] = $v['req'] ? '<font class="important">*</font>' : '';
        // Staff doesn't need to fill in required custom fields
        $v['req'] = '';

        if ($v['type'] == 'checkbox' && !isset($_GET["c_$k"])) {
            $k_value = array();
            if (isset($_SESSION["c_$k"]) && is_array($_SESSION["c_$k"])) {
                foreach ($_SESSION["c_$k"] as $myCB) {
                    $k_value[] = stripslashes(hesk_input($myCB));
                }
            }
        } elseif (isset($_SESSION["as_$k"])) {
            $k_value = stripslashes(hesk_input($_SESSION["as_$k"]));
        } elseif (isset($_GET["as_$k"])) {
            if ($v['type'] == 'checkbox') {
                $k_value = explode('-CHECKBOX-', $_GET["as_$k"]);
            } else {
                $k_value = stripslashes(hesk_GET("as_$k"));
            }
        } else {
            $k_value = '';
        }

        switch ($v['type']) {
            /* Radio box */
            case 'radio':
                echo '<div class="form-group"><label class="col-sm-3 control-label">' . $v['name'] . '</label><div align="left" class="col-sm-9">';

                $options = explode('#HESK#', $v['value']);
                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                foreach ($options as $option) {

                    if (strlen($k_value) == 0 || $k_value == $option) {
                        $k_value = $option;
                        $checked = 'checked="checked"';
                    } else {
                        $checked = '';
                    }

                    //Clean up multiple dashes or whitespaces
                    $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                    $formattedId = preg_replace("/[\s_]/", "-", $v['name']);

                    echo '<label style="font-weight: normal;"><input type="radio" id="' . $formattedId . '" name="' . $k . '" value="' . $option . '" ' . $checked . ' ' . $cls . ' /> ' . $option . '</label><br />';
                }

                echo '</div></div>';
                break;

            /* Select drop-down box */
            case 'select':

                //Clean up multiple dashes or whitespaces
                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                $formattedId = preg_replace("/[\s_]/", "-", $v['name']);

                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                echo '<div class="form-group"><label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] .  '</label>
                                <div class="col-sm-9"><select class="form-control" id="' . $formattedId . '" name="' . $k . '" ' . $cls . '>';

                // Show "Click to select"?
                $v['value'] = str_replace('{HESK_SELECT}', '', $v['value'], $num);
                if ($num) {
                    echo '<option value="">' . $hesklang['select'] . '</option>';
                }

                $options = explode('#HESK#', $v['value']);

                foreach ($options as $option) {

                    if ($k_value == $option) {
                        $k_value = $option;
                        $selected = 'selected="selected"';
                    } else {
                        $selected = '';
                    }

                    echo '<option ' . $selected . '>' . $option . '</option>';
                }

                echo '</select></div></div>';
                break;

            case 'multiselect':
                //Clean up multiple dashes or whitespaces
                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                $formattedId = preg_replace("/[\s_]/", "-", $v['name']);

                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                echo '<div class="form-group"><label for="' . $v['name'] . '[]" class="col-sm-3 control-label">' . $v['name'] . '</label>
                                <div class="col-sm-9"><select class="form-control" id="' . $formattedId . '" name="' . $k . '[]" ' . $cls . ' multiple>';

                $options = explode('#HESK#', $v['value']);

                foreach ($options as $option) {

                    if (strlen($k_value == $option)) {
                        $k_value = $option;
                        $selected = 'selected="selected"';
                    } else {
                        $selected = '';
                    }

                    echo '<option ' . $selected . '>' . $option . '</option>';
                }

                echo '</select>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-default" onclick="selectAll(\'' . $formattedId . '\')">'.$hesklang['select_all_title_case'].'</button>
                                    <button type="button" class="btn btn-default" onclick="deselectAll(\'' . $formattedId . '\')">'.$hesklang['deselect_all_title_case'].'</button>
                                </div></div></div>';
                break;

            /* Checkbox */
            case 'checkbox':
                //Clean up multiple dashes or whitespaces
                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                $formattedId = preg_replace("/[\s_]/", "-", $v['name']);

                echo '<div class="form-group"><label class="col-sm-3 control-label">' . $v['name'] . '</label><div align="left" class="col-sm-9">';
                $options = explode('#HESK#', $v['value']);
                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                foreach ($options as $option) {

                    if (in_array($option, $k_value)) {
                        $checked = 'checked="checked"';
                    } else {
                        $checked = '';
                    }

                    echo '<label style="font-weight: normal;"><input id="' . $formattedId . '" type="checkbox" name="' . $k . '[]" value="' . $option . '" ' . $checked . ' ' . $cls . ' /> ' . $option . '</label><br />';
                }
                echo '</div></div>';
                break;

            /* Large text box */
            case 'textarea':
                //Clean up multiple dashes or whitespaces
                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                $formattedId = preg_replace("/[\s_]/", "-", $v['name']);

                $size = explode('#', $v['value']);
                $size[0] = empty($size[0]) ? 5 : intval($size[0]);
                $size[1] = empty($size[1]) ? 30 : intval($size[1]);

                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                echo '<div class="form-group">
                                <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . '</label>
					            <div class="col-sm-9"><textarea class="form-control" placeholder="' . htmlspecialchars($v['name']) . '" id="' . $formattedId . '" name="' . $k . '" rows="' . $size[0] . '" cols="' . $size[1] . '" ' . $cls . '>' . $k_value . '</textarea></div>
                                </div>';
                break;

            case 'date':
                //Clean up multiple dashes or whitespaces
                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                $formattedId = preg_replace("/[\s_]/", "-", $v['name']);

                if (strlen($k_value) != 0) {
                    $v['value'] = $k_value;
                }

                $cls = in_array($k, $_SESSION['iserror']) ? ' isError ' : '';

                echo '
                                <div class="form-group">
                                    <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . '</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="datepicker form-control white-readonly ' . $cls . '" placeholder="' . htmlspecialchars($v['name']) . '" id="' . $formattedId . '" name="' . $k . '" size="40"
                                            maxlength="' . $v['maxlen'] . '" value="' . $v['value'] . '" readonly/>
                                        <span class="help-block">' . $hesklang['date_format'] . '</span>
                                    </div>
                                </div>';
                break;

            case 'email':
                //Clean up multiple dashes or whitespaces
                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                $formattedId = preg_replace("/[\s_]/", "-", $v['name']);

                if (strlen($k_value) != 0) {
                    $v['value'] = $k_value;
                }

                if ($v['value'] == 'cc' || $v['value'] == 'bcc') {
                    // (b)cc isn't a valid email but is the "value" used by settings. Just remove it.
                    $v['value'] = '';
                }

                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                echo '<div class="form-group">
                                <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . '</label>
					            <div class="col-sm-9"><input type="text" class="form-control" placeholder="' . htmlspecialchars($v['name']) . '" id="' . $formattedId . '" name="' . $k . '" size="40" maxlength="' . $v['maxlen'] . '" value="' . $v['value'] . '" ' . $cls . ' /></div>
                                </div>';

                break;

            case 'hidden':
                //Clean up multiple dashes or whitespaces
                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                $formattedId = preg_replace("/[\s_]/", "-", $formattedId);

                if (strlen($k_value) != 0) {
                    $v['value'] = $k_value;
                }

                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                echo '<input type="hidden" class="form-control" id="' . $formattedId . '" name="' . $k . '" size="40" maxlength="' . $v['maxlen'] . '" value="' . $v['value'] . '" ' . $cls . ' />';

                break;

            case 'readonly':
                //Clean up multiple dashes or whitespaces
                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                $formattedId = preg_replace("/[\s_]/", "-", $formattedId);

                if (strlen($k_value) != 0) {
                    $v['value'] = $k_value;
                }

                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                echo '<div class="form-group">
                                <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . '</label>
					            <div class="col-sm-9"><input type="text" class="form-control" id="' . $formattedId . '" name="' . $k . '" size="40" maxlength="' . $v['maxlen'] . '" value="' . $v['value'] . '" ' . $cls . ' readonly></div>
                                </div>';

                break;

            /* Default text input */
            default:
                //Clean up multiple dashes or whitespaces
                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                $formattedId = preg_replace("/[\s_]/", "-", $v['name']);

                if (strlen($k_value) != 0) {
                    $v['value'] = $k_value;
                }

                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                echo '<div class="form-group">
                                <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . '</label>
					            <div class="col-sm-9"><input type="text" class="form-control" placeholder="' . htmlspecialchars($v['name']) . '" id="' . $formattedId . '" name="' . $k . '" size="40" maxlength="' . $v['maxlen'] . '" value="' . $v['value'] . '" ' . $cls . ' /></div>
                                </div>';
        }
    }
}
?>
<!-- End custom before -->
<!-- Ticket Info -->
<?php
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

<?php if (in_array('subject', $_SESSION['iserror'])) {
    echo '<div class="form-group has-error">';
} else {
    echo '<div class="form-group">';
} ?>
<label for="subject" class="col-sm-3 control-label"><?php echo $hesklang['subject']; ?><span
        class="important">*</span></label>
<div class="col-sm-9">
    <span id="HeskSub"><input class="form-control" type="text" name="subject" id="subject" size="40" maxlength="40"
                              value="<?php if (isset($_SESSION['as_subject']) || isset($_GET['subject'])) {
                                  echo stripslashes(hesk_input($_SESSION['as_subject']));
                              } ?>" placeholder="<?php echo htmlspecialchars($hesklang['subject']); ?>"
                                data-error="<?php echo htmlspecialchars($hesklang['enter_subject']); ?>"
            required></span>
    <div class="help-block with-errors"></div>
</div>
</div>
<?php if (in_array('message', $_SESSION['iserror'])) {
    echo '<div class="form-group has-error" id="message-group">';
} else {
    echo '<div class="form-group" id="message-group">';
} ?>
<label for="subject" class="col-sm-3 control-label"><?php echo $hesklang['message']; ?><span
        class="important">*</span></label>
<div class="col-sm-9">
                        <span id="HeskMsg">
                            <textarea class="form-control htmlEditor" name="message" id="message" rows="12" cols="60"
                                      placeholder="<?php echo htmlspecialchars($hesklang['message']); ?>"
                                      data-error="<?php echo htmlspecialchars($hesklang['enter_message']); ?>"
                                      required><?php if (isset($_SESSION['as_message'])) {
                                    echo stripslashes(hesk_input($_SESSION['as_message']));
                                } ?></textarea>
                        </span>
    <div class="help-block with-errors" id="message-help-block"></div>
</div>
</div>
<hr/>
<!-- Custom After -->
<?php

/* custom fields AFTER comments */

foreach ($hesk_settings['custom_fields'] as $k => $v) {
    if ($v['use'] && $v['place']) {
        if ($modsForHesk_settings['custom_field_setting']) {
            $v['name'] = $hesklang[$v['name']];
        }

        // $v['req'] = $v['req'] ? '<font class="important">*</font>' : '';
        // Staff doesn't need to fill in required custom fields
        $v['req'] = '';

        if ($v['type'] == 'checkbox') {
            $k_value = array();
            if (isset($_SESSION["c_$k"]) && is_array($_SESSION["c_$k"])) {
                foreach ($_SESSION["c_$k"] as $myCB) {
                    $k_value[] = stripslashes(hesk_input($myCB));
                }
            }
        } elseif (isset($_SESSION["c_$k"])) {
            $k_value = stripslashes(hesk_input($_SESSION["c_$k"]));
        } else {
            $k_value = '';
        }

        switch ($v['type']) {
            /* Radio box */
            case 'radio':
                //Clean up multiple dashes or whitespaces
                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                $formattedId = preg_replace("/[\s_]/", "-", $v['name']);

                echo '<div class="form-group"><label class="col-sm-3 control-label">' . $v['name'] . '</label><div align="left" class="col-sm-9">';

                $options = explode('#HESK#', $v['value']);
                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                foreach ($options as $option) {

                    if (strlen($k_value) == 0 || $k_value == $option) {
                        $k_value = $option;
                        $checked = 'checked="checked"';
                    } else {
                        $checked = '';
                    }

                    echo '<label style="font-weight: normal;"><input type="radio" id="' . $formattedId . '" name="' . $k . '" value="' . $option . '" ' . $checked . ' ' . $cls . ' /> ' . $option . '</label><br />';
                }

                echo '</div></div>';
                break;

            /* Select drop-down box */
            case 'select':
                //Clean up multiple dashes or whitespaces
                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                $formattedId = preg_replace("/[\s_]/", "-", $v['name']);

                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                echo '<div class="form-group"><label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . '</label>
                                <div class="col-sm-9"><select class="form-control" id="' . $formattedId . '" name="' . $k . '" ' . $cls . '>';

                // Show "Click to select"?
                $v['value'] = str_replace('{HESK_SELECT}', '', $v['value'], $num);
                if ($num) {
                    echo '<option value="">' . $hesklang['select'] . '</option>';
                }

                $options = explode('#HESK#', $v['value']);

                foreach ($options as $option) {

                    if ($k_value == $option) {
                        $k_value = $option;
                        $selected = 'selected="selected"';
                    } else {
                        $selected = '';
                    }

                    echo '<option ' . $selected . '>' . $option . '</option>';
                }

                echo '</select></div></div>';
                break;

            /* Checkbox */
            case 'checkbox':
                //Clean up multiple dashes or whitespaces
                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                $formattedId = preg_replace("/[\s_]/", "-", $v['name']);

                echo '<div class="form-group"><label class="col-sm-3 control-label">' . $v['name'] . '</label><div align="left" class="col-sm-9">';

                $options = explode('#HESK#', $v['value']);
                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                foreach ($options as $option) {

                    if (in_array($option, $k_value)) {
                        $checked = 'checked="checked"';
                    } else {
                        $checked = '';
                    }

                    echo '<label style="font-weight: normal;"><input id="' . $formattedId . '" type="checkbox" name="' . $k . '[]" value="' . $option . '" ' . $checked . ' ' . $cls . ' /> ' . $option . '</label><br />';
                }
                echo '</div></div>';
                break;

            /* Large text box */
            case 'textarea':
                //Clean up multiple dashes or whitespaces
                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                $formattedId = preg_replace("/[\s_]/", "-", $v['name']);

                $size = explode('#', $v['value']);
                $size[0] = empty($size[0]) ? 5 : intval($size[0]);
                $size[1] = empty($size[1]) ? 30 : intval($size[1]);

                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                echo '<div class="form-group">
                                <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . '</label>
					            <div class="col-sm-9"><textarea class="form-control" placeholder="' . htmlspecialchars($v['name']) . '" id="' . $formattedId . '" name="' . $k . '" rows="' . $size[0] . '" cols="' . $size[1] . '" ' . $cls . '>' . $k_value . '</textarea></div>
                                </div>';
                break;

            case 'date':
                //Clean up multiple dashes or whitespaces
                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                $formattedId = preg_replace("/[\s_]/", "-", $v['name']);

                if (strlen($k_value) != 0) {
                    $v['value'] = $k_value;
                }

                $cls = in_array($k, $_SESSION['iserror']) ? ' isError ' : '';

                echo '
                                <div class="form-group">
                                    <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . '</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="datepicker form-control white-readonly ' . $cls . '" placeholder="' . htmlspecialchars($v['name']) . '" id="' . $formattedId . '" name="' . $k . '" size="40"
                                            maxlength="' . $v['maxlen'] . '" value="' . $v['value'] . '" readonly/>
                                        <span class="help-block">' . $hesklang['date_format'] . '</span>
                                    </div>
                                </div>';
                break;

            case 'multiselect':
                //Clean up multiple dashes or whitespaces
                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                $formattedId = preg_replace("/[\s_]/", "-", $v['name']);

                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                echo '<div class="form-group"><label for="' . $v['name'] . '[]" class="col-sm-3 control-label">' . $v['name'] . '</label>
                                <div class="col-sm-9"><select class="form-control" id="' . $formattedId . '" name="' . $k . '[]" ' . $cls . ' multiple>';

                $options = explode('#HESK#', $v['value']);

                foreach ($options as $option) {

                    if ($k_value == $option) {
                        $k_value = $option;
                        $selected = 'selected="selected"';
                    } else {
                        $selected = '';
                    }

                    echo '<option ' . $selected . '>' . $option . '</option>';
                }

                echo '</select>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-default" onclick="selectAll(\'' . $formattedId . '\')">'.$hesklang['select_all_title_case'].'</button>
                                    <button type="button" class="btn btn-default" onclick="deselectAll(\'' . $formattedId . '\')">'.$hesklang['deselect_all_title_case'].'</button>
                                </div></div></div>';
                break;

            case 'email':
                //Clean up multiple dashes or whitespaces
                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                $formattedId = preg_replace("/[\s_]/", "-", $v['name']);

                if (strlen($k_value) != 0) {
                    $v['value'] = $k_value;
                }

                if ($v['value'] == 'cc' || $v['value'] == 'bcc') {
                    // (b)cc isn't a valid email but is the "value" used by settings. Just remove it.
                    $v['value'] = '';
                }

                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                echo '<div class="form-group">
                                <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . '</label>
					            <div class="col-sm-9"><input type="text" class="form-control" placeholder="' . htmlspecialchars($v['name']) . '" id="' . $formattedId . '" name="' . $k . '" size="40" maxlength="' . $v['maxlen'] . '" value="' . $v['value'] . '" ' . $cls . ' /></div>
                                </div>';

                break;

            case 'hidden':
                //Clean up multiple dashes or whitespaces
                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                $formattedId = preg_replace("/[\s_]/", "-", $formattedId);

                if (strlen($k_value) != 0) {
                    $v['value'] = $k_value;
                }

                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                echo '<input type="hidden" class="form-control" id="' . $formattedId . '" name="' . $k . '" size="40" maxlength="' . $v['maxlen'] . '" value="' . $v['value'] . '" ' . $cls . ' />';

                break;

            case 'readonly':
                //Clean up multiple dashes or whitespaces
                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                $formattedId = preg_replace("/[\s_]/", "-", $formattedId);

                if (strlen($k_value) != 0) {
                    $v['value'] = $k_value;
                }

                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                echo '<div class="form-group">
                                <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . '</label>
					            <div class="col-sm-9"><input type="text" class="form-control" id="' . $formattedId . '" name="' . $k . '" size="40" maxlength="' . $v['maxlen'] . '" value="' . $v['value'] . '" ' . $cls . ' readonly></div>
                                </div>';

                break;

            /* Default text input */
            default:
                //Clean up multiple dashes or whitespaces
                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                $formattedId = preg_replace("/[\s_]/", "-", $v['name']);

                if (strlen($k_value) != 0) {
                    $v['value'] = $k_value;
                }

                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                echo '<div class="form-group">
                                <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . '</label>
					            <div class="col-sm-9"><input type="text" class="form-control" placeholder="' . htmlspecialchars($v['name']) . '" id="' . $formattedId . '" name="' . $k . '" size="40" maxlength="' . $v['maxlen'] . '" value="' . $v['value'] . '" ' . $cls . ' /></div>
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
    <hr/>
    <?php
    display_dropzone_field($hesk_settings['hesk_url'] . '/internal-api/ticket/upload-attachment.php');
}
?>
<!-- Admin options -->
<?php
if (!isset($_SESSION['as_notify'])) {
    $_SESSION['as_notify'] = $_SESSION['notify_customer_new'] ? 1 : 0;
}
?>
<div class="form-group">
    <label class="col-sm-3 control-label"><?php echo $hesklang['addop']; ?>:</label>

    <div class="col-sm-9">
        <label><input type="checkbox" id="notify-email" name="notify"
                      value="1" <?php echo empty($_SESSION['as_notify']) ? '' : 'checked="checked"'; ?> /> <?php echo $hesklang['seno']; ?>
        </label><br/>
        <label><input type="checkbox" name="show"
                      value="1" <?php echo (!isset($_SESSION['as_show']) || !empty($_SESSION['as_show'])) ? 'checked="checked"' : ''; ?> /> <?php echo $hesklang['otas']; ?>
        </label><br/>
        <hr/>
    </div>
</div>
<?php
if (hesk_checkPermission('can_assign_others',0))
{
  if (in_array('owner',$_SESSION['iserror'])) {echo '<div class="form-group has-error">';} else {echo '<div class="form-group">';} ?>
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
        <input type="submit" value="<?php echo $hesklang['sub_ticket']; ?>" class="btn btn-default">
    </div>
</div>
</form>
<script>
    buildValidatorForTicketSubmission("form1",
        "<?php echo addslashes($hesklang['select_at_least_one_value']); ?>");
</script>
</div>
</div>
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
?>
