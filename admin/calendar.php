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
define('VALIDATOR', 1);
define('HESK_PATH', '../');
define('PAGE_TITLE', 'ADMIN_CALENDAR');
define('MFH_PAGE_LAYOUT', 'TOP_AND_SIDE');
define('USE_JQUERY_2', 1);

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/mail_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

// Define required constants
if (hesk_checkPermission('can_man_calendar', 0)) {
    define('MFH_CALENDAR', 1);
} else {
    define('MFH_CALENDAR_READONLY', 1);
}

// Is the calendar enabled?
$modsForHesk_settings = mfh_getSettings();
if ($modsForHesk_settings['enable_calendar'] == '0') {
    hesk_error($hesklang['calendar_disabled']);
}

// Get categories for the dropdown
$order_by = $modsForHesk_settings['category_order_column'];
$rs = hesk_dbQuery("SELECT `id`, `name`, `color` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` WHERE `usage` <> 1 ORDER BY `" . hesk_dbEscape($order_by) . "`");
$categories = array();
while ($row = hesk_dbFetchAssoc($rs)) {
    if (!$_SESSION['isadmin'] && !in_array($row['id'], $_SESSION['categories'])) {
        continue;
    }

    $row['css_style'] = $row['color'] == null ? 'color: black; border: solid 1px #000;' : 'border: solid 1px ' . $row['color'] . '; background: ' . $row['color'];
    $categories[] = $row;
}

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>
<aside class="main-sidebar">
    <section class="sidebar" style="height: auto">
        <ul class="sidebar-menu">
            <li class="header text-uppercase"><?php echo $hesklang['calendar_categories']; ?></li>
            <?php foreach ($categories as $category): ?>
                <!-- TODO Clean this up -->
                <li>
                    <div class="ticket-info">
                        <div class="hide-on-overflow no-wrap event-category background-volatile"
                             data-select-toggle="category-toggle" data-name="category-toggle" data-category-value="<?php echo $category['id']; ?>"
                             data-checked="1"
                             data-toggle="tooltip"
                             title="<?php echo $hesklang['click_to_toggle']; ?>"
                             style="<?php echo $category['css_style']; ?>">
                            <?php echo $category['name']; ?>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
            <li>
                <div class="ticket-info">
                    <button id="select-all" class="btn btn-default btn-sm" data-select-all="category-toggle">
                        <?php echo $hesklang['select_all_title_case']; ?>
                    </button>
                    <button id="deselect-all" class="btn btn-default btn-sm" data-deselect-all="category-toggle">
                        <?php echo $hesklang['deselect_all_title_case']; ?>
                    </button>
                </div>
                <script>
                    $('#select-all').click(function() {
                        $('div[data-name="category-toggle"]').attr('data-checked', 1);
                        updateCategoryVisibility();
                    });
                    $('#deselect-all').click(function() {
                        $('div[data-name="category-toggle"]').attr('data-checked', 0);
                        updateCategoryVisibility();
                    });
                </script>
            </li>
            <li class="header text-uppercase"><?php echo $hesklang['legend']; ?></li>
            <li>
                <div class="ticket-info">
                    <i class="fa fa-calendar"></i> <?php echo $hesklang['event']; ?>
                </div>
            </li>
            <li>
                <div class="ticket-info">
                    <i class="fa fa-ticket"></i> <?php echo $hesklang['ticket']; ?>
                </div>
            </li>
            <li>
                <div class="ticket-info">
                    <i class="fa fa-exclamation-triangle"></i> <?php echo $hesklang['overdue_ticket_legend']; ?>
                </div>
            </li>
        </ul>
    </section>
</aside>
<div class="content-wrapper">
    <section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h1 class="box-title">
                        <?php echo $hesklang['calendar_title_case']; ?>
                    </h1>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body no-padding">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</section>
</div>
<div class="modal fade" id="create-event-modal" tabindex="-1" role="dialog" style="overflow: hidden">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="cursor: move">
                <button type="button" class="close cancel-callback" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <?php echo $hesklang['create_event']; ?>
                </h4>
            </div>
            <form id="create-form" class="form-horizontal" data-toggle="validator">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name" class="col-sm-3 control-label">
                                    <?php echo $hesklang['event_title']; ?>
                                    <i class="fa fa-question-circle settingsquestionmark"
                                        data-toggle="tooltip"
                                        title="<?php echo htmlspecialchars($hesklang['event_title_tooltip']); ?>"></i></label>
                                <div class="col-sm-9">
                                    <input type="text" name="name" class="form-control" placeholder="<?php echo htmlspecialchars($hesklang['event_title']); ?>"
                                           data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                           required>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="location" class="col-sm-3 control-label">
                                    <?php echo $hesklang['event_location']; ?>
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="<?php echo htmlspecialchars($hesklang['event_location_tooltip']); ?>"></i>
                                </label>
                                <div class="col-sm-9">
                                    <input type="text" name="location" class="form-control"
                                           placeholder="<?php echo htmlspecialchars($hesklang['event_location']); ?>">
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="category" class="col-sm-3 control-label">
                                    <?php echo $hesklang['category']; ?>
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="<?php echo htmlspecialchars($hesklang['event_category_tooltip']); ?>"></i>
                                </label>
                                <div class="col-sm-9">
                                    <select name="category" class="form-control"
                                        pattern="[0-9]+"
                                        data-error="<?php echo htmlspecialchars($hesklang['sel_app_cat']); ?>" required>
                                        <?php
                                        if ($hesk_settings['select_cat']) {
                                            echo '<option value="">'.$hesklang['select'].'</option>';
                                        }
                                        foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" data-color="<?php echo htmlspecialchars($category['color']); ?>">
                                                <?php echo $category['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start-date" class="col-sm-6 control-label">
                                    <?php echo $hesklang['event_start']; ?>
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="<?php echo htmlspecialchars($hesklang['event_start_tooltip']); ?>"></i>
                                </label>
                                <div class="col-sm-6">
                                    <input type="text" name="start-date" class="form-control datepicker"
                                           placeholder="<?php echo htmlspecialchars($hesklang['event_start_date']); ?>"
                                           data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                           required>
                                    <input type="text" name="start-time" class="form-control clockpicker"
                                           placeholder="<?php echo htmlspecialchars($hesklang['event_start_time']); ?>"
                                           data-placement="left" data-align="top" data-autoclose="true">
                                    <div class="help-block with-errors"></div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="all-day"> <?php echo $hesklang['event_all_day']; ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end-date" class="col-sm-6 control-label">
                                    <?php echo $hesklang['event_end']; ?>
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="<?php echo htmlspecialchars($hesklang['event_end_tooltip']); ?>"></i>
                                </label>
                                <div class="col-sm-6">
                                    <input type="text" name="end-date" class="form-control datepicker"
                                           placeholder="<?php echo htmlspecialchars($hesklang['event_end_date']); ?>"
                                           data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                           required>
                                    <input type="text" name="end-time" class="form-control clockpicker"
                                           data-placement="left"
                                           data-align="top"
                                           data-autoclose="true"
                                           placeholder="<?php echo htmlspecialchars($hesklang['event_end_time']); ?>">
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="reminder" class="col-sm-3 control-label">
                                    <?php echo $hesklang['event_reminder']; ?>
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="<?php echo htmlspecialchars($hesklang['event_reminder_tooltip']); ?>"></i>
                                </label>
                                <div class="col-sm-2">
                                    <input type="text" name="reminder-value" class="form-control" placeholder="#">
                                </div>
                                <div class="col-sm-4">
                                    <select name="reminder-unit" class="form-control">
                                        <option value="0"><?php echo $hesklang['event_min_before_event']; ?></option>
                                        <option value="1"><?php echo $hesklang['event_hours_before_event']; ?></option>
                                        <option value="2"><?php echo $hesklang['event_days_before_event']; ?></option>
                                        <option value="3"><?php echo $hesklang['event_weeks_before_event']; ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="comments" class="col-sm-3 control-label">
                                    <?php echo $hesklang['event_comments']; ?>
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="<?php echo htmlspecialchars($hesklang['event_comments_tooltip']); ?>"></i>
                                </label>
                                <div class="col-sm-9">
                                    <textarea name="comments" class="form-control" placeholder="<?php echo htmlspecialchars($hesklang['event_comments']); ?>"></textarea>
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
                            <span><?php echo $hesklang['cancel']; ?></span>
                        </button>
                        <button type="submit" class="btn btn-success callback-btn">
                            <i class="fa fa-check-circle"></i>
                            <span><?php echo $hesklang['save']; ?></span>
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
                                    <?php echo $hesklang['event_title']; ?>
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="<?php echo htmlspecialchars($hesklang['event_title_tooltip']); ?>"></i></label>
                                <div class="col-sm-9">
                                    <input type="text" name="name" class="form-control"
                                           placeholder="<?php echo htmlspecialchars($hesklang['event_title']); ?>"
                                           data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                           required>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="location" class="col-sm-3 control-label">
                                    <?php echo $hesklang['event_location']; ?>
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="<?php echo htmlspecialchars($hesklang['event_location_tooltip']); ?>"></i>
                                </label>
                                <div class="col-sm-9">
                                    <input type="text" name="location" class="form-control"
                                           placeholder="<?php echo htmlspecialchars($hesklang['event_location']); ?>">
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="category" class="col-sm-3 control-label">
                                    <?php echo $hesklang['category']; ?>
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="<?php echo htmlspecialchars($hesklang['event_category_tooltip']); ?>"></i>
                                </label>
                                <div class="col-sm-9">
                                    <select name="category" class="form-control"
                                            pattern="[0-9]+"
                                            data-error="<?php echo htmlspecialchars($hesklang['sel_app_cat']); ?>" required>
                                        <?php
                                        if ($hesk_settings['select_cat']) {
                                            echo '<option value="">'.$hesklang['select'].'</option>';
                                        }
                                        foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" data-color="<?php echo $category['color']; ?>">
                                                <?php echo $category['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start-date" class="col-sm-6 control-label">
                                    <?php echo $hesklang['event_start']; ?>
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="<?php echo htmlspecialchars($hesklang['event_start_tooltip']); ?>"></i>
                                </label>
                                <div class="col-sm-6">
                                    <input type="text" name="start-date" class="form-control datepicker"
                                           placeholder="<?php echo htmlspecialchars($hesklang['event_start_date']); ?>"
                                           data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                           required>
                                    <input type="text" name="start-time" class="form-control clockpicker"
                                           placeholder="<?php echo htmlspecialchars($hesklang['event_start_time']); ?>"
                                           data-placement="left" data-align="top" data-autoclose="true">
                                    <div class="help-block with-errors"></div>

                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="all-day"> <?php echo $hesklang['event_all_day']; ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end-date" class="col-sm-6 control-label">
                                    <?php echo $hesklang['event_end']; ?>
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="<?php echo htmlspecialchars($hesklang['event_end_tooltip']); ?>"></i>
                                </label>
                                <div class="col-sm-6">
                                    <input type="text" name="end-date" class="form-control datepicker"
                                           placeholder="<?php echo htmlspecialchars($hesklang['event_end_date']); ?>"
                                           data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                           required>
                                    <input type="text" name="end-time" class="form-control clockpicker"
                                           data-placement="left" data-align="top" data-autoclose="true"
                                           placeholder="<?php echo htmlspecialchars($hesklang['event_end_time']); ?>">
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="reminder" class="col-sm-3 control-label">
                                    <?php echo $hesklang['event_reminder']; ?>
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="<?php echo htmlspecialchars($hesklang['event_reminder_tooltip']); ?>"></i>
                                </label>
                                <div class="col-sm-2">
                                    <input type="text" name="reminder-value" class="form-control" placeholder="#">
                                </div>
                                <div class="col-sm-4">
                                    <select name="reminder-unit" class="form-control">
                                        <option value="0"><?php echo $hesklang['event_min_before_event']; ?></option>
                                        <option value="1"><?php echo $hesklang['event_hours_before_event']; ?></option>
                                        <option value="2"><?php echo $hesklang['event_days_before_event']; ?></option>
                                        <option value="3"><?php echo $hesklang['event_weeks_before_event']; ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="comments" class="col-sm-3 control-label">
                                    <?php echo $hesklang['event_comments']; ?>
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="tooltip"
                                       title="<?php echo htmlspecialchars($hesklang['event_comments_tooltip']); ?>"></i>
                                </label>
                                <div class="col-sm-9">
                                    <textarea name="comments" class="form-control" placeholder="<?php echo htmlspecialchars($hesklang['event_comments']); ?>"></textarea>
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
                            <span><?php echo $hesklang['delete']; ?></span>
                        </button>
                        <a href="#" class="btn btn-primary" id="create-ticket-button">
                            <i class="fa fa-plus"></i>
                            <span><?php echo $hesklang['event_create_ticket']; ?></span>
                        </a>
                        <button type="button" class="btn btn-default cancel-callback" data-dismiss="modal">
                            <i class="fa fa-times-circle"></i>
                            <span><?php echo $hesklang['cancel']; ?></span>
                        </button>
                        <button type="submit" class="btn btn-success callback-btn">
                            <i class="fa fa-check-circle"></i>
                            <span><?php echo $hesklang['save']; ?></span>
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
            <strong><?php echo $hesklang['event_location']; ?></strong>
            <span></span>
        </div>
        <div class="popover-category">
            <strong><?php echo $hesklang['category']; ?></strong>
            <span></span>
        </div>
        <div class="popover-from">
            <strong><?php echo $hesklang['from']; ?></strong>
            <span></span>
        </div>
        <div class="popover-to">
            <strong><?php echo $hesklang['to_title_case']; ?></strong>
            <span></span>
        </div>
        <div class="popover-comments">
            <strong><?php echo $hesklang['event_comments']; ?></strong>
            <span></span>
        </div>
    </div>
</div>
<div class="ticket-popover-template" style="display: none">
    <div>
        <div class="popover-tracking-id">
            <strong><?php echo $hesklang['trackID']; ?></strong>
            <span></span>
        </div>
        <div class="popover-owner">
            <strong><?php echo $hesklang['owner']; ?></strong>
            <span></span>
        </div>
        <div class="popover-subject">
            <strong><?php echo $hesklang['subject']; ?></strong>
            <span></span>
        </div>
        <div class="popover-category">
            <strong><?php echo $hesklang['category']; ?></strong>
            <span></span>
        </div>
        <div class="popover-priority">
            <strong><?php echo $hesklang['priority']; ?></strong>
            <span></span>
        </div>
    </div>
</div>
<div style="display: none">
    <p id="lang_error_loading_events"><?php echo $hesklang['error_loading_events']; ?></p>
    <p id="lang_error_deleting_event"><?php echo $hesklang['error_deleting_event']; ?></p>
    <p id="lang_event_deleted"><?php echo $hesklang['event_deleted']; ?></p>
    <p id="lang_event_created"><?php echo $hesklang['event_created']; ?></p>
    <p id="lang_error_creating_event"><?php echo $hesklang['error_creating_event']; ?></p>
    <p id="lang_event_updated"><?php echo $hesklang['event_updated']; ?></p>
    <p id="lang_error_updating_event"><?php echo $hesklang['error_updating_event']; ?></p>
    <p id="lang_ticket_due_date_updated"><?php echo $hesklang['ticket_due_date_updated']; ?></p>
    <p id="lang_error_updating_ticket_due_date"><?php echo $hesklang['error_updating_ticket_due_date']; ?></p>
    <p id="setting_first_day_of_week"><?php echo $modsForHesk_settings['first_day_of_week']; ?></p>
    <p id="setting_default_view">
        <?php
        $view_array = array(
            0 => 'month',
            1 => 'agendaWeek',
            2 => 'agendaDay',
        );
        echo $view_array[$_SESSION['default_calendar_view']];
        ?>
    </p>
</div>
<?php

require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


/*** START FUNCTIONS ***/
