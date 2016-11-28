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
define('PAGE_TITLE', 'ADMIN_SERVICE_MESSAGES');
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

/* Check permissions for this feature */
hesk_checkPermission('can_service_msg');

// Define required constants
define('LOAD_TABS', 1);
define('WYSIWYG', 1);

// What should we do?
if ($action = hesk_REQUEST('a')) {
    if ($action == 'edit_sm') {
        edit_sm();
    } elseif (defined('HESK_DEMO')) {
        hesk_process_messages($hesklang['ddemo'], 'service_messages.php', 'NOTICE');
    } elseif ($action == 'new_sm') {
        new_sm();
    } elseif ($action == 'save_sm') {
        save_sm();
    } elseif ($action == 'order_sm') {
        order_sm();
    } elseif ($action == 'remove_sm') {
        remove_sm();
    }
}

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>
<div class="content-wrapper">
    <section class="content">
    <div class="box">
        <div class="box-body">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs" role="tablist">
                    <?php
                    // Show a link to banned_emails.php if user has permission to do so
                    if (hesk_checkPermission('can_ban_emails', 0)) {
                        echo '
            <li role="presentation">
                <a title="' . $hesklang['banemail'] . '" href="banned_emails.php">' . $hesklang['banemail'] . '</a>
            </li>';
                    }
                    if (hesk_checkPermission('can_ban_ips', 0)) {
                        echo '
            <li role="presentation">
                <a title="' . $hesklang['banip'] . '" href="banned_ips.php">' . $hesklang['banip'] . '</a>
            </li>';
                    }
                    ?>
                    <li role="presentation" class="active">
                        <a href="#"><?php echo $hesklang['sm_title']; ?> <i class="fa fa-question-circle settingsquestionmark"
                                                                            onclick="javascript:alert('<?php echo hesk_makeJsString($hesklang['sm_intro']); ?>')"></i></a>
                    </li>
                    <?php
                    // Show a link to email tpl management if user has permission to do so
                    if (hesk_checkPermission('can_man_email_tpl', 0)) {
                        echo '
            <li role="presentation">
                <a title="' . $hesklang['email_templates'] . '" href="manage_email_templates.php">' . $hesklang['email_templates'] . '</a>
            </li>
            ';
                    }
                    if (hesk_checkPermission('can_man_ticket_statuses', 0)) {
                        echo '
            <li role="presentation">
                <a title="' . $hesklang['statuses'] . '" href="manage_statuses.php">' . $hesklang['statuses'] . '</a>
            </li>
            ';
                    }
                    // Show a link to custom_fields.php if user has permission to do so
                    if ( hesk_checkPermission('can_man_settings',0) ) {
                        echo '
            <li role="presentation">
                <a title="' . $hesklang['tab_4'] . '" href="custom_fields.php">' . $hesklang['tab_4'] . '</a>
            </li>
            ';
                    }
                    ?>
                </ul>
                <div class="tab-content summaryList tabPadding">
                    <div class="row">
                        <div class="col-sm-12">
                            <?php
                            /* This will handle error, success and notice messages */
                            hesk_handle_messages();

                            if (isset($_SESSION['new_sm']) && !isset($_SESSION['edit_sm'])) {
                                $_SESSION['new_sm'] = hesk_stripArray($_SESSION['new_sm']);
                            }

                            if (isset($_SESSION['preview_sm'])) {
                                hesk_service_message($_SESSION['new_sm']);
                            }

                            if ($hesk_settings['kb_wysiwyg']) {
                                ?>
                                <script type="text/javascript">
                                    tinyMCE.init({
                                        mode: "exact",
                                        elements: "content",
                                        theme: "advanced",
                                        convert_urls: false,
                                        gecko_spellcheck: true,

                                        theme_advanced_buttons1: "cut,copy,paste,|,undo,redo,|,formatselect,fontselect,fontsizeselect,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull",
                                        theme_advanced_buttons2: "sub,sup,|,charmap,|,bullist,numlist,|,outdent,indent,insertdate,inserttime,preview,|,forecolor,backcolor,|,hr,removeformat,visualaid,|,link,unlink,anchor,image,cleanup,code",
                                        theme_advanced_buttons3: "",

                                        theme_advanced_toolbar_location: "top",
                                        theme_advanced_toolbar_align: "left",
                                        theme_advanced_statusbar_location: "bottom",
                                        theme_advanced_resizing: true
                                    });
                                </script>
                                <?php
                            }
                            ?>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4><?php echo $hesklang['ex_sm']; ?></h4>
                                </div>
                                <div class="panel-body">
                                    <?php

                                    // Get service messages from database
                                    $res = hesk_dbQuery('SELECT * FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'service_messages` ORDER BY `order` ASC');
                                    $num = hesk_dbNumRows($res);

                                    if ($num < 1) {
                                        echo '<p>' . $hesklang['no_sm'] . '</p>';
                                    } else {
                                        // List of staff
                                        if (!isset($admins)) {
                                            $admins = array();
                                            $res2 = hesk_dbQuery("SELECT `id`,`name` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users`");
                                            while ($row = hesk_dbFetchAssoc($res2)) {
                                                $admins[$row['id']] = $row['name'];
                                            }
                                        }

                                        ?>
                                        <table class="table table-hover">
                                            <thead>
                                            <tr>
                                                <th><?php echo $hesklang['sm_mtitle']; ?></th>
                                                <th><?php echo $hesklang['sm_author']; ?></th>
                                                <th><?php echo $hesklang['sm_type']; ?></th>
                                                <th>&nbsp;<?php echo $hesklang['opt']; ?>&nbsp;</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            $j = 1;
                                            $k = 1;

                                            while ($sm = hesk_dbFetchAssoc($res)) {
                                                $faIcon = $sm['icon'];
                                                switch ($sm['style']) {
                                                    case 1:
                                                        $sm_style = "alert alert-success";
                                                        break;
                                                    case 2:
                                                        $sm_style = "alert alert-info";
                                                        break;
                                                    case 3:
                                                        $sm_style = "alert alert-warning";
                                                        break;
                                                    case 4:
                                                        $sm_style = "alert alert-danger";
                                                        break;
                                                    default:
                                                        $sm_style = "none";
                                                }

                                                $type = $sm['type'] ? $hesklang['sm_draft'] : $hesklang['sm_published'];

                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="<?php echo $sm_style; ?>">
                                                            <i class="<?php echo $faIcon; ?>"></i>
                                                            <b><?php echo $sm['title']; ?></b>
                                                        </div>
                                                    </td>
                                                    <td><?php echo(isset($admins[$sm['author']]) ? $admins[$sm['author']] : $hesklang['e_udel']); ?></td>
                                                    <td><?php echo $type; ?></td>
                                                    <td>
                                                        <?php
                                                        if ($num > 1) {
                                                            if ($k == 1) {
                                                                ?>
                                                                <img src="../img/blank.gif" width="16" height="16" alt=""
                                                                     style="padding:3px;border:none;"/>
                                                                <a href="service_messages.php?a=order_sm&amp;id=<?php echo $sm['id']; ?>&amp;move=15&amp;token=<?php hesk_token_echo(); ?>">
                                                                    <i class="fa fa-arrow-down font-size-16p green"
                                                                       data-toggle="tooltip" data-placement="top"
                                                                       data-original-title="<?php echo $hesklang['move_dn']; ?>"></i></a>
                                                                <?php
                                                            } elseif ($k == $num) {
                                                                ?>
                                                                <a href="service_messages.php?a=order_sm&amp;id=<?php echo $sm['id']; ?>&amp;move=-15&amp;token=<?php hesk_token_echo(); ?>">
                                                                    <i class="fa fa-arrow-up font-size-16p green" data-toggle="tooltip"
                                                                       data-placement="top"
                                                                       data-original-title="<?php echo $hesklang['move_up']; ?>"></i></a>
                                                                <img src="../img/blank.gif" width="16" height="16" alt=""
                                                                     style="padding:3px;border:none;"/>
                                                                <?php
                                                            } else {
                                                                ?>
                                                                <a href="service_messages.php?a=order_sm&amp;id=<?php echo $sm['id']; ?>&amp;move=-15&amp;token=<?php hesk_token_echo(); ?>">
                                                                    <i class="fa fa-arrow-up font-size-16p green" data-toggle="tooltip"
                                                                       data-placement="top"
                                                                       data-original-title="<?php echo $hesklang['move_up']; ?>"></i></a>
                                                                <a href="service_messages.php?a=order_sm&amp;id=<?php echo $sm['id']; ?>&amp;move=15&amp;token=<?php hesk_token_echo(); ?>">
                                                                    <i class="fa fa-arrow-down font-size-16p green"
                                                                       data-toggle="tooltip" data-placement="top"
                                                                       data-original-title="<?php echo $hesklang['move_dn']; ?>"></i></a>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                        <a href="service_messages.php?a=edit_sm&amp;id=<?php echo $sm['id']; ?>">
                                                            <i class="fa fa-pencil font-size-16p orange" data-toggle="tooltip"
                                                               data-placement="top"
                                                               data-original-title="<?php echo $hesklang['edit']; ?>"></i></a>
                                                        <a href="service_messages.php?a=remove_sm&amp;id=<?php echo $sm['id']; ?>&amp;token=<?php hesk_token_echo(); ?>"
                                                           onclick="return hesk_confirmExecute('<?php echo hesk_makeJsString($hesklang['del_sm']); ?>');">
                                                            <i class="fa fa-times font-size-16p red" data-toggle="tooltip"
                                                               data-placement="top"
                                                               data-original-title="<?php echo $hesklang['delete']; ?>"></i></a>&nbsp;
                                                    </td>
                                                </tr>
                                                <?php
                                                $j++;
                                                $k++;
                                            } // End while

                                            ?>
                                            </tbody>
                                        </table>
                                        <?php
                                    }

                                    ?>
                                </div>
                            </div>

                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4><a name="new_article"></a><?php echo hesk_SESSION('edit_sm') ? $hesklang['edit_sm'] : $hesklang['new_sm']; ?></h4>
                                </div>
                                <div class="panel-body">
                                    <form action="service_messages.php" method="post" name="form1" role="form" class="form-horizontal" data-toggle="validator">
                                        <div class="form-group">
                                            <label for="style"
                                                   class="col-md-2 control-label"><?php echo $hesklang['sm_style']; ?></label>

                                            <div class="col-md-2">
                                                <div class="radio alert pad-5" style="box-shadow: none; border-radius: 4px;">
                                                    <label>
                                                        <input type="radio" name="style" value="0" onclick="setIcon('')"
                                                            <?php if (!isset($_SESSION['new_sm']['style']) || (isset($_SESSION['new_sm']['style']) && $_SESSION['new_sm']['style'] == 0)) {
                                                                echo 'checked';
                                                            } ?>>
                                                        <?php echo $hesklang['sm_none']; ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="radio alert alert-success pad-5">
                                                    <label style="margin-top: -5px">
                                                        <input type="radio" name="style" value="1"
                                                               onclick="setIcon('fa fa-check-circle')"
                                                            <?php if (isset($_SESSION['new_sm']['style']) && $_SESSION['new_sm']['style'] == 1) {
                                                                echo 'checked';
                                                            } ?>>
                                                        <?php echo $hesklang['sm_success']; ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="radio alert alert-info pad-5" onclick="setIcon('fa fa-comment')">
                                                    <label style="margin-top: -5px">
                                                        <input type="radio" name="style" value="2"
                                                            <?php if (isset($_SESSION['new_sm']['style']) && $_SESSION['new_sm']['style'] == 2) {
                                                                echo 'checked';
                                                            } ?>>
                                                        <?php echo $hesklang['sm_info']; ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="radio alert alert-warning pad-5"
                                                     onclick="setIcon('fa fa-exclamation-triangle')">
                                                    <label style="margin-top: -5px">
                                                        <input type="radio" name="style" value="3"
                                                            <?php if (isset($_SESSION['new_sm']['style']) && $_SESSION['new_sm']['style'] == 3) {
                                                                echo 'checked';
                                                            } ?>>
                                                        <?php echo $hesklang['sm_notice']; ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="radio alert alert-danger pad-5" onclick="setIcon('fa fa-times-circle')">
                                                    <label style="margin-top: -5px">
                                                        <input type="radio" name="style" value="4"
                                                            <?php if (isset($_SESSION['new_sm']['style']) && $_SESSION['new_sm']['style'] == 4) {
                                                                echo 'checked';
                                                            } ?> >
                                                        <?php echo $hesklang['sm_error']; ?>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="icon" class="col-md-2 control-label"><?php echo $hesklang['sm_icon']; ?></label>
                                            <?php
                                            $icon = '';
                                            if (isset($_SESSION['new_sm']['icon'])) {
                                                $icon = $_SESSION['new_sm']['icon'];
                                            }
                                            ?>
                                            <div class="col-md-10">
                                                <p style="display:none" id="no-icon"><?php echo $hesklang['sm_no_icon']; ?></p>

                                                <p style="display:none" id="search-icon"><?php echo $hesklang['sm_search_icon']; ?></p>

                                                <p style="display:none"
                                                   id="footer-icon"><?php echo $hesklang['sm_iconpicker_footer_label']; ?></p>

                                                <div name="icon" class="btn btn-default iconpicker-container" data-toggle="iconpicker"
                                                     data-icon="<?php echo $icon; ?>"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="type" class="col-md-2 control-label"><?php echo $hesklang['sm_type']; ?></label>

                                            <div class="col-md-2">
                                                <div class="radio pad-5">
                                                    <label>
                                                        <input type="radio" name="type" value="0"
                                                            <?php if (!isset($_SESSION['new_sm']['type']) || (isset($_SESSION['new_sm']['type']) && $_SESSION['new_sm']['type'] == 0)) {
                                                                echo 'checked';
                                                            } ?> >
                                                        <?php echo $hesklang['sm_published']; ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="radio pad-5">
                                                    <label>
                                                        <input type="radio" name="type" value="1"
                                                            <?php if (isset($_SESSION['new_sm']['type']) && $_SESSION['new_sm']['type'] == 1) {
                                                                echo 'checked';
                                                            } ?> >
                                                        <?php echo $hesklang['sm_draft']; ?>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="title"
                                                   class="col-md-2 control-label"><?php echo $hesklang['sm_mtitle']; ?></label>

                                            <div class="col-md-10">
                                                <input class="form-control"
                                                       placeholder="<?php echo htmlspecialchars($hesklang['sm_mtitle']); ?>"
                                                       type="text" name="title" size="70" maxlength="255"
                                                    <?php if (isset($_SESSION['new_sm']['title'])) {
                                                        echo 'value="' . $_SESSION['new_sm']['title'] . '"';
                                                    } ?> data-error="<?php echo htmlspecialchars($hesklang['sm_e_title']); ?>" required>
                                                <div class="help-block with-errors"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="message"
                                                   class="col-md-2 control-label"><?php echo $hesklang['sm_msg']; ?></label>

                                            <div class="col-md-10">
                                    <textarea placeholder="<?php echo htmlspecialchars($hesklang['sm_msg']); ?>"
                                              class="form-control" name="message" rows="25" cols="70" id="content">
                                        <?php if (isset($_SESSION['new_sm']['message'])) {
                                            echo $_SESSION['new_sm']['message'];
                                        } ?>
                                    </textarea>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?php echo isset($_SESSION['edit_sm']) ? '<input type="hidden" name="a" value="save_sm" /><input type="hidden" name="id" value="' . intval($_SESSION['new_sm']['id']) . '" />' : '<input type="hidden" name="a" value="new_sm" />'; ?>
                                            <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>"/>

                                            <div class="col-md-10 col-md-offset-2">
                                                <div class="btn-group" role="group">
                                                    <input type="submit" name="sm_save" value="<?php echo $hesklang['sm_save']; ?>"
                                                           class="btn btn-primary">
                                                    <input type="submit" name="sm_preview"
                                                           value="<?php echo $hesklang['sm_preview']; ?>" class="btn btn-default">
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</div>

<?php

hesk_cleanSessionVars(array('new_sm', 'preview_sm', 'edit_sm'));

require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


/*** START FUNCTIONS ***/


function save_sm()
{
    global $hesk_settings, $hesklang, $listBox;
    global $hesk_error_buffer;

    // A security check
    # hesk_token_check('POST');

    $hesk_error_buffer = array();

    // Get service messageID
    $id = intval(hesk_POST('id')) or hesk_error($hesklang['sm_e_id']);

    $style = intval(hesk_POST('style', 0));
    if ($style > 4 || $style < 0) {
        $style = 0;
    }

    $type = empty($_POST['type']) ? 0 : 1;
    $icon = hesk_POST('icon');
    $title = hesk_input(hesk_POST('title')) or $hesk_error_buffer[] = $hesklang['sm_e_title'];
    $message = hesk_getHTML(hesk_POST('message'));
	
	// Clean the HTML code
	require(HESK_PATH . 'inc/htmlpurifier/HeskHTMLPurifier.php');
	$purifier = new HeskHTMLPurifier($hesk_settings['cache_dir']);
	$message = $purifier->heskPurify($message);

    // Any errors?
    if (count($hesk_error_buffer)) {
        $_SESSION['edit_sm'] = true;

        $_SESSION['new_sm'] = array(
            'id' => $id,
            'style' => $style,
            'type' => $type,
            'title' => $title,
            'icon' => $icon,
            'message' => hesk_input(hesk_POST('message')),
        );

        $tmp = '';
        foreach ($hesk_error_buffer as $error) {
            $tmp .= "<li>$error</li>\n";
        }
        $hesk_error_buffer = $tmp;

        $hesk_error_buffer = $hesklang['rfm'] . '<br /><br /><ul>' . $hesk_error_buffer . '</ul>';
        hesk_process_messages($hesk_error_buffer, 'service_messages.php');
    }

    // Just preview the message?
    if (isset($_POST['sm_preview'])) {
        $_SESSION['preview_sm'] = true;
        $_SESSION['edit_sm'] = true;

        $_SESSION['new_sm'] = array(
            'id' => $id,
            'style' => $style,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
        );

        header('Location: service_messages.php');
        exit;
    }

    // Update the service message in the database
    hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages` SET
	`author` = '" . intval($_SESSION['id']) . "',
	`title` = '" . hesk_dbEscape($title) . "',
	`message` = '" . hesk_dbEscape($message) . "',
	`style` = '{$style}',
	`type` = '{$type}',
	`icon` = '{$icon}'
	WHERE `id`={$id}");

    $_SESSION['smord'] = $id;
    hesk_process_messages($hesklang['sm_mdf'], 'service_messages.php', 'SUCCESS');

} // End save_sm()


function edit_sm()
{
    global $hesk_settings, $hesklang;

    // Get service messageID
    $id = intval(hesk_GET('id')) or hesk_error($hesklang['sm_e_id']);

    // Get details from the database
    $res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages` WHERE `id`={$id} LIMIT 1");
    if (hesk_dbNumRows($res) != 1) {
        hesk_error($hesklang['sm_not_found']);
    }
    $sm = hesk_dbFetchAssoc($res);

    $_SESSION['new_sm'] = $sm;
    $_SESSION['edit_sm'] = true;

} // End edit_sm()


function order_sm()
{
    global $hesk_settings, $hesklang;

    // A security check
    hesk_token_check();

    // Get ID and move parameters
    $id = intval(hesk_GET('id')) or hesk_error($hesklang['sm_e_id']);
    $move = intval(hesk_GET('move'));
    $_SESSION['smord'] = $id;

    // Update article details
    hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages` SET `order`=`order`+" . intval($move) . " WHERE `id`={$id}");

    // Update order of all service messages
    update_sm_order();

    // Finish
    header('Location: service_messages.php');
    exit();

} // End order_sm()


function update_sm_order()
{
    global $hesk_settings, $hesklang;

    // Get list of current service messages
    $res = hesk_dbQuery("SELECT `id` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages` ORDER BY `order` ASC");

    // Update database
    $i = 10;
    while ($sm = hesk_dbFetchAssoc($res)) {
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages` SET `order`=" . intval($i) . " WHERE `id`='" . intval($sm['id']) . "'");
        $i += 10;
    }

    return true;

} // END update_sm_order()


function remove_sm()
{
    global $hesk_settings, $hesklang;

    // A security check
    hesk_token_check();

    // Get ID
    $id = intval(hesk_GET('id')) or hesk_error($hesklang['sm_e_id']);

    // Delete the service message
    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages` WHERE `id`={$id}");

    // Were we successful?
    if (hesk_dbAffectedRows() == 1) {
        hesk_process_messages($hesklang['sm_deleted'], './service_messages.php', 'SUCCESS');
    } else {
        hesk_process_messages($hesklang['sm_not_found'], './service_messages.php');
    }

} // End remove_sm()


function new_sm()
{
    global $hesk_settings, $hesklang, $listBox;
    global $hesk_error_buffer;

    // A security check
    # hesk_token_check('POST');

    $hesk_error_buffer = array();

    $style = intval(hesk_POST('style', 0));
    if ($style > 4 || $style < 0) {
        $style = 0;
    }

    $type = empty($_POST['type']) ? 0 : 1;
    $icon = hesk_POST('icon');
    $title = hesk_input(hesk_POST('title')) or $hesk_error_buffer[] = $hesklang['sm_e_title'];
    $message = hesk_getHTML(hesk_POST('message'));
	
	// Clean the HTML code
	require(HESK_PATH . 'inc/htmlpurifier/HeskHTMLPurifier.php');
	$purifier = new HeskHTMLPurifier($hesk_settings['cache_dir']);
	$message = $purifier->heskPurify($message);

    // Any errors?
    if (count($hesk_error_buffer)) {
        $_SESSION['new_sm'] = array(
            'style' => $style,
            'type' => $type,
            'title' => $title,
            'icon' => $icon,
            'message' => hesk_input(hesk_POST('message')),
        );

        $tmp = '';
        foreach ($hesk_error_buffer as $error) {
            $tmp .= "<li>$error</li>\n";
        }
        $hesk_error_buffer = $tmp;

        $hesk_error_buffer = $hesklang['rfm'] . '<br /><br /><ul>' . $hesk_error_buffer . '</ul>';
        hesk_process_messages($hesk_error_buffer, 'service_messages.php');
    }

    // Just preview the message?
    if (isset($_POST['sm_preview'])) {
        $_SESSION['preview_sm'] = true;

        $_SESSION['new_sm'] = array(
            'style' => $style,
            'type' => $type,
            'title' => $title,
            'icon' => $icon,
            'message' => $message,
        );

        header('Location: service_messages.php');
        exit;
    }

    // Get the latest service message order
    $res = hesk_dbQuery("SELECT `order` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages` ORDER BY `order` DESC LIMIT 1");
    $row = hesk_dbFetchRow($res);
    $my_order = intval($row[0]) + 10;

    // Insert service message into database
    hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages` (`author`,`title`,`message`,`style`,`type`,`order`, `icon`) VALUES (
    '" . intval($_SESSION['id']) . "',
    '" . hesk_dbEscape($title) . "',
    '" . hesk_dbEscape($message) . "',
    '{$style}',
    '{$type}',
    '{$my_order}',
    '{$icon}'
    )");

    $_SESSION['smord'] = hesk_dbInsertID();
    hesk_process_messages($hesklang['sm_added'], 'service_messages.php', 'SUCCESS');

} // End new_sm()

?>
