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
define('VALIDATOR', 1);
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

/* Check permissions for this feature */
//hesk_checkPermission('can_service_msg');

// Define required constants
define('MFH_CALENDAR', 1);

// Get categories for the dropdown
$rs = hesk_dbQuery("SELECT `id`, `name` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ORDER BY `cat_order`");
$categories = [];
while ($row = hesk_dbFetchAssoc($rs)) {
    $categories[] = $row;
}

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>

<div class="row pad-20">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Calendar</h4>
            </div>
            <div class="panel-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="create-event-modal" tabindex="-1" role="dialog" style="overflow: hidden">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="cursor: move">
                <button type="button" class="close cancel-callback" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Create Event</h4>
            </div>
            <form id="create-form" class="form-horizontal" data-toggle="validator">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name" class="col-sm-3 control-label">
                                    Title
                                    <i class="fa fa-question-circle settingsquestionmark"
                                        data-toggle="tooltip"
                                        title="The title of the event"></i></label>
                                <div class="col-sm-9">
                                    <input type="text" name="name" class="form-control" placeholder="Title"
                                           data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                           required>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="location" class="col-sm-3 control-label">
                                    Location
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="The location of the event"></i>
                                </label>
                                <div class="col-sm-9">
                                    <input type="text" name="location" class="form-control" placeholder="Location">
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="category" class="col-sm-3 control-label">
                                    Category
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="Category for the event"></i>
                                </label>
                                <div class="col-sm-9">
                                    <select name="category" class="form-control">
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>">
                                                <?php echo $category['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start-date" class="col-sm-6 control-label">
                                    Start
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="The starting date (and time) of the event"></i>
                                </label>
                                <div class="col-sm-6">
                                    <input type="text" name="start-date" class="form-control datepicker" placeholder="Start Date"
                                           data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                           required>
                                    <input type="text" name="start-time" class="form-control clockpicker" placeholder="Start Time" data-placement="left" data-align="top" data-autoclose="true">
                                    <div class="help-block with-errors"></div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="all-day"> All day
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end-date" class="col-sm-6 control-label">
                                    End
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="The ending date (and time) of the event"></i>
                                </label>
                                <div class="col-sm-6">
                                    <input type="text" name="end-date" class="form-control datepicker" placeholder="End Date"
                                           data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                           required>
                                    <input type="text" name="end-time" class="form-control clockpicker" data-placement="left" data-align="top" data-autoclose="true" placeholder="End Time">
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="comments" class="col-sm-3 control-label">
                                    Comments
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="Additional comments about the event"></i>
                                </label>
                                <div class="col-sm-9">
                                    <textarea name="comments" class="form-control" placeholder="Comments"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="action" value="create">
                    <div class="btn-group">
                        <button type="button" class="btn btn-default cancel-callback" data-dismiss="modal">
                            <i class="fa fa-times-circle"></i>
                            <span>Cancel</span>
                        </button>
                        <button type="submit" class="btn btn-success callback-btn">
                            <i class="fa fa-check-circle"></i>
                            <span>Save</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php // End create modal, begin edit modal ?>
<div class="modal fade" id="edit-event-modal" tabindex="-1" role="dialog" style="overflow: hidden">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="cursor: move">
                <button type="button" class="close cancel-callback" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Edit Event</h4>
            </div>
            <form id="edit-form" class="form-horizontal" data-toggle="validator">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name" class="col-sm-3 control-label">
                                    Title
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="The title of the event"></i></label>
                                <div class="col-sm-9">
                                    <input type="text" name="name" class="form-control" placeholder="Title"
                                           data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                           required>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="location" class="col-sm-3 control-label">
                                    Location
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="The location of the event"></i>
                                </label>
                                <div class="col-sm-9">
                                    <input type="text" name="location" class="form-control" placeholder="Location">
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="category" class="col-sm-3 control-label">
                                    Category
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="Category for the event"></i>
                                </label>
                                <div class="col-sm-9">
                                    <select name="category" class="form-control">
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>">
                                                <?php echo $category['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start-date" class="col-sm-6 control-label">
                                    Start
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="The starting date (and time) of the event"></i>
                                </label>
                                <div class="col-sm-6">
                                    <input type="text" name="start-date" class="form-control datepicker" placeholder="Start Date"
                                           data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                           required>
                                    <input type="text" name="start-time" class="form-control clockpicker" placeholder="Start Time" data-placement="left" data-align="top" data-autoclose="true">
                                    <div class="help-block with-errors"></div>

                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="all-day"> All day
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end-date" class="col-sm-6 control-label">
                                    End
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="The ending date (and time) of the event"></i>
                                </label>
                                <div class="col-sm-6">
                                    <input type="text" name="end-date" class="form-control datepicker" placeholder="End Date"
                                           data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                           required>
                                    <input type="text" name="end-time" class="form-control clockpicker" data-placement="left" data-align="top" data-autoclose="true" placeholder="End Time">
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="comments" class="col-sm-3 control-label">
                                    Comments
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="Additional comments about the event"></i>
                                </label>
                                <div class="col-sm-9">
                                    <textarea name="comments" class="form-control" placeholder="Comments"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="id">
                    <div class="btn-group">
                        <button type="button" class="btn btn-danger" id="delete-button">
                            <i class="fa fa-trash"></i>
                            <span>Delete</span>
                        </button>
                        <a href="#" class="btn btn-primary" id="create-ticket-button">
                            <i class="fa fa-plus"></i>
                            <span>Create Ticket</span>
                        </a>
                        <button type="button" class="btn btn-default cancel-callback" data-dismiss="modal">
                            <i class="fa fa-times-circle"></i>
                            <span>Cancel</span>
                        </button>
                        <button type="submit" class="btn btn-success callback-btn">
                            <i class="fa fa-check-circle"></i>
                            <span>Save</span>
                        </button>
                    </div>
                </div>
            </form>
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

<?php

require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


/*** START FUNCTIONS ***/
