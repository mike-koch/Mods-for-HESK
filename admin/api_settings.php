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
define('HESK_PATH', '../');
define('PAGE_TITLE', 'ADMIN_SETTINGS');

// Make sure the install folder is deleted
if (is_dir(HESK_PATH . 'install')) {
    die('Please delete the <b>install</b> folder from your server for security reasons then refresh this page!');
}

// Get all the required files and functions
require(HESK_PATH . 'hesk_settings.inc.php');

// Save the default language for the settings page before choosing user's preferred one
$hesk_settings['language_default'] = $hesk_settings['language'];
require(HESK_PATH . 'inc/common.inc.php');
$hesk_settings['language'] = $hesk_settings['language_default'];
require(HESK_PATH . 'inc/admin_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

// Check permissions for this feature
hesk_checkPermission('can_man_settings');

// Print header
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

// Print main manage users page
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>

<div class="row move-down-20">
    <div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                API Information
            </div>
            <table class="table table-striped table-fixed">
                <tr>
                    <td class="text-right">
                        API Version
                    </td>
                    <td class="pad-right-10 warning">
                        <?php echo $hesklang['beta_text']; ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-right">
                        External API
                    </td>
                    <td class="pad-right-10 success">
                        Enabled
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="col-sm-8">
        <h3>API Settings</h3>
        <div class="footerWithBorder blankSpace"></div>
        <ul class="nav nav-tabs">
            <li class="active"><a href="#general" data-toggle="tab"><?php echo $hesklang['tab_1']; ?></a></li>
            <li><a href="#user-security" data-toggle="tab">User Security</a></li>
            <li><a href="#" target="_blank">API Documentation</a></li>
        </ul>
        <div class="tab-content summaryList tabPadding">
            <div class="tab-pane fade in active" id="general">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label for="public-api" class="col-sm-3 control-label">
                            Public API
                            <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                                title="Public API"
                                data-content="Enable or Disable the Public REST API."></i>
                        </label>
                        <div class="col-sm-9">
                            <div class="btn-group" data-toggle="buttons">
                                <label class="btn btn-success active">
                                    <input type="radio" name="public-api" value="1" checked> <i class="fa fa-check-circle"></i> Enable
                                </label>
                                <label class="btn btn-danger">
                                    <input type="radio" name="public-api" value="0"> <i class="fa fa-times-circle"></i> Disable
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="tab-pane fade in" id="user-security">
                <p>User Security Stuff here (tokens, etc)</p>
            </div>
        </div>
    </div>

    <?php
    require_once(HESK_PATH . 'inc/footer.inc.php');
    exit();