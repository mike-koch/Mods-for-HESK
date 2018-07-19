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
define('PAGE_TITLE', 'ADMIN_TICKET');
define('MFH_PAGE_LAYOUT', 'TOP_ONLY');

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
define('AUTO_RELOAD',1);

/* Check permissions for this feature */
hesk_checkPermission('can_view_tickets');


/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');

?>
<div class="content-wrapper">
    <section class="content">
    <?php hesk_handle_messages(); ?>
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
            <div class="row">
                <div class="col-xs-6 text-left">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" onclick="toggleAutoRefresh(this);" id="reloadCB">
                            <?php echo $hesklang['arp']; ?>
                            <span id="timer"></span>
                        </label>
                    </div>
                    <script type="text/javascript">heskCheckReloading();</script>
                </div>
                <div class="col-xs-6 text-right">
                    <a href="new_ticket.php" class="btn btn-success">
                        <span class="glyphicon glyphicon-plus-sign"></span>
                        <?php echo $hesklang['nti']; ?>
                    </a>
                </div>
            </div>
            <?php
            /* Print the list of tickets */
            $is_search = 1;
            require_once(HESK_PATH . 'inc/print_tickets.inc.php');
            echo '<br>';
            /* Update staff default settings? */
            if (!empty($_GET['def'])) {
                hesk_updateStaffDefaults();
            }

            /* Print forms for listing and searching tickets */
            require_once(HESK_PATH . 'inc/show_search_form.inc.php');
            ?>
        </div>
    </div>
    <?php

    /* Clean unneeded session variables */
    hesk_cleanSessionVars('hide');
    ?>
</section>
</div>
<?php


/* Print footer */
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();
?>


