<?php
/*******************************************************************************
 *  Title: Help Desk Software HESK
 *  Version: 2.6.5 from 28th August 2015
 *  Author: Klemen Stirn
 *  Website: https://www.hesk.com
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
define('PAGE_TITLE', 'ADMIN_SETTINGS');
define('MFH_PAGE_LAYOUT', 'TOP_AND_SIDE');

// Make sure the install folder is deleted
if (is_dir(HESK_PATH . 'install')) {
    die('Please delete the <b>install</b> folder from your server for security reasons then refresh this page!');
}

// Get all the required files and functions
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/mail_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

// Check permissions for this feature
hesk_checkPermission('can_man_settings');

$modsForHesk_settings = mfh_getSettings();

define('EXTRA_JS', '<script src="'.HESK_PATH.'internal-api/js/api-settings.js"></script>');
// Print header
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');


// Print main manage users page
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>
<div class="content-wrapper">
    <section class="content">
    <div class="box">
        <div class="box-header with-border">
            <h1 class="box-title">
                <?php echo $hesklang['api_information']; ?>
            </h1>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <table class="table table-striped table-fixed">
                <tr>
                    <td class="text-right">
                        <?php echo $hesklang['api_version']; ?>
                    </td>
                    <td class="warning">
                        <?php echo $hesklang['beta_text']; ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-right">
                        <?php echo $hesklang['external_api']; ?>
                    </td>
                    <td class="success" id="public-api-sidebar">
                        <?php
                        $enabled = $modsForHesk_settings['public_api'] == '1' ? '' : 'hide';
                        $disabled = $modsForHesk_settings['public_api'] == '1' ? 'hide' : '';
                        ?>
                        <span id="public-api-sidebar-disabled" class="<?php echo $disabled; ?>">
                            <?php echo $hesklang['disabled_title_case']; ?>
                        </span>
                        <span id="public-api-sidebar-enabled"  class="<?php echo $enabled; ?>">
                            <?php echo $hesklang['enabled_title_case']; ?>
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="box">
        <div class="box-header with-border">
            <h1 class="box-title">
                <?php echo $hesklang['api_settings']; ?>
            </h1>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#general" data-toggle="tab"><?php echo $hesklang['tab_1']; ?></a></li>
                <li><a href="#user-security" data-toggle="tab"><?php echo $hesklang['user_security']; ?></a></li>
                <li><a href="#" target="_blank"><?php echo $hesklang['api_documentation']; ?> <i class="fa fa-external-link"></i></a></li>
            </ul>
            <div class="tab-content summaryList tabPadding">
                <div class="tab-pane fade in active" id="general">
                    <form class="form-horizontal">
                        <div class="form-group">
                            <label for="public-api" class="col-sm-3 control-label">
                                <?php echo $hesklang['external_api']; ?>
                                <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                                   title="<?php echo $hesklang['external_api']; ?>"
                                   data-content="<?php echo $hesklang['external_api_help']; ?>"></i>
                            </label>
                            <div class="col-sm-9">
                            <span class="btn-group" data-toggle="buttons">
                                <?php
                                $on = $modsForHesk_settings['public_api'] == '1' ? 'active' : '';
                                $off = $modsForHesk_settings['public_api'] == '1' ? '' : 'active';
                                ?>
                                <label id="enable-api-button" class="btn btn-success <?php echo $on; ?>">
                                    <input type="radio" name="public-api" value="1"> <i class="fa fa-check-circle"></i>
                                    <?php echo $hesklang['enable']; ?>
                                </label>
                                <label id="disable-api-button" class="btn btn-danger <?php echo $off; ?>">
                                    <input type="radio" name="public-api" value="0"> <i class="fa fa-times-circle"></i>
                                    <?php echo $hesklang['disable']; ?>
                                </label>
                            </span>
                            <span>
                                <i id="public-api-success" class="fa fa-check-circle fa-2x green hide media-middle"
                                   data-toggle="tooltip" title="<?php echo $hesklang['changes_saved']; ?>"></i>
                                <i id="public-api-failure" class="fa fa-times-circle fa-2x red hide media-middle"
                                   data-toggle="tooltip" title="<?php echo $hesklang['save_failed_check_logs']; ?>"></i>
                                <i id="public-api-saving" class="fa fa-spin fa-spinner fa-2x hide media-middle"
                                   data-toggle="tooltip" title="<?php echo $hesklang['saving']; ?>"></i>
                            </span>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="tab-pane fade in" id="user-security">
                    <?php
                    $users = array();
                    $userRs = hesk_dbQuery("SELECT `id`, `user`, `name` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `active` = '1'");
                    while ($row = hesk_dbFetchAssoc($userRs)) {
                        $row['number_of_tokens'] = 0;
                        $users[$row['id']] = $row;
                    }
                    $tokensRs = hesk_dbQuery("SELECT `user_id`, 1 FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "user_api_tokens`");
                    while ($row = hesk_dbFetchAssoc($tokensRs)) {
                        $users[$row['user_id']]['number_of_tokens']++;
                    }
                    ?>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th><?php echo $hesklang['username']; ?></th>
                            <th><?php echo $hesklang['name']; ?></th>
                            <th><?php echo $hesklang['number_of_tokens']; ?></th>
                            <th><?php echo $hesklang['actions']; ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($users as $row):
                            ?>
                            <tr>
                                <td><?php echo $row['user']; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                <td id="token-<?php echo $row['id']; ?>-count"><?php echo $row['number_of_tokens']; ?></td>
                                <td>
                                <span class="btn-group">
                                    <button class="btn btn-default btn-xs" onclick="generateToken(<?php echo $row['id']; ?>)">
                                        <i class="fa fa-plus-circle"></i>
                                        <?php echo $hesklang['generate_new_token']; ?>
                                    </button>
                                    <button class="btn btn-danger btn-xs" onclick="clearTokens(<?php echo $row['id']; ?>)">
                                        <i class="fa fa-times"></i>
                                        <?php echo $hesklang['revoke_all_tokens']; ?>
                                    </button>
                                </span>
                                <span>
                                    <i id="token-<?php echo $row['id']; ?>-success" class="fa fa-check-circle fa-2x green hide media-middle"
                                       data-toggle="tooltip" title="<?php echo $hesklang['changes_saved']; ?>"></i>
                                    <i id="token-<?php echo $row['id']; ?>-failure" class="fa fa-times-circle fa-2x red hide media-middle"
                                       data-toggle="tooltip" title="<?php echo $hesklang['save_failed_check_logs']; ?>"></i>
                                    <i id="token-<?php echo $row['id']; ?>-saving" class="fa fa-spin fa-spinner fa-2x hide media-middle"
                                       data-toggle="tooltip" title="<?php echo $hesklang['saving']; ?>"></i>
                                </span>
                                </td>
                            </tr>
                            <tr id="token-<?php echo $row['id']; ?>-created" class="success hide">
                                <td colspan="4">
                                    <?php echo $hesklang['generated_token_colon']; ?> <code class="token"></code>
                                    <p><b><?php echo $hesklang['record_this_token_warning']; ?></b></p>
                                </td>
                            </tr>
                            <tr id="token-<?php echo $row['id']; ?>-reset" class="success hide">
                                <td colspan="4">
                                    <p><?php echo $hesklang['all_tokens_revoked']; ?></p>
                                </td>
                            </tr>
                            <?php
                        endforeach;
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
</div>

<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();