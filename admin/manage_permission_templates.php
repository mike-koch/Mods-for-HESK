<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.6.2 from 18th March 2015
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

define('IN_SCRIPT',1);
define('HESK_PATH','../');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

/* Check permissions for this feature */
//TODO Create and use new permission here
hesk_checkPermission('can_man_cat');

/* What should we do? */
if ( $action = hesk_REQUEST('a') )
{
	if ($action == 'linkcode')       {generate_link_code();}
	elseif ( defined('HESK_DEMO') )  {hesk_process_messages($hesklang['ddemo'], 'manage_categories.php', 'NOTICE');}
}

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>

<script language="Javascript" type="text/javascript"><!--
function confirm_delete()
{
    if (confirm('<?php echo hesk_makeJsString($hesklang['confirm_del_cat']); ?>')) {return true;}
else {return false;}
}
//-->
</script>

<?php 
    $res = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."permission_templates` ORDER BY `name` ASC");
    $templates = array();
    while ($row = hesk_dbFetchAssoc($res)) {
        array_push($templates, $row);
    }
    $featureArray = hesk_getFeatureArray();
    $res = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` ORDER BY `name` ASC");
    $categories = array();
    while ($row = hesk_dbFetchAssoc($res)) {
        array_push($categories, $row);
    }
?>
<div class="row" style="margin-top: 20px">
    <div class="col-md-10 col-md-offset-1">
            <h3><?php echo $hesklang['manage_permission_templates']; ?> <i class="fa fa-question-circle settingsquestionmark"></i></h3>
            <div class="footerWithBorder blankSpace"></div>
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
                                title="<?php echo $hesklang['view_permissions_for_this_template'] ?>"></i></a>
                        <?php if ($row['heskprivileges'] == 'ALL' && $row['categories'] == 'ALL'): ?>
                            <i class="fa fa-star icon-link orange" data-toggle="tooltip"
                                title="<?php echo $hesklang['template_has_admin_privileges']; ?>"></i>
                        <?php else: ?>
                            <i class="fa fa-star-o icon-link gray" data-toggle="tooltip"
                               title="<?php echo $hesklang['template_has_no_admin_privileges']; ?>"></i>
                        <?php endif; ?>
                        <i class="fa fa-times icon-link red" data-toggle="tooltip"
                           title="<?php echo $hesklang['delete']; ?>"></i>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
foreach ($templates as $template) {
    createModal($template, $featureArray, $categories);
}

require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


/*** START FUNCTIONS ***/
function getNumberOfUsersWithPermissionGroup($templateId) {
    global $hesk_settings;

    $res = hesk_dbQuery("SELECT 1 FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `permission_template` = ".intval($templateId));
    return hesk_dbNumRows($res);
}

function createModal($template, $features, $categories) {
    global $hesklang;

    $showNotice = true;
    $disabled = 'checked="checked" disabled';
    $enabledFeatures = array();
    $enabledCategories = array();
    if ($template['heskprivileges'] != 'ALL') {
        $showNotice = false;
        $disabled = '';
        $enabledFeatures = explode(',', $template['heskprivileges']);
        $enabledCategories = explode(',', $template['categories']);
    }
    ?>
    <div class="modal fade" id="modal-template-<?php echo $template['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_permission_templates.php" role="form">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><?php echo sprintf($hesklang['permissions_for_template'], $template['name']); ?></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <?php if ($showNotice): ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> <?php echo $hesklang['template_is_admin_cannot_change']; ?>
                                </div>
                            <?php endif; ?>
                            <div class="col-md-6 col-sm-12">
                                <h4><?php echo $hesklang['menu_cat']; ?></h4>
                                <div class="footerWithBorder blankSpace"></div>
                                <?php foreach ($categories as $category): ?>
                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="cat-<?php echo $category['id']; ?>" <?php echo $disabled; ?>>
                                                <?php echo $category['name']; ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <h4><?php echo $hesklang['allow_feat']; ?></h4>
                                <div class="footerWithBorder blankSpace"></div>
                                <?php foreach ($features as $feature): ?>
                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="<?php echo $feature; ?>" <?php echo $disabled; ?>>
                                                <?php echo $hesklang[$feature]; ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group">
                            <input type="submit" class="btn btn-primary" value="<?php echo $hesklang['save_changes']; ?>">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $hesklang['close_modal']; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}
?>
