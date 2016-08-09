<?php
/*******************************************************************************
 *  Title: Help Desk Software HESK
 *  Version: 2.6.7 from 18th April 2016
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

/* Make sure the install folder is deleted */
//if (is_dir(HESK_PATH . 'install')) {
//    die('Please delete the <b>install</b> folder from your server for security reasons then refresh this page!');
//}

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/status_functions.inc.php');
require(HESK_PATH . 'inc/mail_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

define('CALENDAR', 1);
define('MAIN_PAGE', 1);
define('PAGE_TITLE', 'ADMIN_HOME');

/* Print header */
require_once(HESK_PATH . 'inc/header_new_admin.inc.php');
require_once(HESK_PATH . 'inc/new_admin_header_and_sidebar.inc.php');
require_once(HESK_PATH . 'hesk_settings.inc.php');
require_once(HESK_PATH . 'inc/common.inc.php');
require_once(HESK_PATH . 'inc/admin_functions.inc.php');
require_once(HESK_PATH . 'inc/status_functions.inc.php');
require_once(HESK_PATH . 'inc/ticket/get_tickets.inc.php');

$statuses = mfh_getAllStatuses();
$search_filter = get_empty_filter();
$search_filter['status'] = array();
foreach ($statuses as $status) {
    if (!$status['IsClosed']) {
        $search_filter['status'][] = $status['ID'];
    }
}
$search_filter['critical_on_top'] = true;


hesk_handle_messages();
?>
    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h1 class="box-title">
                    <?php echo $hesklang['tickets']; ?>
                </h1>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <?php if (!hesk_checkPermission('can_view_tickets', 0)): ?>
                    <p><i><?php echo $hesklang['na_view_tickets']; ?></i></p>
                <?php
                    else:
                        $tickets = get_tickets($search_filter, $hesk_settings);
                ?>
                    <table class="table table-hover">
                        <thead>
                        <?php foreach ($hesk_settings['ticket_list'] as $ticket_header): ?>
                            <th><?php echo $hesk_settings['possible_ticket_list'][$ticket_header]; ?></th>
                        <?php endforeach; ?>
                        </thead>
                    </table>
                <?php endif; ?>
            </div>
            <div class="box-footer clearfix">
                <a href="new_ticket.php" class="btn btn-success"><span class="glyphicon glyphicon-plus-sign"></span> <?php echo $hesklang['nti']; ?></a>
            </div>
        </div>
    </section>

<?php

require_once(HESK_PATH . 'inc/new_footer.inc.php');
exit();
?>
