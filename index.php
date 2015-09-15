<?php
/*******************************************************************************
 *  Title: Help Desk Software HESK
 *  Version: 2.6.5 from 28th August 2015
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
define('HESK_PATH', './');
define('WYSIWYG', 1);

// Get all the required files and functions
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');

hesk_load_database_functions();
hesk_dbConnect();
// Are we in maintenance mode?
hesk_check_maintenance();

// Are we in "Knowledgebase only" mode?
hesk_check_kb_only();

$modsForHesk_settings = mfh_getSettings();

// What should we do?
$action = hesk_REQUEST('a');

switch ($action) {
    case 'add':
        hesk_session_start();
        print_add_ticket();
        break;

    case 'forgot_tid':
        hesk_session_start();
        forgot_tid();
        break;

    default:
        print_start();
}

// Print footer
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();

/*** START FUNCTIONS ***/

function print_add_ticket()
{
    global $hesk_settings, $hesklang, $modsForHesk_settings;

    // Auto-focus first empty or error field
    define('AUTOFOCUS', true);

    // Pre-populate fields
    // Customer name
    if (isset($_REQUEST['name'])) {
        $_SESSION['c_name'] = $_REQUEST['name'];
    }

    // Customer email address
    if (isset($_REQUEST['email'])) {
        $_SESSION['c_email'] = $_REQUEST['email'];
        $_SESSION['c_email2'] = $_REQUEST['email'];
    }

    // Category ID
    if (isset($_REQUEST['catid'])) {
        $_SESSION['c_category'] = intval($_REQUEST['catid']);
    }
    if (isset($_REQUEST['category'])) {
        $_SESSION['c_category'] = intval($_REQUEST['category']);
    }

    // Priority
    if (isset($_REQUEST['priority'])) {
        $_SESSION['c_priority'] = intval($_REQUEST['priority']);
    }

    // Subject
    if (isset($_REQUEST['subject'])) {
        $_SESSION['c_subject'] = $_REQUEST['subject'];
    }

    // Message
    if (isset($_REQUEST['message'])) {
        $_SESSION['c_message'] = $_REQUEST['message'];
    }

    // Custom fields
    foreach ($hesk_settings['custom_fields'] as $k => $v) {
        if ($v['use'] && isset($_REQUEST[$k])) {
            $_SESSION['c_' . $k] = $_REQUEST[$k];
        }
    }


    // Variables for coloring the fields in case of errors
    if (!isset($_SESSION['iserror'])) {
        $_SESSION['iserror'] = array();
    }

    if (!isset($_SESSION['isnotice'])) {
        $_SESSION['isnotice'] = array();
    }

    if (!isset($_SESSION['c_category']) && !$hesk_settings['select_cat']) {
        $_SESSION['c_category'] = 0;
    }

    hesk_cleanSessionVars('already_submitted');

    // Tell header to load reCaptcha API if needed
    if ($hesk_settings['recaptcha_use'] == 2) {
        define('RECAPTCHA', 1);
    }

    // Print header
    $hesk_settings['tmp_title'] = $hesk_settings['hesk_title'] . ' - ' . $hesklang['submit_ticket'];
    require_once(HESK_PATH . 'inc/header.inc.php');
    ?>

    <ol class="breadcrumb">
        <li><a href="<?php echo $hesk_settings['site_url']; ?>"><?php echo $hesk_settings['site_title']; ?></a></li>
        <li><a href="<?php echo $hesk_settings['hesk_url']; ?>"><?php echo $hesk_settings['hesk_title']; ?></a></li>
        <li class="active"><?php echo $hesklang['sub_support']; ?></li>
    </ol>

    <!-- START MAIN LAYOUT -->
    <?php
    $columnWidth = 'col-md-8';
    hesk_dbConnect();
    $showRs = hesk_dbQuery("SELECT `show` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` WHERE `id` = 1");
    $show = hesk_dbFetchAssoc($showRs);
    if (!$show['show']) {
        $columnWidth = 'col-md-10 col-md-offset-1';
    }
    ?>
    <div class="row">
    <?php if ($columnWidth == 'col-md-8'): ?>
    <div align="left" class="col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo $hesklang['quick_help']; ?></div>
            <div class="panel-body">
                <p><?php echo $hesklang['quick_help_submit_ticket']; ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>
    <div class="<?php echo $columnWidth; ?>">
        <?php
        // This will handle error, success and notice messages
        hesk_handle_messages();
        ?>
        <!-- START FORM -->
        <div class="form">
            <h2><?php hesk_showTopBar($hesklang['submit_ticket']); ?></h2>
            <small><?php echo $hesklang['use_form_below']; ?></small>
            <div class="blankSpace"></div>

            <div align="left" class="h3"><?php echo $hesklang['add_ticket_general_information']; ?></div>
            <div class="footerWithBorder"></div>
            <div class="blankSpace"></div>
            <form class="form-horizontal" role="form" method="post" action="submit_ticket.php?submit=1" name="form1"
                  enctype="multipart/form-data" data-toggle="validator" onsubmit="return validateRichText();">
                <!-- Contact info -->
                <div class="form-group">
                    <label for="name" class="col-sm-3 control-label"><?php echo $hesklang['name']; ?>: <font
                            class="important">*</font></label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="name" name="name" size="40" maxlength="30"
                               value="<?php if (isset($_SESSION['c_name'])) {
                                   echo stripslashes(hesk_input($_SESSION['c_name']));
                               } ?>" <?php if (in_array('name', $_SESSION['iserror'])) {
                            echo ' class="isError" ';
                        } ?> placeholder="<?php echo htmlspecialchars($hesklang['name']); ?>"
                               data-error="<?php echo htmlspecialchars($hesklang['enter_your_name']); ?>" required>

                        <div class="help-block with-errors"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class="col-sm-3 control-label"><?php echo $hesklang['email']; ?>: <span
                            class="important">*</span></label>

                    <div class="col-sm-9">
                        <input type="email" class="form-control" id="email" name="email" size="40" maxlength="1000"
                               value="<?php if (isset($_SESSION['c_email'])) {
                                   echo stripslashes(hesk_input($_SESSION['c_email']));
                               } ?>" <?php if (in_array('email', $_SESSION['iserror'])) {
                            echo ' class="isError" ';
                        } elseif (in_array('email', $_SESSION['isnotice'])) {
                            echo ' class="isNotice" ';
                        } ?> <?php if ($hesk_settings['detect_typos']) {
                            echo ' onblur="Javascript:hesk_suggestEmail(0)"';
                        } ?> placeholder="<?php echo htmlspecialchars($hesklang['email']); ?>"
                               data-error="<?php echo htmlspecialchars($hesklang['enter_valid_email']); ?>" required>

                        <div class="help-block with-errors"></div>
                    </div>
                </div>
                <?php
                if ($hesk_settings['confirm_email']) {
                    ?>
                    <div class="form-group">
                        <label for="email2" class="col-sm-3 control-label"><?php echo $hesklang['confemail']; ?>: <span
                                class="important">*</span></label>

                        <div class="col-sm-9">
                            <input type="email" id="email2" class="form-control" name="email2" size="40"
                                   maxlength="1000"
                                   value="<?php if (isset($_SESSION['c_email2'])) {
                                       echo stripslashes(hesk_input($_SESSION['c_email2']));
                                   } ?>" <?php if (in_array('email2', $_SESSION['iserror'])) {
                                echo ' class="isError" ';
                            } ?> placeholder="<?php echo htmlspecialchars($hesklang['confemail']); ?>"
                                   data-match="#email"
                                   data-error="<?php echo htmlspecialchars($hesklang['confemaile']); ?>" required>

                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <?php
                } ?>
                <div id="email_suggestions"></div>
                <!-- Department and priority -->
                <?php
                // Get categories
                hesk_dbConnect();
                $orderBy = $modsForHesk_settings['category_order_column'];
                $res = hesk_dbQuery("SELECT `id`, `name` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` WHERE `type`='0' ORDER BY `" . $orderBy . "` ASC");

                if (hesk_dbNumRows($res) == 1) {
                    // Only 1 public category, no need for a select box
                    $row = hesk_dbFetchAssoc($res);
                    echo '<input type="hidden" name="category" value="' . $row['id'] . '" />';
                } elseif (hesk_dbNumRows($res) < 1) {
                    // No public categories, set it to default one
                    echo '<input type="hidden" name="category" value="1" />';
                } else {
                    ?>
                    <div class="form-group">
                        <label for="category" class="col-sm-3 control-label"><?php echo $hesklang['category']; ?>: <span
                                class="important">*</span></label>

                        <div class="col-sm-9">
                            <select name="category" id="category"
                                    class="form-control" pattern="[0-9]+"
                                    data-error="<?php echo htmlspecialchars($hesklang['sel_app_cat']); ?>" required
                                <?php if (in_array('category', $_SESSION['iserror'])) {
                                    echo ' class="isError" ';
                                } ?> ><?php
                                // Show the "Click to select"?
                                if ($hesk_settings['select_cat']) {
                                    echo '<option value="">' . $hesklang['select'] . '</option>';
                                }
                                // List categories
                                while ($row = hesk_dbFetchAssoc($res)) {
                                    echo '<option value="' . $row['id'] . '"' . (($_SESSION['c_category'] == $row['id']) ? ' selected="selected"' : '') . '>' . $row['name'] . '</option>';
                                } ?>
                            </select>

                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <?php
                }

                /* Can customer assign urgency? */
                if ($hesk_settings['cust_urgency']) {
                    ?>
                    <div class="form-group">
                        <label for="priority" class="col-sm-3 control-label"><?php echo $hesklang['priority']; ?>: <span
                                class="important">*</span></label>

                        <div class="col-sm-9">
                            <select id="priority" class="form-control"
                                    pattern="[0-9]+"
                                    data-error="<?php echo htmlspecialchars($hesklang['sel_app_priority']); ?>"
                                    name="priority" <?php if (in_array('priority', $_SESSION['iserror'])) {
                                echo ' class="isError" ';
                            } ?> required>
                                <?php
                                // Show the "Click to select"?
                                if ($hesk_settings['select_pri']) {
                                    echo '<option value="">' . $hesklang['select'] . '</option>';
                                }
                                ?>
                                <option
                                    value="3" <?php if (isset($_SESSION['c_priority']) && $_SESSION['c_priority'] == 3) {
                                    echo 'selected="selected"';
                                } ?>><?php echo $hesklang['low']; ?></option>
                                <option
                                    value="2" <?php if (isset($_SESSION['c_priority']) && $_SESSION['c_priority'] == 2) {
                                    echo 'selected="selected"';
                                } ?>><?php echo $hesklang['medium']; ?></option>
                                <option
                                    value="1" <?php if (isset($_SESSION['c_priority']) && $_SESSION['c_priority'] == 1) {
                                    echo 'selected="selected"';
                                } ?>><?php echo $hesklang['high']; ?></option>
                            </select>

                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <?php
                }
                ?>

                <!-- START CUSTOM BEFORE -->
                <?php

                /* custom fields BEFORE comments */

                foreach ($hesk_settings['custom_fields'] as $k => $v) {

                    if ($v['use'] && $v['place'] == 0) {
                        if ($modsForHesk_settings['custom_field_setting']) {
                            $v['name'] = $hesklang[$v['name']];
                        }

                        $required = $v['req'] ? 'required' : '';
                        $v['req'] = $v['req'] ? '<span class="important">*</span>' : '';

                        if ($v['type'] == 'checkbox' || $v['type'] == 'multiselect') {
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
                                $formattedId = preg_replace("/[\s_]/", "-", $formattedId);

                                echo '<div class="form-group"><label class="col-sm-3 control-label">' . $v['name'] . ': ' . $v['req'] . '</label><div align="left" class="col-sm-9">';

                                $options = explode('#HESK#', $v['value']);
                                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                                foreach ($options as $option) {

                                    if (strlen($k_value) == 0 || $k_value == $option) {
                                        $k_value = $option;
                                        $checked = 'checked="checked"';
                                    } else {
                                        $checked = '';
                                    }

                                    echo '<label style="font-weight: normal;"><input type="radio" id="' . $formattedId . '" name="' . $k . '" value="' . $option . '" ' . $checked . ' ' . $cls . ' ' . $required . ' > ' . $option . '</label><br />';
                                }

                                echo '<div class="help-block with-errors"></div>';
                                echo '</div></div>';
                                break;

                            /* Select drop-down box */
                            case 'select':
                                //Clean up multiple dashes or whitespaces
                                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                                $formattedId = preg_replace("/[\s_]/", "-", $formattedId);

                                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                                echo '<div class="form-group"><label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . ': ' . $v['req'] . '</label>
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
                                $formattedId = preg_replace("/[\s_]/", "-", $formattedId);

                                echo '<div class="form-group"><label class="col-sm-3 control-label">' . $v['name'] . ': ' . $v['req'] . '</label><div align="left" class="col-sm-9">';

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
                                $errorText = $required == 'required' ? 'data-error="' . htmlspecialchars($hesklang['this_field_is_required']) . '"' : '';
                                //Clean up multiple dashes or whitespaces
                                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                                $formattedId = preg_replace("/[\s_]/", "-", $formattedId);

                                $size = explode('#', $v['value']);
                                $size[0] = empty($size[0]) ? 5 : intval($size[0]);
                                $size[1] = empty($size[1]) ? 30 : intval($size[1]);

                                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                                echo '<div class="form-group">
                                <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . ': ' . $v['req'] . '</label>
					            <div class="col-sm-9"><textarea class="form-control" id="' . $formattedId . '" name="' . $k . '" rows="' . $size[0] . '" cols="' . $size[1] . '" ' . $cls . ' ' . $errorText . ' ' . $required . '>' . $k_value . '</textarea>
                                <div class="help-block with-errors"></div>
					            </div>
                                </div>';
                                break;

                            case 'multiselect':
                                //Clean up multiple dashes or whitespaces
                                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                                $formattedId = preg_replace("/[\s_]/", "-", $formattedId);

                                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                                echo '<div class="form-group"><label for="' . $v['name'] . '[]" class="col-sm-3 control-label">' . $v['name'] . ': ' . $v['req'] . '</label>
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
                                    <button type="button" class="btn btn-default" onclick="selectAll(\'' . $formattedId . '\')">Select All</button>
                                    <button type="button" class="btn btn-default" onclick="deselectAll(\'' . $formattedId . '\')">Deselect All</button>
                                </div>
                                </div></div>';
                                break;

                            case 'date':
                                //Clean up multiple dashes or whitespaces
                                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                                $formattedId = preg_replace("/[\s_]/", "-", $formattedId);

                                if (strlen($k_value) != 0) {
                                    $v['value'] = $k_value;
                                }

                                $cls = in_array($k, $_SESSION['iserror']) ? ' isError ' : '';

                                echo '
                                <div class="form-group">
                                    <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . ': ' . $v['req'] . '</label>
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
                                $formattedId = preg_replace("/[\s_]/", "-", $formattedId);

                                if (strlen($k_value) != 0) {
                                    $v['value'] = $k_value;
                                }

                                if ($v['value'] == 'cc' || $v['value'] == 'bcc') {
                                    // (b)cc isn't a valid email but is the "value" used by settings. Just remove it.
                                    $v['value'] = '';
                                }

                                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                                echo '<div class="form-group">
                                <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . ': ' . $v['req'] . '</label>
					            <div class="col-sm-9"><input type="email" class="form-control" id="' . $formattedId . '" name="' . $k . '" size="40" maxlength="' . $v['maxlen'] . '" value="' . $v['value'] . '" data-error="' . htmlspecialchars($hesklang['enter_valid_email']) . '" ' . $cls . ' ' . $required . '>
					            <div class="help-block with-errors"></div>
					            </div>
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
                                <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . ': ' . $v['req'] . '</label>
					            <div class="col-sm-9"><input type="text" class="form-control" id="' . $formattedId . '" name="' . $k . '" size="40" maxlength="' . $v['maxlen'] . '" value="' . $v['value'] . '" ' . $cls . ' readonly></div>
                                </div>';

                                break;

                            /* Default text input */
                            default:
                                $errorText = $required == 'required' ? 'data-error="' . htmlspecialchars($hesklang['this_field_is_required']) . '"' : '';
                                //Clean up multiple dashes or whitespaces
                                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                                $formattedId = preg_replace("/[\s_]/", "-", $formattedId);

                                if (strlen($k_value) != 0) {
                                    $v['value'] = $k_value;
                                }

                                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                                echo '<div class="form-group">
                                <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . ': ' . $v['req'] . '</label>
					            <div class="col-sm-9"><input type="text" class="form-control" id="' . $formattedId . '" name="' . $k . '" size="40" maxlength="' . $v['maxlen'] . '" value="' . $v['value'] . '" ' . $cls . ' ' . $errorText . ' ' . $required . '>
					            <div class="help-block with-errors"></div>
					            </div>
                                </div>';
                        }
                    }
                }

                ?>
                <!-- END CUSTOM BEFORE -->

                <div class="blankSpace"></div>
                <div align="left" class="h3"><?php echo $hesklang['add_ticket_your_message']; ?></div>
                <div class="footerWithBorder"></div>
                <div class="blankSpace"></div>
                <!-- ticket info -->
                <div class="form-group">
                    <label for="subject" class="col-sm-3 control-label"><?php echo $hesklang['subject']; ?>: <span
                            class="important">*</span></label>

                    <div class="col-sm-9">
                        <input type="text" id="subject" class="form-control" name="subject" size="40" maxlength="40"
                               value="<?php if (isset($_SESSION['c_subject'])) {
                                   echo stripslashes(hesk_input($_SESSION['c_subject']));
                               } ?>" <?php if (in_array('subject', $_SESSION['iserror'])) {
                            echo ' class="isError" ';
                        } ?> placeholder="<?php echo htmlspecialchars($hesklang['subject']); ?>"
                               data-error="<?php echo htmlspecialchars($hesklang['enter_subject']); ?>" required>

                        <div class="help-block with-errors"></div>
                    </div>
                </div>
                <div class="form-group" id="message-group">

                    <div class="col-sm-12">
                        <textarea placeholder="<?php echo htmlspecialchars($hesklang['message']); ?>" name="message"
                                  id="message" class="form-control htmlEditor" rows="12"
                                  cols="60" <?php if (in_array('message', $_SESSION['iserror'])) {
                            echo ' class="isError" ';
                        } ?> data-error="<?php echo htmlspecialchars($hesklang['enter_message']); ?>"
                                  required><?php if (isset($_SESSION['c_message'])) {
                                echo stripslashes(hesk_input($_SESSION['c_message']));
                            } ?></textarea>

                        <div class="help-block with-errors" id="message-help-block"></div>
                        <?php if ($modsForHesk_settings['rich_text_for_tickets_for_customers']): ?>
                            <script type="text/javascript">
                                /* <![CDATA[ */
                                tinyMCE.init({
                                    mode: "textareas",
                                    editor_selector: "htmlEditor",
                                    elements: "content",
                                    theme: "advanced",
                                    convert_urls: false,

                                    theme_advanced_buttons1: "cut,copy,paste,|,undo,redo,|,formatselect,fontselect,fontsizeselect,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull",
                                    theme_advanced_buttons2: "sub,sup,|,charmap,|,bullist,numlist,|,outdent,indent,insertdate,inserttime,preview,|,forecolor,backcolor,|,hr,removeformat,visualaid,|,link,unlink,anchor,image,cleanup",
                                    theme_advanced_buttons3: "",

                                    theme_advanced_toolbar_location: "top",
                                    theme_advanced_toolbar_align: "left",
                                    theme_advanced_statusbar_location: "bottom",
                                    theme_advanced_resizing: true
                                });
                                /* ]]> */
                            </script>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- START KNOWLEDGEBASE SUGGEST -->
                <?php
                if ($hesk_settings['kb_enable'] && $hesk_settings['kb_recommendanswers']) {
                    ?>
                    <div id="kb_suggestions" style="display:none">
                        <br/>&nbsp;<br/>
                        <img src="img/loading.gif" width="24" height="24" alt="" border="0"
                             style="vertical-align:text-bottom"/> <i><?php echo $hesklang['lkbs']; ?></i>
                    </div>

                    <script language="Javascript" type="text/javascript"><!--
                        hesk_suggestKB();
                        //-->
                    </script>
                    <?php
                }
                ?>
                <!-- END KNOWLEDGEBASE SUGGEST -->

                <!-- START CUSTOM AFTER -->
                <?php

                /* custom fields AFTER comments */

                foreach ($hesk_settings['custom_fields'] as $k => $v) {

                    if ($v['use'] && $v['place']) {
                        if ($modsForHesk_settings['custom_field_setting']) {
                            $v['name'] = $hesklang[$v['name']];
                        }

                        $v['req'] = $v['req'] ? '<span class="important">*</span>' : '';

                        if ($v['type'] == 'checkbox' || $v['type'] == 'multiselect') {
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
                                $formattedId = preg_replace("/[\s_]/", "-", $formattedId);

                                echo '<div class="form-group"><label class="col-sm-3 control-label">' . $v['name'] . ': ' . $v['req'] . '</label><div align="left" class="col-sm-9">';

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
                                $formattedId = preg_replace("/[\s_]/", "-", $formattedId);

                                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                                echo '<div class="form-group"><label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . ': ' . $v['req'] . '</label>
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
                                $formattedId = preg_replace("/[\s_]/", "-", $formattedId);

                                echo '<div class="form-group"><label class="col-sm-3 control-label">' . $v['name'] . ': ' . $v['req'] . '</label><div align="left" class="col-sm-9">';

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
                                $errorText = $required == 'required' ? 'data-error="' . htmlspecialchars($hesklang['this_field_is_required']) . '"' : '';
                                //Clean up multiple dashes or whitespaces
                                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                                $formattedId = preg_replace("/[\s_]/", "-", $formattedId);

                                $size = explode('#', $v['value']);
                                $size[0] = empty($size[0]) ? 5 : intval($size[0]);
                                $size[1] = empty($size[1]) ? 30 : intval($size[1]);

                                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                                echo '<div class="form-group">
                                <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . ': ' . $v['req'] . '</label>
					            <div class="col-sm-9"><textarea class="form-control" id="' . $formattedId . '" name="' . $k . '" rows="' . $size[0] . '" cols="' . $size[1] . '" ' . $cls . ' ' . $errorText . ' ' . $required . '>' . $k_value . '</textarea>
                                <div class="help-block with-errors"></div>
                                </div>';
                                break;

                            case 'multiselect':
                                //Clean up multiple dashes or whitespaces
                                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                                $formattedId = preg_replace("/[\s_]/", "-", $formattedId);

                                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                                echo '<div class="form-group"><label for="' . $v['name'] . '[]" class="col-sm-3 control-label">' . $v['name'] . ': ' . $v['req'] . '</label>
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
                                    <button type="button" class="btn btn-default" onclick="selectAll(\'' . $formattedId . '\')">Select All</button>
                                    <button type="button" class="btn btn-default" onclick="deselectAll(\'' . $formattedId . '\')">Deselect All</button>
                                </div></div></div>';
                                break;

                            case 'date':
                                //Clean up multiple dashes or whitespaces
                                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                                $formattedId = preg_replace("/[\s_]/", "-", $formattedId);

                                if (strlen($k_value) != 0) {
                                    $v['value'] = $k_value;
                                }

                                $cls = in_array($k, $_SESSION['iserror']) ? ' isError ' : '';

                                echo '
                                <div class="form-group">
                                    <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . ': ' . $v['req'] . '</label>
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
                                $formattedId = preg_replace("/[\s_]/", "-", $formattedId);

                                if (strlen($k_value) != 0) {
                                    $v['value'] = $k_value;
                                }

                                if ($v['value'] == 'cc' || $v['value'] == 'bcc') {
                                    // (b)cc isn't a valid email but is the "value" used by settings. Just remove it.
                                    $v['value'] = '';
                                }

                                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                                echo '<div class="form-group">
                                <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . ': ' . $v['req'] . '</label>
					            <div class="col-sm-9"><input type="email" class="form-control" id="' . $formattedId . '" name="' . $k . '" size="40" maxlength="' . $v['maxlen'] . '" value="' . $v['value'] . '" data-error="' . htmlspecialchars($hesklang['enter_valid_email']) . '" ' . $cls . ' ' . $required . '>
					            <div class="help-block with-errors"></div>
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
                                <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . ': ' . $v['req'] . '</label>
					            <div class="col-sm-9"><input type="text" class="form-control" id="' . $formattedId . '" name="' . $k . '" size="40" maxlength="' . $v['maxlen'] . '" value="' . $v['value'] . '" ' . $cls . ' readonly></div>
                                </div>';

                                break;

                            /* Default text input */
                            default:
                                $errorText = $required == 'required' ? 'data-error="' . htmlspecialchars($hesklang['this_field_is_required']) . '"' : '';
                                //Clean up multiple dashes or whitespaces
                                $formattedId = preg_replace("/[\s-]+/", " ", $v['name']);
                                $formattedId = preg_replace("/[\s_]/", "-", $formattedId);

                                if (strlen($k_value) != 0) {
                                    $v['value'] = $k_value;
                                }

                                $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                                echo '<div class="form-group">
                                <label for="' . $v['name'] . '" class="col-sm-3 control-label">' . $v['name'] . ': ' . $v['req'] . '</label>
					            <div class="col-sm-9"><input type="text" class="form-control" id="' . $formattedId . '" name="' . $k . '" size="40" maxlength="' . $v['maxlen'] . '" value="' . $v['value'] . '" ' . $cls . ' ' . $errorText . ' ' . $required . '>
					            <div class="help-block with-errors"></div>
                                </div>';
                        }
                    }
                }

                ?>
                <!-- END CUSTOM AFTER -->

                <?php
                /* attachments */
                if ($hesk_settings['attachments']['use']) {
                    ?>
                    <div class="form-group">
                        <label for="attachments" class="col-sm-3 control-label"><?php echo $hesklang['attachments']; ?>
                            :</label>

                        <div align="left" class="col-sm-9">
                            <?php
                            for ($i = 1; $i <= $hesk_settings['attachments']['max_number']; $i++) {
                                $cls = ($i == 1 && in_array('attachments', $_SESSION['iserror'])) ? ' class="isError" ' : '';
                                echo '<input type="file" name="attachment[' . $i . ']" size="50" ' . $cls . ' /><br />';
                            }
                            ?>
                            <a href="file_limits.php" target="_blank"
                               onclick="Javascript:hesk_window('file_limits.php',250,500);return false;"><?php echo $hesklang['ful']; ?></a>
                        </div>
                    </div>
                    <?php
                }

                if ($hesk_settings['question_use'] || $hesk_settings['secimg_use'])
                {
                ?>

                <!-- Security checks -->
                <?php
                if ($hesk_settings['question_use']) {
                    ?>
                    <div class="form-group">
                        <label for="question" class="col-sm-3 control-label"><?php echo $hesklang['verify_q']; ?> <span
                                class="important">*</span></label>

                        <?php
                        $value = '';
                        if (isset($_SESSION['c_question'])) {
                            $value = stripslashes(hesk_input($_SESSION['c_question']));
                        }
                        $cls = in_array('question', $_SESSION['iserror']) ? ' class="isError" ' : '';
                        echo '<div class="col-md-9">' . $hesk_settings['question_ask'] . '<br /><input class="form-control" id="question" type="text" name="question" size="20" value="' . $value . '" ' . $cls . ' /></div>';
                        ?>
                    </div>
                    <?php
                }

                if ($hesk_settings['secimg_use'])
                {
                ?>
                <div class="form-group">
                    <label for="secimage" class="col-sm-3 control-label"><?php echo $hesklang['verify_i']; ?> <span
                            class="important">*</span></label>
                    <?php
                    // SPAM prevention verified for this session
                    if (isset($_SESSION['img_verified'])) {
                        echo '<img src="' . HESK_PATH . 'img/success.png" width="16" height="16" border="0" alt="" style="vertical-align:text-bottom" /> ' . $hesklang['vrfy'];
                    } // Not verified yet, should we use Recaptcha?
                    elseif ($hesk_settings['recaptcha_use'] == 1) {
                        ?>
                        <script type="text/javascript">
                            var RecaptchaOptions = {
                                theme: '<?php echo ( isset($_SESSION['iserror']) && in_array('mysecnum',$_SESSION['iserror']) ) ? 'red' : 'white'; ?>',
                                custom_translations: {
                                    visual_challenge: "<?php echo hesk_slashJS($hesklang['visual_challenge']); ?>",
                                    audio_challenge: "<?php echo hesk_slashJS($hesklang['audio_challenge']); ?>",
                                    refresh_btn: "<?php echo hesk_slashJS($hesklang['refresh_btn']); ?>",
                                    instructions_visual: "<?php echo hesk_slashJS($hesklang['instructions_visual']); ?>",
                                    instructions_context: "<?php echo hesk_slashJS($hesklang['instructions_context']); ?>",
                                    instructions_audio: "<?php echo hesk_slashJS($hesklang['instructions_audio']); ?>",
                                    help_btn: "<?php echo hesk_slashJS($hesklang['help_btn']); ?>",
                                    play_again: "<?php echo hesk_slashJS($hesklang['play_again']); ?>",
                                    cant_hear_this: "<?php echo hesk_slashJS($hesklang['cant_hear_this']); ?>",
                                    incorrect_try_again: "<?php echo hesk_slashJS($hesklang['incorrect_try_again']); ?>",
                                    image_alt_text: "<?php echo hesk_slashJS($hesklang['image_alt_text']); ?>"
                                }
                            };
                        </script>
                        <div class="col-md-9">
                            <?php
                            require(HESK_PATH . 'inc/recaptcha/recaptchalib.php');
                            echo recaptcha_get_html($hesk_settings['recaptcha_public_key'], null, true);
                            ?>
                        </div>
                    <?php
                    }
                    // Use reCaptcha API v2?
                    elseif ($hesk_settings['recaptcha_use'] == 2)
                    {
                    ?>
                        <div class="col-md-9">
                            <div class="g-recaptcha"
                                 data-sitekey="<?php echo $hesk_settings['recaptcha_public_key']; ?>"></div>
                        </div>
                        <?php
                    }
                    // At least use some basic PHP generated image (better than nothing)
                    else {
                        $cls = in_array('mysecnum', $_SESSION['iserror']) ? ' class="isError" ' : '';

                        echo '<div align="left" class="col-sm-9">';

                        echo $hesklang['sec_enter'] . '<br />&nbsp;<br /><img src="print_sec_img.php?' . rand(10000, 99999) . '" width="150" height="40" alt="' . $hesklang['sec_img'] . '" title="' . $hesklang['sec_img'] . '" border="1" name="secimg" style="vertical-align:text-bottom" /> ' .
                            '<a href="javascript:void(0)" onclick="javascript:document.form1.secimg.src=\'print_sec_img.php?\'+ ( Math.floor((90000)*Math.random()) + 10000);"><img src="img/reload.png" height="24" width="24" alt="' . $hesklang['reload'] . '" title="' . $hesklang['reload'] . '" border="0" style="vertical-align:text-bottom" /></a>' .
                            '<br />&nbsp;<br /><input type="text" name="mysecnum" size="20" maxlength="5" ' . $cls . ' />';
                    }
                    echo '</div></div>';
                    }
                    ?>

                    <?php
                    }

                    if ($modsForHesk_settings['request_location']):
                        ?>

                        <div class="form-group">
                            <label for="location"
                                   class="col-md-3 control-label"><?php echo $hesklang['location_colon']; ?></label>

                            <div class="col-sm-9">
                                <p id="console"><?php echo $hesklang['requesting_location_ellipsis']; ?></p>

                                <div id="map" style="height: 300px; display:none">
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <?php
                    endif;

                    if ($hesk_settings['submit_notice']) {
                        ?>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <b><?php echo $hesklang['before_submit']; ?></b>
                                    <ul>
                                        <li><?php echo $hesklang['all_info_in']; ?>.</li>
                                        <li><?php echo $hesklang['all_error_free']; ?>.</li>
                                    </ul>


                                    <b><?php echo $hesklang['we_have']; ?>:</b>
                                    <ul>
                                        <li><?php echo hesk_htmlspecialchars($_SERVER['REMOTE_ADDR']) . ' ' . $hesklang['recorded_ip']; ?></li>
                                        <li><?php echo $hesklang['recorded_time']; ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-9 col-md-offset-3">
                                <input type="hidden" id="latitude" name="latitude" value="E-0">
                                <input type="hidden" id="longitude" name="longitude" value="E-0">
                                <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>">
                                <input type="submit" value="<?php echo $hesklang['sub_ticket']; ?>"
                                       class="btn btn-default">
                            </div>
                        </div>
                        <script>
                            $('#screen-resolution-height').prop('value', screen.height);
                            $('#screen-resolution-width').prop('value', screen.width);
                        </script>

                    <?php
                    } // End IF submit_notice
                    else {
                    ?>
                        <div class=" row">
                            <div class="col-md-9 col-md-offset-3">
                                <input type="hidden" id="latitude" name="latitude" value="E-0">
                                <input type="hidden" id="longitude" name="longitude" value="E-0">
                                <input type="hidden" id="screen-resolution-height" name="screen_resolution_height">
                                <input type="hidden" id="screen-resolution-width" name="screen_resolution_width">
                                <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>">
                                <input class="btn btn-default" type="submit"
                                       value="<?php echo $hesklang['sub_ticket']; ?>">
                                <script>
                                    $('#screen-resolution-height').prop('value', screen.height);
                                    $('#screen-resolution-width').prop('value', screen.width);
                                </script>
                            </div>
                        </div>

                        <?php
                    } // End ELSE submit_notice
                    ?>
                    <script>
                        function validateRichText() {
                            var content = tinyMCE.get("message").getContent();
                            if (content == '') {
                                $('#message-help-block').text("This can't be empty");
                                $('#message-group').addClass('has-error');
                                return false;
                            }
                            return true;
                        }
                    </script>

                    <!-- Do not delete or modify the code below, it is used to detect simple SPAM bots -->
                    <input type="hidden" name="hx" value="3"/><input type="hidden" name="hy" value=""/>
                    <!-- >
                    <input type="text" name="phone" value="3" />
                    < -->

            </form>
        </div>
    </div>
    <?php if ($columnWidth == 'col-md-10 col-md-offset-1'): ?>
    <div class="col-md-1">&nbsp;</div></div>
<?php endif; ?>
    <!-- END FORM -->


    <?php

// Request for the users location if enabled
    if ($modsForHesk_settings['request_location']) {
        echo '
    <script>
        requestUserLocation("' . $hesklang['your_current_location'] . '", "' . $hesklang['unable_to_determine_location'] . '");
    </script>
    ';
    }

    hesk_cleanSessionVars('iserror');
    hesk_cleanSessionVars('isnotice');

} // End print_add_ticket()


function print_start()
{
	global $hesk_settings, $hesklang;

	if ($hesk_settings['kb_enable'])
	{
        require(HESK_PATH . 'inc/knowledgebase_functions.inc.php');
	}

    // Connect to database
    hesk_dbConnect();

	/* Print header */
	require_once(HESK_PATH . 'inc/header.inc.php');

	?>

<ol class="breadcrumb">
  <li><a href="<?php echo $hesk_settings['site_url']; ?>"><?php echo $hesk_settings['site_title']; ?></a></li>
  <li class="active"><?php echo $hesk_settings['hesk_title']; ?></li>
</ol>
    <?php
    // Service messages
    $res = hesk_dbQuery('SELECT `title`, `message`, `style`, `icon` FROM `'.hesk_dbEscape($hesk_settings['db_pfix'])."service_messages` WHERE `type`='0' ORDER BY `order` ASC");
    if (hesk_dbNumRows($res) > 0)
    {
    ?>
    <div class="row">
        <div class="col-md-12">
            <?php
            while ($sm=hesk_dbFetchAssoc($res))
            {
                hesk_service_message($sm);
            }
            ?>
        </div>
    </div>
    <?php } ?>
	<div class="row">
		<div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading"><?php echo $hesklang['view_ticket']; ?></div>
                <div class="panel-body">
                    <form role="form" class="viewTicketSidebar" action="ticket.php" method="get" name="form2">
                        <div class="form-group">
                            <br/>
                            <label for="ticketID"><?php echo $hesklang['ticket_trackID']; ?>:</label>
                            <input type="text" class="form-control" name="track" id="ticketID" maxlength="20" size="35" value="" placeholder="<?php echo htmlspecialchars($hesklang['ticket_trackID']); ?>">
                        </div>
                        <?php
                        $tmp = '';
                        if ($hesk_settings['email_view_ticket'])
                        {
                            $tmp = 'document.form1.email.value=document.form2.e.value;';
                        ?>
                        <div class="form-group">
                            <label for="emailAddress"><?php echo $hesklang['email']; ?>:</label>
                            <?php
                            $my_email = '';
                            $do_remember = '';
                            if (isset($_COOKIE['hesk_myemail']))
                            {
                                $my_email = $_COOKIE['hesk_myemail'];
                                $do_remember = 'checked';
                            }
                            ?>
                            <input type="text" class="form-control" name="e" id="emailAddress" size="35" value="<?php echo $my_email; ?>" placeholder="<?php echo htmlspecialchars($hesklang['email']); ?>"/>
                        </div>
                        <div class="checkbox">
                            <label for="r">
                                <input type="checkbox" name="r" value="Y" <?php echo $do_remember; ?>> <?php echo $hesklang['rem_email']; ?>
                            </label>
                        </div>
                        <?php
                        }
                        ?>
                        <input type="submit" value="<?php echo $hesklang['view_ticket']; ?>" class="btn btn-default" /><input type="hidden" name="Refresh" value="<?php echo rand(10000,99999); ?>"><input type="hidden" name="f" value="1">
                    </form>
                </div>
            </div>
		</div>
		<div class="col-md-8">
				<?php
				// Print small search box
				if ($hesk_settings['kb_enable'])
				{
					hesk_kbSearchSmall();
					hesk_kbSearchLarge();
				}
				else
				{
					echo '&nbsp;';
				}
				?>
            <div class="row default-row-margins">
                <div class="col-sm-6 col-xs-12">
                    <a href="index.php?a=add" class="button-link">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-xs-1">
                                        <img src="img/newTicket.png" alt="<?php echo $hesklang['sub_support']; ?>">
                                    </div>
                                    <div class="col-xs-11">
                                        <b><?php echo $hesklang['sub_support']; ?></b><br>
                                        <?php echo $hesklang['open_ticket']; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <a href="ticket.php" class="button-link">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-xs-1">
                                        <img src="img/viewTicket.png" alt="<?php echo $hesklang['view_existing']; ?>">
                                    </div>
                                    <div class="col-xs-11">
                                        <b><?php echo $hesklang['view_existing']; ?></b><br>
                                        <?php echo $hesklang['vet']; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <?php
            if ($hesk_settings['kb_enable'])
            {
                ?>
                <div class="row default-row-margins">
                    <div class="col-sm-6 col-xs-12">
                        <a href="knowledgebase.php" class="button-link">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-xs-1">
                                            <img src="img/knowledgebase.png" alt="<?php echo $hesklang['kb_text']; ?>">
                                        </div>
                                        <div class="col-xs-11">
                                            <b><?php echo $hesklang['kb_text']; ?></b><br>
                                            <?php echo $hesklang['viewkb']; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            <?php
                hesk_kbTopArticles($hesk_settings['kb_index_popart']);
                hesk_kbLatestArticles($hesk_settings['kb_index_latest']);
            }?>
		</div>
	</div>
	<div class="blankSpace"></div>
	<div class="footerWithBorder"></div>
	<div class="blankSpace"></div>
</div>

<?php
	// Show a link to admin panel?
	if ($hesk_settings['alink'])
	{
		?>
		<p class="text-center"><a href="<?php echo $hesk_settings['admin_dir']; ?>/" ><?php echo $hesklang['ap']; ?></a></p>
		<?php
	}

} // End print_start()


function forgot_tid()
{
global $hesk_settings, $hesklang, $modsForHesk_settings;

require(HESK_PATH . 'inc/email_functions.inc.php');

/* Get ticket(s) from database */
hesk_dbConnect();

$email = hesk_validateEmail(hesk_POST('email'), 'ERR', 0) or hesk_process_messages($hesklang['enter_valid_email'], 'ticket.php?remind=1');

if (isset($_POST['open_only'])) {
    $hesk_settings['open_only'] = $_POST['open_only'] == 1 ? 1 : 0;
}

/* Prepare ticket statuses */
$myStatusSQL = hesk_dbQuery("SELECT `ID`, `Key` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses`");
$my_status = array();
while ($myStatusRow = hesk_dbFetchAssoc($myStatusSQL)) {
    $my_status[$myStatusRow['ID']] = $hesklang[$myStatusRow['Key']];
}

// Get tickets from the database
$res = hesk_dbQuery('SELECT * FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'tickets` FORCE KEY (`statuses`) WHERE ' . ($hesk_settings['open_only'] ? "`status` IN (SELECT `ID` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` WHERE `IsClosed` = 0) AND " : '') . ' ' . hesk_dbFormatEmail($email) . ' ORDER BY `status` ASC, `lastchange` DESC ');

$num = hesk_dbNumRows($res);
if ($num < 1) {
    if ($hesk_settings['open_only']) {
        hesk_process_messages($hesklang['noopen'], 'ticket.php?remind=1&e=' . $email);
    } else {
        hesk_process_messages($hesklang['tid_not_found'], 'ticket.php?remind=1&e=' . $email);
    }
}

$tid_list = '';
$html_tid_list = '<ul>';
$name = '';

$email_param = $hesk_settings['email_view_ticket'] ? '&e=' . rawurlencode($email) : '';

while ($my_ticket = hesk_dbFetchAssoc($res)) {
    $name = $name ? $name : hesk_msgToPlain($my_ticket['name'], 1, 0);
    $tid_list .= "
        $hesklang[trackID]: " . $my_ticket['trackid'] . "
        $hesklang[subject]: " . hesk_msgToPlain($my_ticket['subject'], 1, 0) . "
        $hesklang[status]: " . $my_status[$my_ticket['status']] . "
        $hesk_settings[hesk_url]/ticket.php?track={$my_ticket['trackid']}{$email_param}
        ";

    $html_tid_list .= "<li>
        $hesklang[trackID]: " . $my_ticket['trackid'] . " <br>
        $hesklang[subject]: " . hesk_msgToPlain($my_ticket['subject'], 1, 0) . " <br>
        $hesklang[status]: " . $my_status[$my_ticket['status']] . " <br>
        $hesk_settings[hesk_url]/ticket.php?track={$my_ticket['trackid']}{$email_param}
        </li>";
}
$html_tid_list .= '</ul>';

/* Get e-mail message for customer */
$msg = hesk_getEmailMessage('forgot_ticket_id', '', $modsForHesk_settings, 0, 0, 1);
$msg = processEmail($msg, $name, $num, $tid_list);

// Get HTML message for customer
$htmlMsg = hesk_getHtmlMessage('forgot_ticket_id', '', $modsForHesk_settings, 0, 0, 1);
$htmlMsg = processEmail($htmlMsg, $name, $num, $html_tid_list);


$subject = hesk_getEmailSubject('forgot_ticket_id');

/* Send e-mail */
hesk_mail($email, $subject, $msg, $htmlMsg, $modsForHesk_settings);

/* Show success message */
$tmp = '<b>' . $hesklang['tid_sent'] . '!</b>';
$tmp .= '<br />&nbsp;<br />' . $hesklang['tid_sent2'] . '.';
$tmp .= '<br />&nbsp;<br />' . $hesklang['check_spambox'];
hesk_process_messages($tmp, 'ticket.php?e=' . $email, 'SUCCESS');
exit();

/* Print header */
$hesk_settings['tmp_title'] = $hesk_settings['hesk_title'] . ' - ' . $hesklang['tid_sent'];
require_once(HESK_PATH . 'inc/header.inc.php');
?>

<ol class="breadcrumb">
    <li><a href="<?php echo $hesk_settings['site_url']; ?>"><?php echo $hesk_settings['site_title']; ?></a></li>
    <li><a href="<?php echo $hesk_settings['hesk_url']; ?>"><?php echo $hesk_settings['hesk_title']; ?></a></li>
    <li class="active"><?php echo $hesklang['tid_sent']; ?></li>
</ol>
<tr>
    <td>

        <?php

        } // End forgot_tid()

        function processEmail($msg, $name, $num, $tid_list)
        {
            global $hesk_settings;

            $msg = str_replace('%%NAME%%', $name, $msg);
            $msg = str_replace('%%NUM%%', $num, $msg);
            $msg = str_replace('%%LIST_TICKETS%%', $tid_list, $msg);
            $msg = str_replace('%%SITE_TITLE%%', hesk_msgToPlain($hesk_settings['site_title'], 1), $msg);
            $msg = str_replace('%%SITE_URL%%', $hesk_settings['site_url'], $msg);
            return $msg;
        }

        ?>
