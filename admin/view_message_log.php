<?php

define('IN_SCRIPT', 1);
define('HESK_PATH', '../');
define('PAGE_TITLE', 'ADMIN_TOOLS');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();
/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>

    <div class="row pad-20">
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Search Logs
                </div>
                <div class="panel-body">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="location" class="control-label col-sm-4">Location</label>
                            <div class="col-sm-8">
                                <input type="text" name="location" class="form-control" placeholder="Location">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="date" class="control-label col-sm-4">Date Logged</label>
                            <div class="col-sm-8">
                                <input type="text" name="from-date" class="datepicker form-control white-readonly" placeholder="From Date" readonly>
                                <input type="text" name="to-date" class="datepicker form-control white-readonly" placeholder="To Date" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="severity" class="control-label col-sm-4">Severity</label>
                            <div class="col-sm-8">
                                <select name="severity" class="form-control">
                                    <option value="-1" selected>All</option>
                                    <option value="0">Debug</option>
                                    <option value="1">Info</option>
                                    <option value="2">Warning</option>
                                    <option value="3">Error</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-default">Search</button>
                </div>
            </div>
        </div>
        <div class="col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Logs
                </div>
            </div>
        </div>
    </div>

<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();