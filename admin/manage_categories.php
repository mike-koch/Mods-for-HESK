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
define('EXTRA_JS', '
    <script src="'.HESK_PATH.'js/jstree.min.js"></script>
    <script src="'.HESK_PATH.'js/jstreegrid.js"></script>
    <script src="'.HESK_PATH.'internal-api/js/manage-categories.js"></script>');

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
<div class="content-wrapper">
    <section class="content">
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
                        <div id="category-tree" style="min-width: 100%">
                            <?php // Tree rendered here via JS ?>
                        </div>

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
                                <th><?php echo $hesklang['manager']; ?></th>
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
            <!--<div class="overlay" id="overlay">
                <i class="fa fa-spinner fa-spin"></i>
            </div>-->
        </div>
    </section>
</div>
<?php
$usersRs = hesk_dbQuery("SELECT `id`, `name` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `active` = '1' AND `isadmin` = '0'");
echo '<script>var users = [];';
$users = array();
while ($row = hesk_dbFetchAssoc($usersRs)) {
    $users[] = $row;
    echo "users[" . $row['id'] . "] = {
        id: ".$row['id'].",
        name: '".$row['name']."'
    }\n";
}
echo '</script>';
?>
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
                            <h4><?php echo $hesklang['cat_name_description']; ?></h4>
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
                            <h4><?php echo $hesklang['basicProperties']; ?></h4>
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
                            <div class="form-group">
                                <label for="manager" class="col-sm-5 control-label">
                                    <?php echo $hesklang['manager']; ?>
                                </label>
                                <div class="col-sm-7">
                                    <select name="manager" class="form-control">
                                        <option value="0"><?php echo $hesklang['no_manager']; ?></option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
                                        <?php endforeach; ?>
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
                                <div class="col-sm-7 form-inline">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="type" value="0">
                                            <?php echo $hesklang['cat_public']; ?>
                                        </label>
                                    </div><br>
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="type" value="1" checked>
                                            <?php echo $hesklang['cat_private']; ?>
                                        </label>
                                    </div>
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
<div class="modal fade" id="generate-link-modal" tabindex="-1" role="dialog" style="overflow: hidden">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="cursor: move">
                <button type="button" class="close cancel-callback" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo $hesklang['genl']; ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <p><?php echo $hesklang['genl2']; ?></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 form-group">
                        <div class="input-group">
                            <input type="text" id="link" class="form-control white-readonly"
                                   title="<?php echo $hesklang['genl']; ?>" readonly>
                            <div class="generate-link-button input-group-addon button" data-toggle="tooltip" title="Copy to clipboard"
                                style="padding:0; border: none">
                                <button class="btn btn-primary">
                                    <i class="fa fa-files-o"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default cancel-button cancel-callback" data-dismiss="modal">
                    <i class="fa fa-times-circle"></i>
                    <span><?php echo $hesklang['close_modal']; ?></span>
                </button>
            </div>
        </div>
    </div>
</div>
<script type="text/html" id="category-row-template">
    <tr>
        <td><span data-property="id" data-value="x"></span></td>
        <td>
            <span class="label category-label" data-property="category-name">
            </span>
            <i class="fa fa-info-circle" data-toggle="popover" title="<?php echo $hesklang['description']; ?>"></i>
        </td>
        <td>
            <i style="display: none; padding-right: 8px;" class="fa fa-fw fa-lock icon-link gray"></i>
            <i style="display: none; padding-right: 8px;" class="fa fa-fw fa-unlock-alt icon-link blue"></i>
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
            <span data-property="manager"></span>
        </td>
        <td>
            <i class="fa fa-fw fa-ticket icon-link" data-toggle="tooltip" title="<?php echo $hesklang['tickets']; ?>"></i>
            <i class="fa fa-fw fa-calendar icon-link" data-toggle="tooltip" title="<?php echo $hesklang['events']; ?>"></i>
        </td>
        <td>
            <a data-property="generate-link" href="#">
                <i class="fa fa-fw icon-link" data-toggle="tooltip"
                   data-placement="top"></i>
            </a>
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
    'error_retrieving_categories',
    'error_saving_updating_category',
    'copied_to_clipboard',
    'category_updated',
    'cat_name_added',
    'enabled_title_case',
    'disabled_title_case',
    'geco',
    'cpric',
    'no_manager',
));

require_once(HESK_PATH . 'inc/footer.inc.php');
exit();
?>
