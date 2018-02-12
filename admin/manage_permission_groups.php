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
define('PAGE_TITLE', 'ADMIN_PERMISSION_TPL');
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
hesk_checkPermission('can_man_permission_tpl');

/* What should we do? */
if ($action = hesk_REQUEST('a')) {
    if ($action == 'save') {
        save();
    } elseif ($action == 'create') {
        create();
    } elseif ($action == 'delete') {
        deleteTemplate();
    }
}

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');

$modsForHesk_settings = mfh_getSettings();

$res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "permission_templates` ORDER BY `name` ASC");
$templates = array();
while ($row = hesk_dbFetchAssoc($res)) {
    $templates[] = $row;
}
$featureArray = hesk_getFeatureArray();
$orderBy = $modsForHesk_settings['category_order_column'];
$res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ORDER BY `" . $orderBy . "` ASC");
$categories = array();
while ($row = hesk_dbFetchAssoc($res)) {
    $categories[] = $row;
}
?>
<div class="content-wrapper">
    <section class="content">
    <?php hesk_handle_messages(); ?>
    <div class="box">
        <div class="box-header with-border">
            <h1 class="box-title">
                <?php echo $hesklang['manage_permission_groups']; ?>
                <i class="fa fa-question-circle settingsquestionmark" data-toggle="tooltip" data-placement="right"
                   title="<?php echo $hesklang['manage_permission_groups_help']; ?>"></i>
            </h1>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <a href="#" data-toggle="modal" data-target="#modal-template-new" class="btn btn-success nu-floatRight">
                <i class="fa fa-plus-circle"></i> <?php echo $hesklang['create_new_group']; ?>
            </a>
            <table class="table table-striped">
                <thead>
                <th><?php echo $hesklang['name']; ?></th>
                <th><?php echo $hesklang['number_of_users']; ?></th>
                <th><?php echo $hesklang['actions']; ?></th>
                </thead>
                <tbody>
                <?php foreach ($templates as $row): ?>
                    <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo getNumberOfUsersWithPermissionGroup($row['id']); ?></td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#modal-template-<?php echo $row['id'] ?>">
                                <i class="fa fa-pencil icon-link" data-toggle="tooltip"
                                   title="<?php echo $hesklang['view_permissions_for_this_group'] ?>"></i></a>
                            <?php
                            if ($row['id'] != 1 && $row['id'] != 2):
                                ?>
                                <a href="manage_permission_groups.php?a=delete&amp;id=<?php echo $row['id']; ?>">
                                    <i class="fa fa-times icon-link red" data-toggle="tooltip"
                                       title="<?php echo $hesklang['delete']; ?>"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
</div>
<?php
foreach ($templates as $template) {
    createEditModal($template, $featureArray, $categories);
}
buildCreateModal($featureArray, $categories);

require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


/*** START FUNCTIONS ***/
function getNumberOfUsersWithPermissionGroup($templateId)
{
    global $hesk_settings;

    $res = hesk_dbQuery("SELECT 1 FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `permission_template` = " . intval($templateId));
    return hesk_dbNumRows($res);
}

function createEditModal($template, $features, $categories)
{
    global $hesklang;

    $enabledFeatures = array();
    $enabledCategories = array();
    if ($template['heskprivileges'] !== 'ALL') {
        $enabledFeatures = explode(',', $template['heskprivileges']);
        $enabledCategories = explode(',', $template['categories']);
    }
    ?>
    <div class="modal fade" id="modal-template-<?php echo $template['id'] ?>" tabindex="-1" role="dialog"
         aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_permission_groups.php" role="form" method="post" id="form<?php echo $template['id']; ?>">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><?php echo sprintf($hesklang['permissions_for_group'], $template['name']); ?></h4>
                    </div>
                    <div class="modal-body">
                        <?php if ($template['id'] == 1): ?>
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                <?php echo $hesklang['protected_group']; ?>
                            </div>
                        <?php endif; ?>
                        <div class="row">
                            <div class="form-group">
                                <div class="col-sm-2">
                                    <label for="name"
                                           class="control-label"><?php echo $hesklang['group_name']; ?></label>
                                </div>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="name"
                                           value="<?php echo htmlspecialchars($template['name']); ?>"
                                           placeholder="<?php echo htmlspecialchars($hesklang['group_name']); ?>"
                                           data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                           required>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <h4><?php echo $hesklang['menu_cat']; ?></h4>

                                <div class="footerWithBorder blankSpace"></div>
                                <div class="form-group">
                                    <?php
                                    foreach ($categories as $category): ?>
                                        <?php
                                        $checked = '';
                                        $disabled = '';
                                        if (in_array($category['id'], $enabledCategories) ||
                                            $template['categories'] == 'ALL') {
                                            $checked = 'checked ';
                                        }
                                        if ((!hesk_SESSION('isadmin') &&
                                                !in_array($category['id'], $_SESSION['categories'])) ||
                                            $template['categories'] === 'ALL') {
                                            $disabled = ' disabled';
                                        }?>

                                        <?php if ($_SESSION['isadmin'] || in_array($category['id'], $_SESSION['categories']) || $checked): ?>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="categories[]"
                                                       value="<?php echo $category['id']; ?>" <?php echo $checked . ' ' . $disabled; ?>>
                                                <?php echo $category['name']; ?>
                                            </label>
                                        </div>
                                        <?php
                                        endif;
                                    endforeach; ?>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <h4><?php echo $hesklang['allow_feat']; ?></h4>

                                <div class="footerWithBorder blankSpace"></div>
                                <div class="form-group">
                                    <?php
                                    foreach ($features as $feature): ?>
                                        <?php
                                        $checked = '';
                                        $disabled = '';
                                        if (in_array($feature, $enabledFeatures) ||
                                            $template['heskprivileges'] === 'ALL') {
                                            $checked = 'checked ';
                                        }
                                        if ((!hesk_SESSION('isadmin') &&
                                                strpos($_SESSION['heskprivileges'], $feature) === false) ||
                                            $template['heskprivileges'] === 'ALL') {
                                            $disabled = ' disabled';
                                        }
                                        if ($_SESSION['isadmin'] || strpos($_SESSION['heskprivileges'], $feature) !== false || $checked):  ?>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="features[]"
                                                       value="<?php echo $feature; ?>" <?php echo $checked . $disabled; ?>>
                                                <?php echo $hesklang[$feature]; ?>
                                            </label>
                                        </div>
                                        <?php endif;
                                        endforeach; ?>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="a" value="save">
                        <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                        <div class="btn-group">
                            <input type="submit" class="btn btn-success"
                                   value="<?php echo $hesklang['save_changes']; ?>">
                            <button type="button" class="btn btn-default"
                                    data-dismiss="modal"><?php echo $hesklang['close_modal_without_saving']; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

function buildCreateModal($features, $categories)
{
    global $hesklang;
    ?>
    <div class="modal fade" id="modal-template-new" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_permission_groups.php" role="form" method="post" id="createForm">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><?php echo $hesklang['create_new_group_title']; ?></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group">
                                <div class="col-sm-2">
                                    <label for="name"
                                           class="control-label"><?php echo $hesklang['group_name']; ?></label>
                                </div>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="name"
                                           placeholder="<?php echo $hesklang['group_name']; ?>" required>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <h4><?php echo $hesklang['menu_cat']; ?></h4>

                                <div class="footerWithBorder blankSpace"></div>
                                <div class="form-group">
                                    <?php
                                    foreach ($categories as $category):
                                        if (hesk_SESSION('isadmin') || in_array($category['id'], $_SESSION['categories'])): ?>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="categories[]"
                                                       data-modal="new-categories"
                                                       data-checkbox="categories"
                                                       value="<?php echo $category['id']; ?>">
                                                <?php echo $category['name']; ?>
                                            </label>
                                        </div>
                                    <?php endif; endforeach; ?>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <h4><?php echo $hesklang['allow_feat']; ?></h4>

                                <div class="footerWithBorder blankSpace"></div>
                                <div class="form-group">
                                    <?php foreach ($features as $feature):
                                        if (strpos($_SESSION['heskprivileges'], $feature) !== false || hesk_SESSION('isadmin')):
                                        ?>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="features[]"
                                                       data-modal="new-features"
                                                       data-checkbox="features"
                                                       value="<?php echo $feature; ?>">
                                                <?php echo $hesklang[$feature]; ?>
                                            </label>
                                        </div>
                                    <?php endif; endforeach; ?>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="a" value="create">

                        <div class="btn-group">
                            <input type="submit" class="btn btn-success"
                                   value="<?php echo $hesklang['save_changes']; ?>">
                            <button type="button" class="btn btn-default"
                                    data-dismiss="modal"><?php echo $hesklang['close_modal_without_saving']; ?></button>
                        </div>
                    </div>
                </form>
                <script>
                    buildValidatorForPermissionTemplates('createForm', '<?php echo $hesklang['select_at_least_one_value']; ?>');
                </script>
            </div>
        </div>
    </div>
    <?php
}

function save()
{
    global $hesk_settings, $hesklang;

    $templateId = hesk_POST('template_id');
    $res = hesk_dbQuery("SELECT `heskprivileges`, `categories` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "permission_templates`
        WHERE `id` = " . intval($templateId));
    $row = hesk_dbFetchAssoc($res);

    // Add 'can ban emails' if 'can unban emails' is set (but not added). Same with 'can ban ips'
    $catArray = hesk_POST_array('categories');
    $featArray = hesk_POST_array('features');
    validate($featArray, $catArray);
    if (in_array('can_unban_emails', $featArray) && !in_array('can_ban_emails', $featArray)) {
        array_push($catArray, 'can_ban_emails');
    }
    if (in_array('can_unban_ips', $featArray) && !in_array('can_ban_ips', $featArray)) {
        array_push($featArray, 'can_ban_ips');
    }
    $categories = implode(',', $catArray);
    $features = implode(',', $featArray);
    $name = hesk_POST('name');

    // Only allow users to add what they are allowed to add
    // Admins can handle anything
    if (!$_SESSION['isadmin']) {
        // Update categories based on user visibility
        $originalCategories = explode(',', $row['categories']);
        $newCategories = array();
        foreach ($originalCategories as $innerCategory) {
            if (in_array($innerCategory, $catArray) && in_array($innerCategory, $_SESSION['categories'])) {
                $newCategories[] = $innerCategory;
            } elseif (!in_array($innerCategory, $catArray) && !in_array($innerCategory, $_SESSION['categories'])) {
                // The user can't modify this, so keep it in
                $newCategories[] = $innerCategory;
            }
            // If neither, the user removed it.
        }

        // Update features based on user visibility
        $originalFeatures = explode(',', $row['heskprivileges']);
        $newFeatures = array();
        foreach ($originalFeatures as $innerFeature) {
            if (in_array($innerFeature, $featArray) && strpos($_SESSION['heskprivileges'], $innerFeature) !== false) {
                $newFeatures[] = $innerFeature;
            } elseif (!in_array($innerFeature, $featArray) && strpos($_SESSION['heskprivileges'], $innerFeature) === false) {
                // The user can't modify this, so keep it in
                $newFeatures[] = $innerFeature;
            }
            // If neither, the user removed it.
        }

        $categories = implode(',', $newCategories);
        $features = implode(',', $newFeatures);
    }



    hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "permission_templates`
        SET `categories` = '" . hesk_dbEscape($categories) . "', `heskprivileges` = '" . hesk_dbEscape($features) . "',
            `name` = '" . hesk_dbEscape($name) . "'
        WHERE `id` = " . intval($templateId));

    if ($row['categories'] != $categories || $row['heskprivileges'] != $features) {
        // Any users with this template should have their permissions updated
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `heskprivileges` = '" . hesk_dbEscape($features) . "',
            `categories` = '" . hesk_dbEscape($categories) . "'
            WHERE `permission_template` = " . intval($templateId));
    }

    hesk_process_messages($hesklang['permission_group_updated'], $_SERVER['PHP_SELF'], 'SUCCESS');
}

function create()
{
    global $hesk_settings, $hesklang;

    // Add 'can ban emails' if 'can unban emails' is set (but not added). Same with 'can ban ips'
    $catArray = hesk_POST_array('categories');
    $featArray = hesk_POST_array('features');
    $name = hesk_POST('name');
    validate($featArray, $catArray, true, $name);
    if (in_array('can_unban_emails', $featArray) && !in_array('can_ban_emails', $featArray)) {
        array_push($catArray, 'can_ban_emails');
    }
    if (in_array('can_unban_ips', $featArray) && !in_array('can_ban_ips', $featArray)) {
        array_push($featArray, 'can_ban_ips');
    }

    $categories = implode(',', $catArray);
    $features = implode(',', $featArray);

    hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "permission_templates` (`name`, `heskprivileges`, `categories`)
        VALUES ('" . hesk_dbEscape($name) . "', '" . hesk_dbEscape($features) . "', '" . hesk_dbEscape($categories) . "')");

    hesk_process_messages($hesklang['group_created'], $_SERVER['PHP_SELF'], 'SUCCESS');
}

function validate($features, $categories, $create = false, $name = '')
{
    global $hesklang;

    $errorMarkup = '<ul>';
    $isValid = true;
    if ($create && $name == '') {
        $errorMarkup .= '<li>' . $hesklang['group_name_required'] . '</li>';
        $isValid = false;
    }
    if (count($features) == 0) {
        $errorMarkup .= '<li>' . $hesklang['you_must_select_a_feature'] . '</li>';
        $isValid = false;
    }
    if (count($categories) == 0) {
        $errorMarkup .= '<li>' . $hesklang['you_must_select_a_category'] . '</li>';
        $isValid = false;
    }
    $errorMarkup .= '</ul>';

    if (!$isValid) {
        $error = sprintf($hesklang['permission_group_error'], $errorMarkup);
        hesk_process_messages($error, $_SERVER['PHP_SELF']);
    }
    return true;
}

function deleteTemplate()
{
    global $hesk_settings, $hesklang;

    $id = hesk_GET('id');

    // Admin/Staff templates cannot be deleted!
    if ($id == 1 || $id == 2) {
        hesk_process_messages($hesklang['cannot_delete_admin_or_staff'], $_SERVER['PHP_SELF']);
    }

    // Otherwise delete the template
    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "permission_templates` WHERE `id` = " . intval($id));
    if (hesk_dbAffectedRows() != 1) {
        hesk_process_messages($hesklang['no_group_were_deleted'], $_SERVER['PHP_SELF']);
    }

    // Move all users who used to be in this group to "custom"
    hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `permission_template` = NULL
        WHERE `permission_template` = " . intval($id));

    hesk_process_messages($hesklang['permission_group_deleted'], $_SERVER['PHP_SELF'], 'SUCCESS');
}

?>
