<?php

define('IN_SCRIPT', 1);
define('HESK_PATH', '../');
define('PAGE_TITLE', 'ADMIN_LOGS');
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

hesk_checkPermission('can_view_logs');

define('EXTRA_JS', '<script src="'.HESK_PATH.'internal-api/js/view-message-log.js"></script>');

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>
<div class="content-wrapper">
    <section class="content">
    <div class="box">
        <div class="box-header with-border">
            <h1 class="box-title">
                <?php echo $hesklang['search_logs']; ?>
            </h1>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="form-horizontal">
                <div class="form-group">
                    <label for="location" class="control-label col-sm-4">
                        <?php echo $hesklang['custom_place']; ?>
                    </label>
                    <div class="col-sm-8">
                        <input type="text" name="location" class="form-control" placeholder="<?php echo hesk_htmlspecialchars($hesklang['custom_place']); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="date" class="control-label col-sm-4">
                        <?php echo $hesklang['date_logged']; ?>
                    </label>
                    <div class="col-sm-8">
                        <input type="text" name="from-date" class="datepicker form-control white-readonly no-bottom-round-corners no-bottom-border" placeholder="<?php echo hesk_htmlspecialchars($hesklang['from_date']); ?>" readonly>
                        <input type="text" name="to-date" class="datepicker form-control white-readonly no-top-round-corners" placeholder="<?php echo hesk_htmlspecialchars($hesklang['to_date']); ?>" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label for="severity" class="control-label col-sm-4"><?php echo $hesklang['severity']; ?></label>
                    <div class="col-sm-8">
                        <select name="severity" class="form-control">
                            <option value="-1" selected><?php echo $hesklang['all']; ?></option>
                            <option value="0"><?php echo $hesklang['debug']; ?></option>
                            <option value="1"><?php echo $hesklang['info']; ?></option>
                            <option value="2"><?php echo $hesklang['warning_title_case']; ?></option>
                            <option value="3"><?php echo $hesklang['sm_error']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-8 col-sm-offset-4">
                        <button class="btn btn-default" id="search-button"><?php echo $hesklang['search']; ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="box">
        <div class="box-header with-border">
            <h1 class="box-title">
                <?php echo $hesklang['logs']; ?>
            </h1>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <table class="table table-striped" id="results-table">
                <thead>
                <tr>
                    <th><?php echo $hesklang['date']; ?></th>
                    <th><?php echo $hesklang['user']; ?></th>
                    <th><?php echo $hesklang['custom_place']; ?></th>
                    <th><?php echo $hesklang['message']; ?></th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</section>
</div>
<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();