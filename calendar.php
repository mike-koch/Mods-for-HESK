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
define('PAGE_TITLE', 'CUSTOMER_CALENDAR');
define('MFH_CUSTOMER_CALENDAR', 1);

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');

// Are we in maintenance mode?
hesk_check_maintenance();

hesk_load_database_functions();

hesk_session_start();
/* Connect to database */
hesk_dbConnect();
$modsForHesk_settings = mfh_getSettings();

// Is the calendar enabled?
if ($modsForHesk_settings['enable_calendar'] != '1') {
    hesk_error($hesklang['calendar_disabled']);
}

$categories = [];
$orderBy = $modsForHesk_settings['category_order_column'];
$categorySql = "SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` WHERE `usage` <> 1 AND `type` = '0' ORDER BY '" . $orderBy . "'";
$categoryRs = hesk_dbQuery($categorySql);
while ($row = hesk_dbFetchAssoc($categoryRs))
{
    $row['css_style'] = $row['color'] == null ? 'color: black; border: solid 1px #000' : 'background: ' . $row['color'];
    $categories[] = $row;
}

require_once(HESK_PATH . 'inc/header.inc.php');
?>

<div class="row pad-20">
    <div class="col-lg-3">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4><?php echo $hesklang['calendar_categories']; ?></h4>
            </div>
            <div class="panel-body">
                <ul class="list-unstyled">
                    <?php foreach ($categories as $category): ?>
                        <li class="move-down-20 move-right-20">
                            <div class="checkbox">
                                <input type="checkbox" name="category-toggle" value="<?php echo $category['id']; ?>" checked>
                            </div>
                        <span class="label background-volatile category-label" style="<?php echo $category['css_style']; ?>">
                            <?php echo $category['name']; ?>
                        </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-9">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>
                    <?php echo $hesklang['calendar_title_case']; ?>
                </h4>
            </div>
            <div class="panel-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>
<div class="popover-template" style="display: none">
    <div>
        <div class="popover-location">
            <strong>Location</strong>
            <span></span>
        </div>
        <div class="popover-category">
            <strong>Category</strong>
            <span></span>
        </div>
        <div class="popover-from">
            <strong>From</strong>
            <span></span>
        </div>
        <div class="popover-to">
            <strong>To</strong>
            <span></span>
        </div>
    </div>
</div>
<div style="display: none">
    <p id="lang_error_loading_events"><?php echo $hesklang['error_loading_events']; ?></p>
</div>