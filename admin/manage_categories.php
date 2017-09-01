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
define('PAGE_TITLE', 'ADMIN_CATEGORIES');
define('MFH_PAGE_LAYOUT', 'TOP_ONLY');
define('EXTRA_JS', '<script src="'.HESK_PATH.'internal-api/js/manage-categories.js"></script>');

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
hesk_checkPermission('can_man_cat');

// Possible priorities
$priorities = array(
    3 => array('value' => 3, 'text' => $hesklang['low'], 'formatted' => $hesklang['low']),
    2 => array('value' => 2, 'text' => $hesklang['medium'], 'formatted' => '<span class="medium">' . $hesklang['medium'] . '</span>'),
    1 => array('value' => 1, 'text' => $hesklang['high'], 'formatted' => '<span class="important">' . $hesklang['high'] . '</span>'),
    0 => array('value' => 0, 'text' => $hesklang['critical'], 'formatted' => '<span class="critical">' . $hesklang['critical'] . '</span>'),
);

/* What should we do? */
if ($action = hesk_REQUEST('a')) {
    if ($action == 'linkcode') {
        generate_link_code();
    } elseif (defined('HESK_DEMO')) {
        hesk_process_messages($hesklang['ddemo'], 'manage_categories.php', 'NOTICE');
    } elseif ($action == 'new') {
        new_cat();
    } elseif ($action == 'remove') {
        remove();
    } elseif ($action == 'order') {
        order_cat();
    } elseif ($action == 'autoassign') {
        toggle_autoassign();
    } elseif ($action == 'type') {
        toggle_type();
    } elseif ($action == 'edit') {
        update_category();
    }
}

$modsForHesk_settings = mfh_getSettings();

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>

<script language="Javascript" type="text/javascript"><!--
    function confirm_delete() {
        if (confirm('<?php echo hesk_makeJsString($hesklang['confirm_del_cat']); ?>')) {
            return true;
        }
        else {
            return false;
        }
    }
    //-->
</script>

<?php
$orderBy = $modsForHesk_settings['category_order_column'];
$res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ORDER BY `" . $orderBy . "` ASC");
?>
<div class="content-wrapper">
    <section class="content">
        <!-- OLD ADD CATEGORY -->
    <!--<div class="box collapsed-box">
        <div class="box-header with-border">
            <h1 class="box-title">
                <?php /*echo $hesklang['add_cat']; */?>
            </h1>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <form action="manage_categories.php" method="post" role="form" class="form-horizontal" data-toggle="validator">
                <div class="form-group">
                    <label for="name" class="col-sm-4 control-label"><?php /*echo $hesklang['cat_name']; */?></label>

                    <div class="col-sm-8">
                        <input class="form-control"
                               placeholder="<?php /*echo htmlspecialchars($hesklang['cat_name']); */?>" type="text"
                               name="name" size="40" maxlength="40"
                            <?php
/*                            if (isset($_SESSION['catname'])) {
                                echo ' value="' . hesk_input($_SESSION['catname']) . '" ';
                            }
                            */?>
                               data-error="<?php /*echo htmlspecialchars($hesklang['enter_cat_name']); */?>"
                               required>
                        <div class="help-block"><?php /*echo $hesklang['max_chars']; */?></div>
                        <div class="help-block with-errors"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="priority" class="col-sm-4 control-label"><?php /*echo $hesklang['def_pri']; */?> <a href="#"
                                                                                           onclick="alert('<?php /*echo hesk_makeJsString($hesklang['cat_pri']); */?>')"><i
                                class="fa fa-question-circle settingsquestionmark"></i> </a> </label>

                    <div class="col-sm-8">
                        <select name="priority" class="form-control">
                            <?php
/*                            // Default priority: low
                            if (!isset($_SESSION['cat_priority'])) {
                                $_SESSION['cat_priority'] = 3;
                            }

                            // List possible priorities
                            foreach ($priorities as $value => $info) {
                                echo '<option value="' . $value . '"' . ($_SESSION['cat_priority'] == $value ? ' selected="selected"' : '') . '>' . $info['text'] . '</option>';
                            }
                            */?>
                        </select>

                    </div>
                </div>
                <div class="form-group">
                    <label for="color" class="col-sm-4 control-label">
                        <?php /*echo $hesklang['category_background_color']; */?>
                        <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                           title="<?php /*echo htmlspecialchars($hesklang['category_background_color']); */?>"
                           data-content="<?php /*echo htmlspecialchars($hesklang['category_background_color_help']); */?>"></i>
                    </label>
                    <div class="col-sm-8">
                        <input class="form-control colorpicker-trigger"
                               placeholder="<?php /*echo htmlspecialchars($hesklang['category_background_color']); */?>" type="text"
                               name="background-color" maxlength="7" required>
                        <div class="help-block with-errors"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="color" class="col-sm-4 control-label">
                        <?php /*echo $hesklang['category_foreground_color']; */?>
                        <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                           title="<?php /*echo htmlspecialchars($hesklang['category_foreground_color']); */?>"
                           data-content="<?php /*echo htmlspecialchars($hesklang['category_foreground_color_help']); */?>"></i>
                    </label>
                    <div class="col-sm-8">
                        <input class="form-control colorpicker-trigger"
                               placeholder="<?php /*echo htmlspecialchars($hesklang['category_foreground_color']); */?>" type="text"
                               name="foreground-color" maxlength="7">
                    </div>
                </div>
                <div class="form-group">
                    <label for="display-border" class="col-sm-4 control-label">
                        <?php /*echo $hesklang['category_display_border']; */?>
                        <i class="fa fa-question-circle settingsquestionmark" data-toggle="htmlpopover"
                           title="<?php /*echo htmlspecialchars($hesklang['category_display_border']); */?>"
                           data-content="<?php /*echo htmlspecialchars($hesklang['category_display_border_help']); */?>"></i>
                    </label>
                    <div class="col-sm-8 form-inline">
                        <div class="radio">
                            <label>
                                <input type="radio" name="display-border" value="1">
                                <?php /*echo $hesklang['yes']; */?>
                            </label>
                        </div>&nbsp;&nbsp;&nbsp;
                        <div class="radio">
                            <label>
                                <input type="radio" name="display-border" value="0" checked>
                                <?php /*echo $hesklang['no']; */?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="usage" class="col-sm-4 control-label"><?php /*echo $hesklang['usage']; */?></label>
                    <div class="col-sm-8">
                        <select name="usage" class="form-control">
                            <option value="0"><?php /*echo $hesklang['tickets_and_events']; */?></option>
                            <option value="1"><?php /*echo $hesklang['tickets_only']; */?></option>
                            <option value="2"><?php /*echo $hesklang['events_only']; */?></option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="options" class="col-sm-4 control-label"><?php /*echo $hesklang['opt']; */?></label>

                    <div class="col-sm-8">
                        <?php
/*                        if ($hesk_settings['autoassign']) {
                            */?>
                            <div class="checkbox">
                                <label><input type="checkbox" name="autoassign"
                                              value="Y" <?php /*if (!isset($_SESSION['cat_autoassign']) || $_SESSION['cat_autoassign'] == 1) {
                                        echo 'checked="checked"';
                                    } */?> /> <?php /*echo $hesklang['cat_aa']; */?></label><br/>
                            </div>
                            <?php
/*                        }
                        */?>
                        <div class="checkbox">
                            <label><input type="checkbox" name="type"
                                          value="Y" <?php /*if (isset($_SESSION['cat_type']) && $_SESSION['cat_type'] == 1) {
                                    echo 'checked="checked"';
                                } */?> /> <?php /*echo $hesklang['cat_type']; */?></label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-4">
                        <input type="hidden" name="a" value="new"/>
                        <input type="hidden" name="token" value="<?php /*hesk_token_echo(); */?>"/>
                        <input type="submit" value="<?php /*echo $hesklang['create_cat']; */?>" class="btn btn-default"/>
                    </div>
                </div>
            </form>
        </div>
    </div>-->
        <!-- END OLD ADD CATEGORY -->
        <div class="box">
            <div class="box-header with-border">
                <h1 class="box-title">
                    <?php echo $hesklang['manage_cat']; ?> <a href="javascript:void(0)"
                                                              onclick="javascript:alert('<?php echo hesk_makeJsString($hesklang['cat_intro']); ?>')"><i
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

                if ($hesk_settings['cust_urgency']) {
                    hesk_show_notice($hesklang['cat_pri_info'] . ' ' . $hesklang['cpri']);
                }
                ?>
                <div class="row">
                    <div class="col-md-12 text-right">
                        <button id="create-button" class="btn btn-success">
                            <i class="fa fa-plus-circle"></i>&nbsp;
                            <?php echo $hesklang['create_new']; ?>
                        </button>
                    </div>
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th><?php echo $hesklang['id']; ?></th>
                                <th><?php echo $hesklang['cat_name']; ?></th>
                                <th><?php echo $hesklang['visibility']; ?></th>
                                <th><?php echo $hesklang['aass']; ?></th>
                                <th><?php echo $hesklang['priority']; ?></th>
                                <th><?php echo $hesklang['not']; ?></th>
                                <th><?php echo $hesklang['graph']; ?></th>
                                <th><?php echo $hesklang['usage']; ?></th>
                                <th><?php echo $hesklang['opt']; ?></th>
                            </tr>
                            </thead>
                            <tbody id="table-body">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="overlay" id="overlay">
                <i class="fa fa-spinner fa-spin"></i>
            </div>
        </div>
    </section>
</div>
<!-- Category modal -->
<div class="modal fade" id="category-modal" tabindex="-1" role="dialog" style="overflow: hidden">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="cursor: move">
                <button type="button" class="close cancel-callback" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <span id="edit-label"><?php echo $hesklang['edit_category']; ?></span>
                    <span id="create-label"><?php echo $hesklang['create_cat']; ?></span>
                </h4>
            </div>
            <form id="manage-category" class="form-horizontal" data-toggle="validator" method="post">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4><?php echo $hesklang['basicProperties']; ?></h4>
                            <div class="form-group">
                                <label for="name" class="col-sm-5 control-label"><?php echo $hesklang['cat_name']; ?></label>
                                <div class="col-sm-7">
                                    <input type="text" name="name" class="form-control" placeholder="<?php echo $hesklang['cat_name']; ?>"
                                           data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                           required>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description" class="col-sm-5 control-label">
                                    <?php echo $hesklang['description']; ?>
                                </label>
                                <div class="col-sm-7">
                                    <textarea class="form-control" name="description" placeholder="<?php echo $hesklang['description']; ?>"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h4><?php echo $hesklang['color']; ?></h4>
                            <div class="form-group">
                                <label for="background-color" class="col-sm-5 control-label">
                                    <?php echo $hesklang['category_background_color']; ?>
                                    <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                                       title="<?php echo htmlspecialchars($hesklang['category_background_color']); ?>"
                                       data-content="<?php echo htmlspecialchars($hesklang['category_background_color_help']); ?>"></i>
                                </label>
                                <div class="col-sm-7">
                                    <input type="text" name="background-color" class="form-control category-colorpicker"
                                           placeholder="<?php echo $hesklang['category_background_color']; ?>"
                                           data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                           required>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="foreground-color" class="col-sm-5 control-label">
                                    <?php echo $hesklang['category_foreground_color']; ?>
                                    <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                                       title="<?php echo htmlspecialchars($hesklang['category_foreground_color']); ?>"
                                       data-content="<?php echo htmlspecialchars($hesklang['category_foreground_color_help']); ?>"></i>
                                </label>
                                <div class="col-sm-7">
                                    <input type="text" name="foreground-color" class="form-control category-colorpicker"
                                           placeholder="<?php echo $hesklang['category_foreground_color']; ?>">
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="display-border" class="col-sm-5 control-label">
                                    <?php echo $hesklang['category_display_border']; ?>
                                    <i class="fa fa-question-circle settingsquestionmark" data-toggle="htmlpopover"
                                       title="<?php echo htmlspecialchars($hesklang['category_display_border']); ?>"
                                       data-content="<?php echo htmlspecialchars($hesklang['category_display_border_help']); ?>"></i>
                                </label>
                                <div class="col-sm-7 form-inline">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="display-border" value="1">
                                            <?php echo $hesklang['yes']; ?>
                                        </label>
                                    </div>&nbsp;&nbsp;&nbsp;
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="display-border" value="0" checked>
                                            <?php echo $hesklang['no']; ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h4>PROPERTIES [!]</h4>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="priority" class="col-sm-5 control-label">
                                    <?php echo $hesklang['priority']; ?>
                                    <a href="#"
                                       onclick="alert('<?php echo hesk_makeJsString($hesklang['cat_pri']); ?>')"><i
                                                class="fa fa-question-circle settingsquestionmark"></i> </a>
                                </label>
                                <div class="col-sm-7">
                                    <select name="priority" class="form-control">
                                        <?php
                                        // List possible priorities
                                        foreach ($priorities as $value => $info) {
                                            echo '<option value="' . $value . '">' . $info['text'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="usage" class="col-sm-5 control-label">
                                    <?php echo $hesklang['usage']; ?>
                                </label>
                                <div class="col-sm-7">
                                    <select name="usage" class="form-control">
                                        <option value="0"><?php echo $hesklang['tickets_and_events']; ?></option>
                                        <option value="1"><?php echo $hesklang['tickets_only']; ?></option>
                                        <option value="2"><?php echo $hesklang['events_only']; ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="autoassign" class="col-sm-5 control-label">
                                    <?php echo $hesklang['aass']; ?>
                                </label>
                                <div class="col-sm-7 form-inline">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="autoassign" value="1">
                                            <?php echo $hesklang['yes']; ?>
                                        </label>
                                    </div>&nbsp;&nbsp;&nbsp;
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="autoassign" value="0" checked>
                                            <?php echo $hesklang['no']; ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="type" class="col-sm-5 control-label">
                                    <?php echo $hesklang['visibility']; ?>
                                </label>
                                <div class="col-sm-7">
                                    <select name="type" class="form-control">
                                        <option value="0"><?php echo $hesklang['cat_public']; ?></option>
                                        <option value="1"><?php echo $hesklang['cat_private']; ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="id">
                    <input type="hidden" name="cat-order">
                    <div id="action-buttons" class="btn-group">
                        <button type="button" class="btn btn-default cancel-button cancel-callback" data-dismiss="modal">
                            <i class="fa fa-times-circle"></i>
                            <span><?php echo $hesklang['cancel']; ?></span>
                        </button>
                        <button type="submit" class="btn btn-success save-button">
                            <i class="fa fa-check-circle"></i>
                            <span><?php echo $hesklang['save']; ?></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/html" id="category-row-template">
    <tr>
        <td><span data-property="id" data-value="x"></span></td>
        <td>
            <span class="label category-label" data-property="category-name">
            </span>
        </td>
        <td>
            <i style="display: none" class="fa fa-fw fa-lock icon-link gray"></i>
            <i style="display: none" class="fa fa-fw fa-unlock-alt icon-link blue"></i>
            <span data-property="type"></span>
        </td>
        <td>
            <i class="fa fa-fw fa-bolt icon-link"></i>
            <span data-property="autoassign"></span>
        </td>
        <td><span data-property="priority"></span></td>
        <td><a data-property="number-of-tickets" href="#"></a></td>
        <td>
            <div class="progress" style="width: 160px; margin-bottom: 0" title="Width tooltip" data-toggle="tooltip">
                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
        </td>
        <td>
            <i class="fa fa-fw fa-ticket icon-link" data-toggle="tooltip" title="<?php echo $hesklang['tickets']; ?>"></i>
            <i class="fa fa-fw fa-calendar icon-link" data-toggle="tooltip" title="<?php echo $hesklang['events']; ?>"></i>
        </td>
        <td>
            <span class="generate-link-group">
                <a data-property="generate-link" href="#">
                    <i class="fa fa-fw icon-link" data-toggle="tooltip"
                       data-placement="top"></i>
                </a>
            </span>
            <span class="sort-arrows">
                <a href="#" data-action="sort"
                   data-direction="up">
                    <i class="fa fa-fw fa-arrow-up icon-link green"
                       data-toggle="tooltip" title="<?php echo $hesklang['move_up']; ?>"></i>
                </a>
                <a href="#" data-action="sort"
                   data-direction="down">
                    <i class="fa fa-fw fa-arrow-down icon-link green"
                       data-toggle="tooltip" title="<?php echo $hesklang['move_dn'] ?>"></i>
                </a>
            </span>
            <a href="#" data-action="edit">
                <i class="fa fa-fw fa-pencil icon-link orange"
                   data-toggle="tooltip" title="<?php echo $hesklang['edit']; ?>"></i>
            </a>
            <a href="#" data-action="delete">
                <i class="fa fa-fw fa-times icon-link red"
                   data-toggle="tooltip" title="<?php echo $hesklang['delete']; ?>"></i>
            </a>
        </td>
    </tr>
</script>
<input type="hidden" name="show-tickets-path" value="show_tickets.php?category={0}&amp;s_all=1&amp;s_my=1&amp;s_ot=1&amp;s_un=1">
<?php
echo mfh_get_hidden_fields_for_language(array(
    'critical',
    'high',
    'medium',
    'low',
    'perat',
    'aaon',
    'aaoff',
    'cat_private',
    'cat_public',
    'cat_removed',
    'error_deleting_category',
    'enabled_title_case',
    'disabled_title_case',
    'geco',
    'cpric',
));

require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


/*** START FUNCTIONS ***/


function change_priority()
{
    global $hesk_settings, $hesklang, $priorities;

    /* A security check */
    hesk_token_check('POST');

    $_SERVER['PHP_SELF'] = 'manage_categories.php?catid=' . intval(hesk_POST('catid'));

    $catid = hesk_isNumber(hesk_POST('catid'), $hesklang['choose_cat_ren'], $_SERVER['PHP_SELF']);
    $_SESSION['selcat'] = $catid;
    $_SESSION['selcat2'] = $catid;

    $priority = intval(hesk_POST('priority', 3));
    if (!array_key_exists($priority, $priorities)) {
        $priority = 3;
    }

    hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` SET `priority`='{$priority}' WHERE `id`='" . intval($catid) . "'");

    hesk_cleanSessionVars('cat_ch_priority');

    hesk_process_messages($hesklang['cat_pri_ch'] . ' ' . $priorities[$priority]['formatted'], $_SERVER['PHP_SELF'], 'SUCCESS');
} // END change_priority()


function generate_link_code() {
	global $hesk_settings, $hesklang;
	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML; 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<title><?php echo $hesklang['genl']; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=<?php echo $hesklang['ENCODING']; ?>" />
<style type="text/css">
body
{
        margin:5px 5px;
        padding:0;
        background:#fff;
        color: black;
        font : 68.8%/1.5 Verdana, Geneva, Arial, Helvetica, sans-serif;
}

p
{
        color : black;
        font-family : Verdana, Geneva, Arial, Helvetica, sans-serif;
        font-size: 1.0em;
}
h3
{
        color : #AF0000;
        font-family : Verdana, Geneva, Arial, Helvetica, sans-serif;
        font-weight: bold;
        font-size: 1.0em;
}
</style>
</head>
<body>

<div class="text-center">

<h3><?php echo $hesklang['genl']; ?></h3>

<?php
if ( ! empty($_GET['p']) )
{
	echo '<p>&nbsp;<br />' . $hesklang['cpric'] . '<br />&nbsp;</p>';
}
else
{
	?>
	<p><i><?php echo $hesklang['genl2']; ?></i></p>

	<textarea rows="3" cols="50" onfocus="this.select()"><?php echo $hesk_settings['hesk_url'].'/index.php?a=add&amp;catid='.intval( hesk_GET('catid') ); ?></textarea>
	<?php
}
?>

<p align="center"><a href="#" onclick="Javascript:window.close()"><?php echo $hesklang['cwin']; ?></a></p>

</div>

</body>

</html>
	<?php
    exit();
}


function new_cat()
{
    global $hesk_settings, $hesklang;

    /* A security check */
    hesk_token_check('POST');

    /* Options */
    $_SESSION['cat_autoassign'] = hesk_POST('autoassign') == 'Y' ? 1 : 0;
    $_SESSION['cat_type'] = hesk_POST('type') == 'Y' ? 1 : 0;

    // Default priority
    $_SESSION['cat_priority'] = intval(hesk_POST('priority', 3));
    if ($_SESSION['cat_priority'] < 0 || $_SESSION['cat_priority'] > 3) {
        $_SESSION['cat_priority'] = 3;
    }

    /* Category name */
    $catname = hesk_input(hesk_POST('name'), $hesklang['enter_cat_name'], 'manage_categories.php');

    $background_color = hesk_POST('background-color', '#ffffff');
    $foreground_color = hesk_POST('foreground-color', '#000000');
    $display_border = hesk_POST('display-border', 0);
    if ($foreground_color == '') {
        $foreground_color = 'AUTO';
        $display_border = 0;
    }

    $usage = hesk_POST('usage', 0);

    /* Do we already have a category with this name? */
    $res = hesk_dbQuery("SELECT `id` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` WHERE `name` LIKE '" . hesk_dbEscape(hesk_dbLike($catname)) . "' LIMIT 1");
    if (hesk_dbNumRows($res) != 0) {
        $_SESSION['catname'] = $catname;
        hesk_process_messages($hesklang['cndupl'], 'manage_categories.php');
    }

    /* Get the latest cat_order */
    $res = hesk_dbQuery("SELECT `cat_order` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ORDER BY `cat_order` DESC LIMIT 1");
    $row = hesk_dbFetchRow($res);
    $my_order = $row[0] + 10;

    hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` 
        (`name`,`cat_order`,`autoassign`,`type`, `priority`, `background_color`, `foreground_color`, `display_border_outline`, `usage`) VALUES 
        ('" . hesk_dbEscape($catname) . "','" . intval($my_order) . "','" . intval($_SESSION['cat_autoassign']) . "',
        '" . intval($_SESSION['cat_type']) . "','{$_SESSION['cat_priority']}', '" . hesk_dbEscape($background_color) . "', 
        '" . hesk_dbEscape($foreground_color) . "', '" . intval($display_border) . "', " . intval($usage) . ")");

    hesk_cleanSessionVars('catname');
    hesk_cleanSessionVars('cat_autoassign');
    hesk_cleanSessionVars('cat_type');
    hesk_cleanSessionVars('cat_priority');

    $_SESSION['selcat2'] = hesk_dbInsertID();

    hesk_process_messages(sprintf($hesklang['cat_name_added'], '<i>' . stripslashes($catname) . '</i>'), 'manage_categories.php', 'SUCCESS');
} // End new_cat()


function update_category()
{
    global $hesk_settings, $hesklang;

    /* A security check */
    hesk_token_check('POST');

    $_SERVER['PHP_SELF'] = 'manage_categories.php?catid=' . intval(hesk_POST('catid'));

    $catid = hesk_isNumber(hesk_POST('id'), $hesklang['choose_cat_ren'], $_SERVER['PHP_SELF']);
    $_SESSION['selcat'] = $catid;
    $_SESSION['selcat2'] = $catid;

    $catname = hesk_input(hesk_POST('name'), $hesklang['cat_ren_name'], $_SERVER['PHP_SELF']);
    $_SESSION['catname2'] = $catname;

    $background_color = hesk_POST('background-color', '#ffffff');
    $foreground_color = hesk_POST('foreground-color', '#000000');
    $display_border = hesk_POST('display-border', 0);
    if ($foreground_color == '') {
        $foreground_color = 'AUTO';
        $display_border = 0;
    }
    $manager = hesk_POST('manager', 0);
    $priority = hesk_POST('priority', 0);
    $usage = hesk_POST('usage', 0);


    hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` SET `name`='" . hesk_dbEscape($catname) . "',
     `priority` = '" . hesk_dbEscape($priority) . "',
     `manager` = " . intval($manager) . ",
     `background_color` = '" . hesk_dbEscape($background_color) . "',
     `foreground_color` = '" . hesk_dbEscape($foreground_color) . "',
     `display_border_outline` = '" . intval($display_border) . "',
     `usage` = " . intval($usage) . "
     WHERE `id`='" . intval($catid) . "'");

    unset($_SESSION['selcat']);
    unset($_SESSION['catname2']);

    hesk_process_messages(sprintf($hesklang['category_updated'], stripslashes($catname)), $_SERVER['PHP_SELF'], 'SUCCESS');
} // End rename_cat()


function remove()
{
    global $hesk_settings, $hesklang;

    /* A security check */
    hesk_token_check();

    $_SERVER['PHP_SELF'] = 'manage_categories.php';

    $mycat = intval(hesk_GET('catid')) or hesk_error($hesklang['no_cat_id']);
    if ($mycat == 1) {
        hesk_process_messages($hesklang['cant_del_default_cat'], $_SERVER['PHP_SELF']);
    }

    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` WHERE `id`='" . intval($mycat) . "'");
    if (hesk_dbAffectedRows() != 1) {
        hesk_error("$hesklang[int_error]: $hesklang[cat_not_found].");
    }

    hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `category`=1 WHERE `category`='" . intval($mycat) . "'");

    hesk_process_messages($hesklang['cat_removed_db'], $_SERVER['PHP_SELF'], 'SUCCESS');
} // End remove()


function order_cat()
{
    global $hesk_settings, $hesklang;

    /* A security check */
    hesk_token_check();

    $catid = intval(hesk_GET('catid')) or hesk_error($hesklang['cat_move_id']);
    $_SESSION['selcat2'] = $catid;

    $cat_move = intval(hesk_GET('move'));

    hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` SET `cat_order`=`cat_order`+" . intval($cat_move) . " WHERE `id`='" . intval($catid) . "'");
    if (hesk_dbAffectedRows() != 1) {
        hesk_error("$hesklang[int_error]: $hesklang[cat_not_found].");
    }

    /* Update all category fields with new order */
    $res = hesk_dbQuery("SELECT `id` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ORDER BY `cat_order` ASC");

    $i = 10;
    while ($mycat = hesk_dbFetchAssoc($res)) {
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` SET `cat_order`=" . intval($i) . " WHERE `id`='" . intval($mycat['id']) . "'");
        $i += 10;
    }

    header('Location: manage_categories.php');
    exit();
} // End order_cat()


function toggle_autoassign()
{
    global $hesk_settings, $hesklang;

    /* A security check */
    hesk_token_check();

    $catid = intval(hesk_GET('catid')) or hesk_error($hesklang['cat_move_id']);
    $_SESSION['selcat2'] = $catid;

    if (intval(hesk_GET('s'))) {
        $autoassign = 1;
        $tmp = $hesklang['caaon'];
    } else {
        $autoassign = 0;
        $tmp = $hesklang['caaoff'];
    }

    /* Update auto-assign settings */
    $res = hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` SET `autoassign`='" . intval($autoassign) . "' WHERE `id`='" . intval($catid) . "'");
    if (hesk_dbAffectedRows() != 1) {
        hesk_process_messages($hesklang['int_error'] . ': ' . $hesklang['cat_not_found'], './manage_categories.php');
    }

    hesk_process_messages($tmp, './manage_categories.php', 'SUCCESS');

} // End toggle_autoassign()


function toggle_type()
{
    global $hesk_settings, $hesklang;

    /* A security check */
    hesk_token_check();

    $catid = intval(hesk_GET('catid')) or hesk_error($hesklang['cat_move_id']);
    $_SESSION['selcat2'] = $catid;

    if (intval(hesk_GET('s'))) {
        $type = 1;
        $tmp = $hesklang['cpriv'];
    } else {
        $type = 0;
        $tmp = $hesklang['cpub'];
    }

    /* Update auto-assign settings */
    hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` SET `type`='{$type}' WHERE `id`='" . intval($catid) . "'");
    if (hesk_dbAffectedRows() != 1) {
        hesk_process_messages($hesklang['int_error'] . ': ' . $hesklang['cat_not_found'], './manage_categories.php');
    }

    hesk_process_messages($tmp, './manage_categories.php', 'SUCCESS');

} // End toggle_type()

function get_manager($user_id, $user_array) {
    global $hesklang;

    if ($user_id == 0) {
        return $hesklang['no_manager'];
    }

    foreach ($user_array as $user) {
        if ($user['id'] == $user_id) {
            return $user['name'];
        }
    }

    return 'Error!';
}

?>
