<?php

define('IN_SCRIPT', 1);
define('HESK_PATH', '../');
define('PAGE_TITLE', 'ADMIN_STATUSES');
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

hesk_checkPermission('can_man_ticket_statuses');

define('WYSIWYG', 1);

// Are we performing an action?
if (isset($_REQUEST['a'])) {
    if (defined('HESK_DEMO')) {
        hesk_process_messages($hesklang['cannot_edit_status_demo'], 'manage_statuses.php');
    } elseif ($_REQUEST['a'] == 'create') {
        createStatus();
    } elseif ($_REQUEST['a'] == 'update') {
        updateStatus();
    } elseif ($_REQUEST['a'] == 'delete') {
        deleteStatus();
    } elseif ($_REQUEST['a'] == 'sort') {
        moveStatus();
    } elseif ($_REQUEST['a'] == 'save') {
        save();
    }
}

$modsForHesk_settings = mfh_getSettings();


/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>
<div class="content-wrapper">
    <section class="content">
    <div class="box">
        <div class="box-body">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs" role="tablist">
                    <?php
                    // Show a link to banned_emails.php if user has permission
                    if (hesk_checkPermission('can_ban_emails', 0)) {
                        echo '
            <li role="presentation">
                <a title="' . $hesklang['banemail'] . '" href="banned_emails.php">' . $hesklang['banemail'] . '</a>
            </li>
            ';
                    }
                    if (hesk_checkPermission('can_ban_ips', 0)) {
                        echo '
            <li role="presentation">
                <a title="' . $hesklang['banip'] . '" href="banned_ips.php">' . $hesklang['banip'] . '</a>
            </li>';
                    }
                    // Show a link to status_message.php if user has permission to do so
                    if (hesk_checkPermission('can_service_msg', 0)) {
                        echo '
            <li role="presentation">
                <a title="' . $hesklang['sm_title'] . '" href="service_messages.php">' . $hesklang['sm_title'] . '</a>
            </li>';
                    }
                    if (hesk_checkPermission('can_man_email_tpl', 0)) {
                        echo '
            <li role="presentation">
                <a title="' . $hesklang['email_templates'] . '" href="manage_email_templates.php">' . $hesklang['email_templates'] . '</a>
            </li>
            ';
                    }
                    ?>
                    <li role="presentation" class="active">
                        <a href="#"><?php echo $hesklang['statuses']; ?> <i class="fa fa-question-circle settingsquestionmark"
                                                                            data-toggle="popover"
                                                                            title="<?php echo $hesklang['statuses']; ?>"
                                                                            data-content="<?php echo $hesklang['statuses_intro']; ?>"></i></a>
                    </li>
                    <?php
                    if (hesk_checkPermission('can_man_settings', 0)) {
                        echo '
                    <li role="presentation">
						<a title="' . $hesklang['tab_4'] . '" href="custom_fields.php">' .
							$hesklang['tab_4']
						. '</a>
					</li>
                        ';
                    }
                    ?>
                </ul>
                <div class="tab-content summaryList tabPadding">
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                            /* This will handle error, success and notice messages */
                            hesk_handle_messages();

                            //-- We need to get all of the statuses and dump the information to the page.
                            $numOfStatusesRS = hesk_dbQuery('SELECT 1 FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'statuses`');
                            $numberOfStatuses = hesk_dbNumRows($numOfStatusesRS);

                            $statuses = mfh_getAllStatuses();
                            ?>
                            <form class="form-horizontal" method="post" action="manage_statuses.php" role="form">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4>
                                            <?php echo $hesklang['statuses']; ?>
                                            <span style="float: right; margin-top: -7px">
                                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-status-new">
                                                    <i class="fa fa-plus-circle"></i>
                                                    <?php
                                                    echo $hesklang['new_status'];
                                                    ?>
                                                </button>
                                            </span>
                                        </h4>
                                    </div>
                                    <table class="table table-hover">
                                        <thead>
                                        <tr>
                                            <th><?php echo $hesklang['name']; ?></th>
                                            <th><?php echo $hesklang['closable_question']; ?></th>
                                            <th><?php echo $hesklang['closedQuestionMark']; ?></th>
                                            <th><?php echo $hesklang['actions']; ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $j = 1;
                                        foreach ($statuses as $key => $row):
                                            ?>
                                            <tr id="s<?php echo $row['ID']; ?>_row">
                                                <td class="bold" style="color: <?php echo $row['TextColor']; ?>">
                                                    <?php echo $row['text']; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if ($row['Closable'] == 'yes') {
                                                        echo $hesklang['yes_title_case'];
                                                    } elseif ($row['Closable'] == 'conly') {
                                                        echo $hesklang['customers_only'];
                                                    } elseif ($row['Closable'] == 'sonly') {
                                                        echo $hesklang['staff_only'];
                                                    } elseif ($row['Closable'] == 'no') {
                                                        echo $hesklang['no_title_case'];
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if ($row['IsClosed']) {
                                                        echo '<i class="fa fa-check-circle icon-link green"></i>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                        <span data-toggle="modal" data-target="#modal-status-<?php echo $row['ID']; ?>"
                                              style="cursor: pointer;">
                                            <i class="fa fa-pencil icon-link orange"
                                               data-toggle="tooltip" title="<?php echo $hesklang['edit']; ?>"></i>
                                        </span>
                                                    <?php echoArrows($j, $numberOfStatuses, $row['ID'], $modsForHesk_settings); ?>
                                                    <?php
                                                    // Only show the delete button if (1) it's not a default action and (2) no tickets are set to that status
                                                    $delete = canStatusBeDeleted($row['ID']);
                                                    $cursor = 'cursor: pointer';
                                                    $iconStyle = 'color: red';
                                                    $dataTarget = 'data-target="#modal-status-delete-' . $row['ID'] . '"';
                                                    $tooltip = $hesklang['delete'];
                                                    if ($delete == 'no-default' || $delete == 'no-tickets') {
                                                        $cursor = '';
                                                        $dataTarget = '';
                                                        $iconStyle = 'color: grey';
                                                    }
                                                    if ($delete == 'no-default') {
                                                        $tooltip = $hesklang['whyCantIDeleteThisStatusReason'];
                                                    } elseif ($delete == 'no-tickets') {
                                                        $tooltip = $hesklang['cannot_delete_status_tickets'];
                                                    }
                                                    ?>
                                                    <span data-toggle="modal" <?php echo $dataTarget; ?>
                                                          style="<?php echo $cursor; ?>;">
                                            <i class="fa fa-times icon-link" style="<?php echo $iconStyle; ?>"
                                               data-toggle="tooltip" title="<?php echo $tooltip; ?>"></i>
                                        </span>
                                                </td>
                                            </tr>
                                            <?php
                                            $j++;
                                        endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4><?php echo $hesklang['defaultStatusForAction']; ?></h4>
                                    </div>
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label for="newTicket"
                                                   class="col-sm-6 col-xs-12 control-label"><?php echo $hesklang['isNewTicketMsg']; ?></label>

                                            <div class="col-sm-6 col-xs-12">
                                                <select name="newTicket" class="form-control" id="newTicket">
                                                    <?php
                                                    foreach ($statuses as $key => $row) {
                                                        if ($row['IsClosed'] == 1) {
                                                            continue;
                                                        }

                                                        $selectedEcho = ($row['IsNewTicketStatus'] == 1) ? 'selected="selected"' : '';
                                                        echo '<option value="' . $row['ID'] . '" ' . $selectedEcho . '>' . mfh_getDisplayTextForStatusId($row['ID']) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="closedByClient"
                                                   class="col-sm-6 col-xs-12 control-label"><?php echo $hesklang['isClosedByClientMsg']; ?></label>

                                            <div class="col-sm-6 col-xs-12">
                                                <select name="closedByClient" class="form-control" id="closedByClient">
                                                    <?php
                                                    foreach ($statuses as $key => $row) {
                                                        if ($row['IsClosed'] == 0) {
                                                            continue;
                                                        }

                                                        $selectedEcho = ($row['IsClosedByClient'] == 1) ? 'selected="selected"' : '';
                                                        echo '<option value="' . $row['ID'] . '" ' . $selectedEcho . '>' . mfh_getDisplayTextForStatusId($row['ID']) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="replyFromClient"
                                                   class="col-sm-6 col-xs-12 control-label"><?php echo $hesklang['isRepliedByClientMsg']; ?></label>

                                            <div class="col-sm-6 col-xs-12">
                                                <select name="replyFromClient" class="form-control" id="replyFromClient">
                                                    <?php
                                                    foreach ($statuses as $key => $row) {
                                                        if ($row['IsClosed'] == 1) {
                                                            continue;
                                                        }

                                                        $selectedEcho = ($row['IsCustomerReplyStatus'] == 1) ? 'selected="selected"' : '';
                                                        echo '<option value="' . $row['ID'] . '" ' . $selectedEcho . '>' . mfh_getDisplayTextForStatusId($row['ID']) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="staffClosedOption"
                                                   class="col-sm-6 col-xs-12 control-label"><?php echo $hesklang['isStaffClosedOptionMsg']; ?></label>

                                            <div class="col-sm-6 col-xs-12">
                                                <select name="staffClosedOption" class="form-control" id="staffClosedOption">
                                                    <?php
                                                    foreach ($statuses as $key => $row) {
                                                        if ($row['IsClosed'] == 0) {
                                                            continue;
                                                        }

                                                        $selectedEcho = ($row['IsStaffClosedOption'] == 1) ? 'selected="selected"' : '';
                                                        echo '<option value="' . $row['ID'] . '" ' . $selectedEcho . '>' . mfh_getDisplayTextForStatusId($row['ID']) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="staffReopenedStatus"
                                                   class="col-sm-6 col-xs-12 control-label"><?php echo $hesklang['isStaffReopenedStatusMsg']; ?></label>

                                            <div class="col-sm-6 col-xs-12">
                                                <select name="staffReopenedStatus" class="form-control"
                                                        id="staffReopenedStatus">
                                                    <?php
                                                    foreach ($statuses as $key => $row) {
                                                        if ($row['IsClosed'] == 1) {
                                                            continue;
                                                        }

                                                        $selectedEcho = ($row['IsStaffReopenedStatus'] == 1) ? 'selected="selected"' : '';
                                                        echo '<option value="' . $row['ID'] . '" ' . $selectedEcho . '>' . mfh_getDisplayTextForStatusId($row['ID']) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="defaultStaffReplyStatus"
                                                   class="col-sm-6 col-xs-12 control-label"><?php echo $hesklang['isDefaultStaffReplyStatusMsg']; ?></label>

                                            <div class="col-sm-6 col-xs-12">
                                                <select name="defaultStaffReplyStatus" class="form-control"
                                                        id="defaultStaffReplyStatus">
                                                    <?php
                                                    foreach ($statuses as $key => $row) {
                                                        if ($row['IsClosed'] == 1) {
                                                            continue;
                                                        }

                                                        $selectedEcho = ($row['IsDefaultStaffReplyStatus'] == 1) ? 'selected="selected"' : '';
                                                        echo '<option value="' . $row['ID'] . '" ' . $selectedEcho . '>' . mfh_getDisplayTextForStatusId($row['ID']) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="lockedTicketStatus"
                                                   class="col-sm-6 col-xs-12 control-label"><?php echo $hesklang['lockedTicketStatusMsg']; ?></label>

                                            <div class="col-sm-6 col-xs-12">
                                                <select name="lockedTicketStatus" class="form-control" id="lockedTicketStatus">
                                                    <?php
                                                    foreach ($statuses as $key => $row) {
                                                        $selectedEcho = ($row['LockedTicketStatus'] == 1) ? 'selected="selected"' : '';
                                                        echo '<option value="' . $row['ID'] . '" ' . $selectedEcho . '>' . mfh_getDisplayTextForStatusId($row['ID']) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="autocloseTicketOption"
                                                   class="col-sm-6 col-xs-12 control-label"><?php echo $hesklang['autoclose_ticket_status']; ?></label>

                                            <div class="col-sm-6 col-xs-12">
                                                <select name="autocloseTicketOption" class="form-control"
                                                        id="autocloseTicketOption">
                                                    <?php
                                                    foreach ($statuses as $key => $row) {
                                                        if ($row['IsClosed'] == 0) {
                                                            continue;
                                                        }

                                                        $selectedEcho = ($row['IsAutocloseOption'] == 1) ? 'selected' : '';
                                                        echo '<option value="' . $row['ID'] . '" ' . $selectedEcho . '>' . mfh_getDisplayTextForStatusId($row['ID']) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-sm-offset-6">
                                    <input type="hidden" name="a" value="save">
                                    <input type="submit" class="btn btn-default"
                                           value="<?php echo $hesklang['save_changes']; ?>">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</div>
<?php
foreach ($statuses as $status) {
    buildEditModal($status['ID']);
    buildConfirmDeleteModal($status['ID']);
}
buildCreateModal();

require_once(HESK_PATH . 'inc/footer.inc.php');
exit();

function buildConfirmDeleteModal($statusId)
{
    global $hesklang;

    ?>
    <div class="modal fade" id="modal-status-delete-<?php echo $statusId; ?>" tabindex="-1" role="dialog"
         aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo $hesklang['confirm_delete_status_question']; ?></h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <p><?php echo $hesklang['confirm_delete_status']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="a" value="create">

                    <div class="btn-group">
                        <a href="manage_statuses.php?a=delete&id=<?php echo $statusId; ?>" class="btn btn-danger">
                            <?php echo $hesklang['delete']; ?>
                        </a>
                        <button type="button" class="btn btn-default"
                                data-dismiss="modal"><?php echo $hesklang['cancel']; ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function echoArrows($index, $numberOfStatuses, $statusId, $modsForHesk_settings)
{
    global $hesklang;

    if ($modsForHesk_settings['statuses_order_column'] == 'name') {
        return;
    }

    if ($index !== 1) {
        // Display move up
        echo '<a href="manage_statuses.php?a=sort&move=-15&id=' . $statusId . '">
            <i class="fa fa-arrow-up icon-link green" data-toggle="tooltip"
            title="' . htmlspecialchars($hesklang['move_up']) . '"></i></a> ';
    } else {
        echo '<img src="../img/blank.gif" width="16" height="16" alt="" style="padding:3px;border:none;"> ';
    }

    if ($index !== $numberOfStatuses) {
        // Display move down
        echo '<a href="manage_statuses.php?a=sort&move=15&id=' . $statusId . '">
            <i class="fa fa-arrow-down icon-link green" data-toggle="tooltip"
            title="' . htmlspecialchars($hesklang['move_dn']) . '"></i></a>';
    } else {
        echo '<img src="../img/blank.gif" width="16" height="16" alt="" style="padding:3px;border:none;">';
    }

}

function buildCreateModal()
{
    global $hesklang, $hesk_settings;

    $languages = array();
    foreach ($hesk_settings['languages'] as $key => $value) {
        $languages[$key] = $hesk_settings['languages'][$key]['folder'];
    }
    ?>
    <div class="modal fade" id="modal-status-new" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_statuses.php" role="form" method="post" class="form-horizontal" data-toggle="validator">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><?php echo $hesklang['create_new_status_title']; ?></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4><?php echo $hesklang['status_name_title']; ?>
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="popover"
                                       title="<?php echo $hesklang['status_name_title']; ?>"
                                       data-content="<?php echo $hesklang['status_name_title_help']; ?>"></i></h4>

                                <div class="footerWithBorder blankSpace"></div>
                                <?php foreach ($languages as $language => $languageCode): ?>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label" for="name[<?php echo $language; ?>]">
                                            <?php echo $language; ?>
                                        </label>

                                        <div class="col-sm-9">
                                            <input type="text" placeholder="<?php echo htmlspecialchars($language); ?>"
                                                   data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                                   class="form-control" name="name[<?php echo $language; ?>]" required>
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="col-md-6">
                                <h4><?php echo $hesklang['properties']; ?></h4>

                                <div class="footerWithBorder blankSpace"></div>
                                <div class="form-group">
                                    <label for="text-color" class="col-sm-4 control-label">
                                        <?php echo $hesklang['textColor']; ?>
                                        <i class="fa fa-question-circle settingsquestionmark"
                                           data-toggle="popover"
                                           title="<?php echo $hesklang['textColor']; ?>"
                                           data-content="<?php echo $hesklang['textColorDescr']; ?>"></i>
                                    </label>

                                    <div class="col-sm-8">
                                        <input type="text" name="text-color" class="form-control colorpicker-trigger"
                                               data-color=""
                                               data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                               placeholder="<?php echo htmlspecialchars($hesklang['textColor']); ?>" required>
                                        <div class="help-block with-errors"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="closable" class="col-sm-4 control-label">
                                        <?php echo $hesklang['closable']; ?>
                                        <i class="fa fa-question-circle settingsquestionmark"
                                           data-toggle="htmlpopover"
                                           title="<?php echo $hesklang['closable']; ?>"
                                           data-content="<?php echo $hesklang['closable_description']; ?>"></i>
                                    </label>

                                    <div class="col-sm-8">
                                        <select name="closable" class="form-control">
                                            <option value="yes"><?php echo $hesklang['yes_title_case']; ?></option>
                                            <option value="conly"><?php echo $hesklang['customers_only']; ?></option>
                                            <option value="sonly"><?php echo $hesklang['staff_only']; ?></option>
                                            <option value="no"><?php echo $hesklang['no_title_case']; ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="closed" class="col-sm-4 control-label">
                                        <?php echo $hesklang['closed_title']; ?>
                                        <i class="fa fa-question-circle settingsquestionmark"
                                           data-toggle="htmlpopover"
                                           title="<?php echo $hesklang['closed_title']; ?>"
                                           data-content="<?php echo $hesklang['closedQuestionMarkDescr']; ?>"></i>
                                    </label>

                                    <div class="col-sm-8">
                                        <select name="closed" class="form-control">
                                            <option value="1"><?php echo $hesklang['yes_title_case']; ?></option>
                                            <option value="0"><?php echo $hesklang['no_title_case']; ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="a" value="create">

                        <div class="btn-group">
                            <input type="submit" class="btn btn-success"
                                   value="<?php echo $hesklang['save_changes']; ?>">
                            <button type="button" class="btn btn-default"
                                    data-dismiss="modal"><?php echo $hesklang['close_modal_without_saving']; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

function buildEditModal($statusId)
{
    global $hesklang, $hesk_settings;

    // Get status information for this status
    $getStatusRs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` WHERE `ID` = " . intval($statusId));
    $status = hesk_dbFetchAssoc($getStatusRs);

    $textRs = hesk_dbQuery("SELECT `language`, `text` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "text_to_status_xref`
        WHERE `status_id` = " . intval($statusId));
    $textArray = array();
    while ($row = hesk_dbFetchAssoc($textRs)) {
        $textArray[$row['language']] = $row['text'];
    }

    $languages = array();
    foreach ($hesk_settings['languages'] as $key => $value) {
        $languages[$key] = $hesk_settings['languages'][$key]['folder'];
    }
    ?>
    <div class="modal fade" id="modal-status-<?php echo $statusId; ?>" tabindex="-1" role="dialog"
         aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_statuses.php" role="form" method="post" class="form-horizontal" data-toggle="validator">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><?php echo sprintf($hesklang['editing_status_x'], $status['TextColor'], mfh_getDisplayTextForStatusId($statusId)); ?></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>
                                    <?php echo $hesklang['status_name_title']; ?>
                                    <i class="fa fa-question-circle settingsquestionmark"
                                       data-toggle="popover"
                                       title="<?php echo $hesklang['status_name_title']; ?>"
                                       data-content="<?php echo $hesklang['status_name_title_help']; ?>"></i>
                                </h4>

                                <div class="footerWithBorder blankSpace"></div>
                                <?php foreach ($languages as $language => $languageCode):
                                    $warning = '';
                                    if (isset($textArray[$language])) {
                                        $text = $textArray[$language];
                                    } else {
                                        hesk_setLanguage($language);
                                        $text = $hesklang[$status['Key']];
                                        hesk_resetLanguage();
                                        $warning = 'has-warning';
                                    }
                                    ?>
                                    <div class="form-group <?php echo $warning; ?>">
                                        <label class="col-sm-3 control-label" for="name[<?php echo $language; ?>]">
                                            <?php
                                            if ($warning != '') {
                                                echoWarningForStatus();
                                            }
                                            echo $language;
                                            ?>
                                        </label>

                                        <div class="col-sm-9">
                                            <input type="text" placeholder="<?php echo htmlspecialchars($language); ?>"
                                                   class="form-control" name="name[<?php echo $language; ?>]"
                                                   data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                                   value="<?php echo htmlspecialchars($text); ?>" required>
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="col-md-6">
                                <h4><?php echo $hesklang['properties']; ?></h4>

                                <div class="footerWithBorder blankSpace"></div>
                                <div class="form-group">
                                    <label for="text-color" class="col-sm-4 control-label">
                                        <?php echo $hesklang['textColor']; ?>
                                        <i class="fa fa-question-circle settingsquestionmark"
                                           data-toggle="popover"
                                           title="<?php echo $hesklang['textColor']; ?>"
                                           data-content="<?php echo $hesklang['textColorDescr']; ?>"></i>
                                    </label>

                                    <div class="col-sm-8">
                                        <input type="text" name="text-color" class="form-control colorpicker-trigger"
                                               value="<?php echo $status['TextColor']; ?>"
                                               data-error="<?php echo htmlspecialchars($hesklang['this_field_is_required']); ?>"
                                               placeholder="<?php echo htmlspecialchars($hesklang['textColor']); ?>" required>
                                        <div class="help-block with-errors"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="closable" class="col-sm-4 control-label">
                                        <?php echo $hesklang['closable']; ?>
                                        <i class="fa fa-question-circle settingsquestionmark"
                                           data-toggle="htmlpopover"
                                           title="<?php echo $hesklang['closable']; ?>"
                                           data-content="<?php echo $hesklang['closable_description']; ?>"></i>
                                    </label>

                                    <div class="col-sm-8">
                                        <?php
                                        $yesSelected = $status['Closable'] == 'yes' ? 'selected' : '';
                                        $customersOnlySelected = $status['Closable'] == 'conly' ? 'selected' : '';
                                        $staffOnlySelected = $status['Closable'] == 'sonly' ? 'selected' : '';
                                        $noSelected = $status['Closable'] == 'no' ? 'selected' : '';
                                        ?>
                                        <select name="closable" class="form-control">
                                            <option
                                                value="yes" <?php echo $yesSelected; ?>><?php echo $hesklang['yes_title_case']; ?></option>
                                            <option
                                                value="conly" <?php echo $customersOnlySelected; ?>><?php echo $hesklang['customers_only']; ?></option>
                                            <option
                                                value="sonly" <?php echo $staffOnlySelected; ?>><?php echo $hesklang['staff_only']; ?></option>
                                            <option
                                                value="no" <?php echo $noSelected; ?>><?php echo $hesklang['no_title_case']; ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="closed" class="col-sm-4 control-label">
                                        <?php echo $hesklang['closed_title']; ?>
                                        <i class="fa fa-question-circle settingsquestionmark"
                                           data-toggle="htmlpopover"
                                           title="<?php echo $hesklang['closed_title']; ?>"
                                           data-content="<?php echo $hesklang['closedQuestionMarkDescr']; ?>"></i>
                                    </label>

                                    <div class="col-sm-8">
                                        <?php
                                        $yes = $status['IsClosed'] == 1 ? 'selected' : '';
                                        $no = $status['IsClosed'] == 1 ? '' : 'selected';
                                        ?>
                                        <select name="closed" class="form-control">
                                            <option
                                                value="1" <?php echo $yes; ?>><?php echo $hesklang['yes_title_case']; ?></option>
                                            <option
                                                value="0" <?php echo $no; ?>><?php echo $hesklang['no_title_case']; ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="a" value="update">
                        <input type="hidden" name="status-id" value="<?php echo $statusId; ?>">

                        <div class="btn-group">
                            <input type="submit" class="btn btn-success"
                                   value="<?php echo $hesklang['save_changes']; ?>">
                            <button type="button" class="btn btn-default"
                                    data-dismiss="modal"><?php echo $hesklang['close_modal_without_saving']; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

function canStatusBeDeleted($id)
{
    global $hesk_settings;

    $defaultActionSql = "SELECT 1 FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` WHERE `ID` = " . intval($id) . " AND
        (`IsNewTicketStatus` = 1 OR `IsClosedByClient` = 1 OR `IsCustomerReplyStatus` = 1 OR `IsStaffClosedOption` = 1
            OR `IsStaffReopenedStatus` = 1 OR `IsDefaultStaffReplyStatus` = 1 OR `LockedTicketStatus` = 1 OR `IsAutocloseOption` = 1)";
    $defaultActionRs = hesk_dbQuery($defaultActionSql);
    if (hesk_dbNumRows($defaultActionRs) > 0) {
        // it's a default action
        return 'no-default';
    }
    // check if any tickets have this status
    $statusRs = hesk_dbQuery("SELECT 1 FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `status` = " . intval($id));
    if (hesk_dbNumRows($statusRs) > 0) {
        return 'no-tickets';
    }
    return 'yes';
}

function echoWarningForStatus()
{
    global $hesklang;

    echo '<i class="fa fa-exclamation-triangle" data-toggle="tooltip" title="' . htmlspecialchars($hesklang['status_not_in_database']) . '"></i> ';
}

function createStatus()
{
    global $hesklang, $hesk_settings;

    hesk_dbConnect();

    // Create the new status record
    $isClosed = hesk_POST('closed');
    $closable = hesk_POST('closable');
    $textColor = hesk_POST('text-color');

    /* Get the latest cat_order */
    $res = hesk_dbQuery("SELECT `sort` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ORDER BY `sort` DESC LIMIT 1");
    $row = hesk_dbFetchRow($res);
    $my_order = $row[0] + 10;

    // Get the next status id
    $res = hesk_dbQuery("SELECT `ID` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ORDER BY `ID` DESC LIMIT 1");
    $row = hesk_dbFetchAssoc($res);
    $nextId = $row['ID'] + 1;

    $insert = "INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` (`ID`, `Key`, `TextColor`, `IsClosed`, `Closable`, `sort`)
		VALUES (" . intval($nextId) . ", 'STORED IN XREF TABLE', '" . hesk_dbEscape($textColor) . "', " . intval($isClosed) . ", '" . hesk_dbEscape($closable) . "', " . intval($my_order) . ")";
    hesk_dbQuery($insert);


    // For each language, create a value in the xref table
    foreach (hesk_POST_array('name') as $language => $translation) {
        hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "text_to_status_xref` (`language`, `text`, `status_id`)
            VALUES ('" . hesk_dbEscape($language) . "', '" . hesk_dbEscape($translation) . "', " . intval($nextId) . ")");
    }

    hesk_process_messages($hesklang['new_status_created'], 'manage_statuses.php', 'SUCCESS');
}

function updateStatus()
{
    global $hesklang, $hesk_settings;

    $statusId = hesk_POST('status-id');
    $isClosed = hesk_POST('closed');
    $closable = hesk_POST('closable');
    $textColor = hesk_POST('text-color');
    $update = "UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses`
        SET `TextColor` = '" . hesk_dbEscape($textColor) . "',
            `IsClosed` = " . intval($isClosed) . ",
            `Closable` = '" . hesk_dbEscape($closable) . "'
        WHERE `ID` = " . intval($statusId);
    hesk_dbQuery($update);

    // For each language, delete the xref record and insert the new ones
    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "text_to_status_xref` WHERE `status_id` = " . intval($statusId));
    foreach (hesk_POST_array('name') as $language => $translation) {
        hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "text_to_status_xref` (`language`, `text`, `status_id`)
            VALUES ('" . hesk_dbEscape($language) . "', '" . hesk_dbEscape($translation) . "', " . intval($statusId) . ")");
    }

    hesk_process_messages($hesklang['ticket_status_updated'], 'manage_statuses.php', 'SUCCESS');
}

function deleteStatus()
{
    global $hesklang, $hesk_settings;

    $statusId = hesk_GET('id');

    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "text_to_status_xref` WHERE `status_id` = " . intval($statusId));
    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` WHERE `ID` = " . intval($statusId));
    resortStatuses();

    hesk_process_messages($hesklang['ticket_status_deleted'], 'manage_statuses.php', 'SUCCESS');
}

function moveStatus()
{
    global $hesk_settings, $hesklang;

    $statusId = intval(hesk_GET('id'));
    $statusMove = intval(hesk_GET('move'));

    hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` SET `sort` = `sort`+" . intval($statusMove) . "
        WHERE `ID` = '" . intval($statusId) . "' LIMIT 1");

    resortStatuses();

    hesk_process_messages($hesklang['status_sort_updated'], 'manage_statuses.php', 'SUCCESS');
}

function resortStatuses()
{
    global $hesk_settings;

    /* Update all category fields with new order */
    $res = hesk_dbQuery("SELECT `ID` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` ORDER BY `sort` ASC");
    $i = 10;
    while ($myStatus = hesk_dbFetchAssoc($res)) {
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` SET `sort`=" . intval($i) . "
            WHERE `ID`='" . intval($myStatus['ID']) . "' LIMIT 1");
        $i += 10;
    }
}

function save()
{
    global $hesklang, $hesk_settings;

    //-- Update default status for actions
    $defaultQuery = "UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` SET ";

    hesk_dbQuery($defaultQuery . "`IsNewTicketStatus` = 0");
    $updateQuery = $defaultQuery . "`IsNewTicketStatus` = 1 WHERE `ID` = " . intval($_POST['newTicket']);
    hesk_dbQuery($updateQuery);

    hesk_dbQuery($defaultQuery . "`IsClosedByClient` = 0");
    $updateQuery = $defaultQuery . "`IsClosedByClient` = 1 WHERE `ID` = " . intval($_POST['closedByClient']);
    hesk_dbQuery($updateQuery);

    hesk_dbQuery($defaultQuery . "`IsCustomerReplyStatus` = 0");
    $updateQuery = $defaultQuery . "`IsCustomerReplyStatus` = 1 WHERE `ID` = " . intval($_POST['replyFromClient']);
    hesk_dbQuery($updateQuery);

    hesk_dbQuery($defaultQuery . "`IsStaffClosedOption` = 0");
    $updateQuery = $defaultQuery . "`IsStaffClosedOption` = 1 WHERE `ID` = " . intval($_POST['staffClosedOption']);
    hesk_dbQuery($updateQuery);

    hesk_dbQuery($defaultQuery . "`IsStaffReopenedStatus` = 0");
    $updateQuery = $defaultQuery . "`IsStaffReopenedStatus` = 1 WHERE `ID` = " . intval($_POST['staffReopenedStatus']);
    hesk_dbQuery($updateQuery);

    hesk_dbQuery($defaultQuery . "`IsDefaultStaffReplyStatus` = 0");
    $updateQuery = $defaultQuery . "`IsDefaultStaffReplyStatus` = 1 WHERE `ID` = " . intval($_POST['defaultStaffReplyStatus']);
    hesk_dbQuery($updateQuery);

    hesk_dbQuery($defaultQuery . "`LockedTicketStatus` = 0");
    $updateQuery = $defaultQuery . "`LockedTicketStatus` = 1 WHERE `ID` = " . intval($_POST['lockedTicketStatus']);
    hesk_dbQuery($updateQuery);

    hesk_dbQuery($defaultQuery . "`IsAutocloseOption` = 0");
    $updateQuery = $defaultQuery . "`IsAutocloseOption` = 1 WHERE `ID` = " . intval($_POST['autocloseTicketOption']);
    hesk_dbQuery($updateQuery);

    hesk_process_messages($hesklang['default_statuses_updated'], 'manage_statuses.php', 'SUCCESS');
}