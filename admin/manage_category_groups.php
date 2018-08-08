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
define('PAGE_TITLE', 'ADMIN_CATEGORY_GROUPS');
define('MFH_PAGE_LAYOUT', 'TOP_ONLY');
define('EXTRA_JS', '<script src="'.HESK_PATH.'internal-api/js/manage-category-groups.js"></script>');

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

$modsForHesk_settings = mfh_getSettings();

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');

$orderBy = $modsForHesk_settings['category_order_column'];
$res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ORDER BY `" . $orderBy . "` ASC");
?>
<div class="content-wrapper">
    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h1 class="box-title">
                    <?php echo $hesklang['manage_cat_groups']; ?> <a href="javascript:void(0)" data-toggle="tooltip"
                                                                     data-placement="right"
                                                              title="<?php echo hesk_htmlspecialchars($hesklang['cat_groups_intro']); ?>">
                        <i class="fa fa-question-circle settingsquestionmark"></i></a>
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
                ?>
                <div class="row">
                    <div class="col-md-12 text-right">
                        <button id="create-button" class="btn btn-success">
                            <i class="fa fa-plus-circle"></i>&nbsp;
                            <?php echo $hesklang['create_new']; ?>
                        </button>
                    </div>
                    <div class="col-md-12">
                        <div id="tree"></div>
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
                    <span id="create-label"><?php echo $hesklang['create_category_group']; ?></span>
                    <span id="edit-label"><?php echo $hesklang['edit_category_group']; ?></span>
                </h4>
            </div>
            <form id="manage-category" class="form-horizontal" data-toggle="validator" method="post">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4><?php echo $hesklang['category_group_name_header']; ?></h4>
                            <?php foreach ($hesk_settings['languages'] as $name => $info): ?>
                                <div class="form-group">
                                    <label for="<?php echo $info['folder']; ?>" class="control-label col-sm-5"><?php echo $name; ?></label>
                                    <div class="col-sm-7">
                                        <input data-type="name" name="<?php echo $info['folder']; ?>" class="form-control" placeholder="<?php echo $name; ?>">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="col-md-6">
                            <h4><?php echo $hesklang['parent_category_group']; ?></h4>
                            <div class="form-group">
                                <label for="parent-category-group" class="col-sm-5 control-label">
                                    <?php echo $hesklang['parent_category_group']; ?>
                                </label>
                                <div class="col-sm-7">
                                    <select name="parent-category-group" class="selectpicker form-control">
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="id">
                    <input type="hidden" name="cat-group-order">
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
<script type="text/html" id="category-group-edit-template">
    <span>
        <a name="Edit Category Group" href="#" data-action="edit">
            <i class="fa fa-fw fa-pencil icon-link orange"
               data-toggle="tooltip" title="<?php echo $hesklang['edit']; ?>"></i>
        </a>
    </span>
</script>
<script type="text/html" id="category-group-delete-template">
    <span>
        <a name="Delete Category Group" href="#" data-action="delete">
            <i class="fa fa-fw fa-times icon-link red"
               data-toggle="tooltip" title="<?php echo $hesklang['delete']; ?>"></i>
        </a>
    </span>
</script>
<input type="hidden" name="hesk_lang" value="<?php echo $hesk_settings['languages'][$hesk_settings['language']]['folder']; ?>">
<?php
echo mfh_get_hidden_fields_for_language(array(
    'error_retrieving_category_groups',
    'no_category_groups_found',
    'category_group_created',
    'category_group_updated',
    'error_saving_updating_category_group',
    'none',
));

require_once(HESK_PATH . 'inc/footer.inc.php');
exit();
?>
