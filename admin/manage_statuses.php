<?php

define('IN_SCRIPT',1);
define('HESK_PATH','../');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

hesk_checkPermission('can_man_ticket_statuses');

define('WYSIWYG',1);

// Are we performing an action?
if (isset($_REQUEST['a'])) {
    if ($_POST['a'] == 'create') { createStatus(); }
    elseif ($_POST['a'] == 'update') { updateStatus(); }
    elseif ($_GET['a'] == 'delete') { deleteStatus(); }
    elseif ($_GET['a'] == 'up') { moveStatus('up'); }
}


/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>

<div class="row" style="padding: 20px">
    <ul class="nav nav-tabs" role="tablist">
        <?php
        // Show a link to banned_emails.php if user has permission
        if ( hesk_checkPermission('can_ban_emails',0) )
        {
            echo '
            <li role="presentation">
                <a title="' . $hesklang['banemail'] . '" href="banned_emails.php">'.$hesklang['banemail'].'</a>
            </li>
            ';
        }
        if ( hesk_checkPermission('can_ban_ips',0) )
        {
            echo '
            <li role="presentation">
                <a title="' . $hesklang['banip'] . '" href="banned_ips.php">'.$hesklang['banip'].'</a>
            </li>';
        }
        // Show a link to status_message.php if user has permission to do so
        if ( hesk_checkPermission('can_service_msg',0) )
        {
            echo '
            <li role="presentation">
                <a title="' . $hesklang['sm_title'] . '" href="service_messages.php">' . $hesklang['sm_title'] . '</a>
            </li>';
        }
        if ( hesk_checkPermission('can_man_email_tpl',0) )
        {
            echo '
            <li role="presentation">
                <a title="'.$hesklang['email_templates'].'" href="manage_email_templates.php">'.$hesklang['email_templates'].'</a>
            </li>
            ';
        }
        ?>
        <li role="presentation" class="active">
            <a href="#"><?php echo $hesklang['statuses']; ?> <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover" title="<?php echo $hesklang['statuses']; ?>" data-content="<?php echo $hesklang['statuses_intro']; ?>"></i></a>
        </li>
    </ul>
    <div class="tab-content summaryList tabPadding">
        <div class="row">
            <div class="col-md-12">
                <?php
                /* This will handle error, success and notice messages */
                hesk_handle_messages();

                //-- We need to get all of the statuses and dump the information to the page.
                $numOfStatusesRS = hesk_dbQuery('SELECT 1 FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'statuses`');
                $numberOfStatuses = hesk_dbNumRows($numOfStatusesRS);

                $statusesSql = 'SELECT `ID`, `IsAutocloseOption`, `TextColor`, `Closable`, `IsClosed` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'statuses`';
                $closedStatusesSql = 'SELECT `ID`, `IsClosedByClient` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'statuses` WHERE `IsClosed` = 1';
                $openStatusesSql = 'SELECT `ID`, `IsNewTicketStatus`, `IsStaffReopenedStatus`, `IsDefaultStaffReplyStatus` FROM
                    `'.hesk_dbEscape($hesk_settings['db_pfix']).'statuses` WHERE `IsClosed` = 0';
                $statusesRS = hesk_dbQuery($statusesSql);
                ?>
                <form class="form-horizontal" method="post" action="manage_statuses.php" role="form">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4>
                                <?php echo $hesklang['statuses']; ?>
                                <span class="nu-floatRight" style="margin-top: -7px">
                                    <button class="btn btn-success" data-toggle="modal" data-target="#modal-status-new">
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
                            while ($row = hesk_dbFetchAssoc($statusesRS)):
                            ?>
                                <tr id="s<?php echo $row['ID']; ?>_row">
                                    <td style="color: <?php echo $row['TextColor']; ?>; font-weight: bold">
                                        <?php echo mfh_getDisplayTextForStatusId($row['ID']); ?>
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
                                            echo '<i class="fa fa-check-circle icon-link" style="color: green;"></i>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span data-toggle="modal" data-target="#modal-status-<?php echo $row['ID']; ?>" style="cursor: pointer;">
                                            <i class="fa fa-pencil icon-link" style="color: orange"
                                               data-toggle="tooltip" title="<?php echo $hesklang['edit']; ?>"></i>
                                        </span>
                                        <?php echoArrows($j, $numberOfStatuses, $row['ID']); ?>
                                        <span data-toggle="modal" data-target="#modal-status-delete-<?php echo $row['ID']; ?>" style="cursor: pointer;">
                                            <i class="fa fa-times icon-link" style="color: red"
                                               data-toggle="tooltip" title="<?php echo $hesklang['delete']; ?>"></i>
                                        </span>
                                    </td>
                                </tr>
                            <?php
                                buildEditModal($row['ID']);
                                buildConfirmDeleteModal($row['ID']);
                                $j++;
                                endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4><?php echo $hesklang['defaultStatusForAction']; ?></h4>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="newTicket" class="col-sm-6 col-xs-12 control-label"><?php echo $hesklang['isNewTicketMsg']; ?></label>
                                <div class="col-sm-6 col-xs-12">
                                    <select name="newTicket" class="form-control" id="newTicket">
                                        <?php
                                        $statusesRS = hesk_dbQuery($openStatusesSql);
                                        while ($row = $statusesRS->fetch_assoc())
                                        {
                                            $selectedEcho = ($row['IsNewTicketStatus'] == 1) ? 'selected="selected"' : '';
                                            echo '<option value="'.$row['ID'].'" '.$selectedEcho.'>'.mfh_getDisplayTextForStatusId($row['ID']).'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="closedByClient" class="col-sm-6 col-xs-12 control-label"><?php echo $hesklang['isClosedByClientMsg']; ?></label>
                                <div class="col-sm-6 col-xs-12">
                                    <select name="closedByClient" class="form-control" id="closedByClient">
                                        <?php
                                        $statusesRS = hesk_dbQuery($closedStatusesSql);
                                        while ($row = $statusesRS->fetch_assoc())
                                        {
                                            $selectedEcho = ($row['IsClosedByClient'] == 1) ? 'selected="selected"' : '';
                                            echo '<option value="'.$row['ID'].'" '.$selectedEcho.'>'.mfh_getDisplayTextForStatusId($row['ID']).'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="replyFromClient" class="col-sm-6 col-xs-12 control-label"><?php echo $hesklang['isRepliedByClientMsg']; ?></label>
                                <div class="col-sm-6 col-xs-12">
                                    <select name="replyFromClient" class="form-control" id="replyFromClient">
                                        <?php
                                        $statusesRS = hesk_dbQuery($openStatusesSql);
                                        while ($row = $statusesRS->fetch_assoc())
                                        {
                                            $selectedEcho = ($row['IsCustomerReplyStatus'] == 1) ? 'selected="selected"' : '';
                                            echo '<option value="'.$row['ID'].'" '.$selectedEcho.'>'.mfh_getDisplayTextForStatusId($row['ID']).'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="staffClosedOption" class="col-sm-6 col-xs-12 control-label"><?php echo $hesklang['isStaffClosedOptionMsg']; ?></label>
                                <div class="col-sm-6 col-xs-12">
                                    <select name="staffClosedOption" class="form-control" id="staffClosedOption">
                                        <?php
                                        $statusesRS = hesk_dbQuery($closedStatusesSql);
                                        while ($row = $statusesRS->fetch_assoc())
                                        {
                                            $selectedEcho = ($row['IsStaffClosedOption'] == 1) ? 'selected="selected"' : '';
                                            echo '<option value="'.$row['ID'].'" '.$selectedEcho.'>'.mfh_getDisplayTextForStatusId($row['ID']).'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="staffReopenedStatus" class="col-sm-6 col-xs-12 control-label"><?php echo $hesklang['isStaffReopenedStatusMsg']; ?></label>
                                <div class="col-sm-6 col-xs-12">
                                    <select name="staffReopenedStatus" class="form-control" id="staffReopenedStatus">
                                        <?php
                                        $statusesRS = hesk_dbQuery($openStatusesSql);
                                        while ($row = $statusesRS->fetch_assoc())
                                        {
                                            $selectedEcho = ($row['IsStaffReopenedStatus'] == 1) ? 'selected="selected"' : '';
                                            echo '<option value="'.$row['ID'].'" '.$selectedEcho.'>'.mfh_getDisplayTextForStatusId($row['ID']).'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="defaultStaffReplyStatus" class="col-sm-6 col-xs-12 control-label"><?php echo $hesklang['isDefaultStaffReplyStatusMsg']; ?></label>
                                <div class="col-sm-6 col-xs-12">
                                    <select name="defaultStaffReplyStatus" class="form-control" id="defaultStaffReplyStatus">
                                        <?php
                                        $statusesRS = hesk_dbQuery($openStatusesSql);
                                        while ($row = $statusesRS->fetch_assoc())
                                        {
                                            $selectedEcho = ($row['IsDefaultStaffReplyStatus'] == 1) ? 'selected="selected"' : '';
                                            echo '<option value="'.$row['ID'].'" '.$selectedEcho.'>'.mfh_getDisplayTextForStatusId($row['ID']).'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="lockedTicketStatus" class="col-sm-6 col-xs-12 control-label"><?php echo $hesklang['lockedTicketStatusMsg']; ?></label>
                                <div class="col-sm-6 col-xs-12">
                                    <select name="lockedTicketStatus" class="form-control" id="lockedTicketStatus">
                                        <?php
                                        $statusesRS = hesk_dbQuery($statusesSql);
                                        while ($row = $statusesRS->fetch_assoc())
                                        {
                                            $selectedEcho = ($row['LockedTicketStatus'] == 1) ? 'selected="selected"' : '';
                                            echo '<option value="'.$row['ID'].'" '.$selectedEcho.'>'.mfh_getDisplayTextForStatusId($row['ID']).'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="autocloseTicketOption" class="col-sm-6 col-xs-12 control-label"><?php echo $hesklang['autoclose_ticket_status']; ?></label>
                                <div class="col-sm-6 col-xs-12">
                                    <select name="autocloseTicketOption" class="form-control" id="autocloseTicketOption">
                                        <?php
                                        $statusesRS = hesk_dbQuery($closedStatusesSql);
                                        while ($row = $statusesRS->fetch_assoc())
                                        {
                                            $selectedEcho = ($row['IsAutocloseOption'] == 1) ? 'selected' : '';
                                            echo '<option value="'.$row['ID'].'" '.$selectedEcho.'>'.mfh_getDisplayTextForStatusId($row['ID']).'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-sm-offset-6">
                        <input type="hidden" name="action" value="save">
                        <input type="submit" class="btn btn-default" value="<?php echo $hesklang['save_changes']; ?>">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
buildCreateModal();

require_once(HESK_PATH . 'inc/footer.inc.php');
exit();

function buildConfirmDeleteModal($statusId) {
    global $hesklang;

    ?>
    <div class="modal fade" id="modal-status-delete-<?php echo $statusId; ?>" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $hesklang['cancel']; ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function echoArrows($index, $numberOfStatuses, $statusId) {
    global $hesklang;

    if ($index !== 1) {
        // Display move up
        echo '<a href="manage_statuses.php?a=up?id='.$statusId.'">
            <i class="fa fa-arrow-up icon-link" style="color: green" data-toggle="tooltip"
            title="'.htmlspecialchars($hesklang['move_up']).'"></i></a> ';
    } else {
        echo '<img src="../img/blank.gif" width="16" height="16" alt="" style="padding:3px;border:none;"> ';
    }

    if ($index !== $numberOfStatuses) {
        // Display move down
        echo '<a href="#"><i class="fa fa-arrow-down icon-link" style="color: green" data-toggle="tooltip" title="'.htmlspecialchars($hesklang['move_dn']).'"></i></a>';
    } else {
        echo '<img src="../img/blank.gif" width="16" height="16" alt="" style="padding:3px;border:none;">';
    }

}

function buildCreateModal() {
    global $hesklang, $hesk_settings;

    $languages = array();
    foreach ($hesk_settings['languages'] as $key => $value) {
        $languages[$key] = $hesk_settings['languages'][$key]['folder'];
    }
?>
    <div class="modal fade" id="modal-status-new" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_statuses.php" role="form" method="post" class="form-horizontal">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><?php echo $hesklang['create_new_status_title']; ?></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4><?php echo $hesklang['status_name_title']; ?></h4>
                                <div class="footerWithBorder blankSpace"></div>
                                <?php foreach ($languages as $language => $languageCode): ?>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="name[<?php echo $language; ?>]">
                                        <?php echo $language; ?>
                                    </label>
                                    <div class="col-sm-9">
                                        <input type="text" placeholder="<?php echo htmlspecialchars($language); ?>"
                                            class="form-control" name="name[<?php echo $language; ?>]">
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="col-md-6">
                                <h4><?php echo $hesklang['properties']; ?></h4>
                                <div class="footerWithBorder blankSpace"></div>
                                <div class="form-group">
                                    <label for="text-color" class="col-sm-4 control-label"><?php echo $hesklang['textColor']; ?></label>
                                    <div class="col-sm-8">
                                        <input type="text" name="text-color" class="form-control"
                                               placeholder="<?php echo htmlspecialchars($hesklang['textColor']); ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="closable" class="col-sm-4 control-label"><?php echo $hesklang['closable']; ?></label>
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
                                    <label for="closed" class="col-sm-4 control-label"><?php echo $hesklang['closed_title']; ?></label>
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
                            <input type="submit" class="btn btn-success" value="<?php echo $hesklang['save_changes']; ?>">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $hesklang['close_modal_without_saving']; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

function buildEditModal($statusId) {
    global $hesklang, $hesk_settings;

    // Get status information for this status
    $getStatusRs = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` WHERE `ID` = ".intval($statusId));
    $status = hesk_dbFetchAssoc($getStatusRs);

    $textRs = hesk_dbQuery("SELECT `language`, `text` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."text_to_status_xref`
        WHERE `status_id` = ".intval($statusId));
    $textArray = array();
    while ($row = hesk_dbFetchAssoc($textRs)) {
        $textArray[$row['language']] = $row['text'];
    }

    $languages = array();
    foreach ($hesk_settings['languages'] as $key => $value) {
        $languages[$key] = $hesk_settings['languages'][$key]['folder'];
    }
    ?>
    <div class="modal fade" id="modal-status-<?php echo $statusId; ?>" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_statuses.php" role="form" method="post" class="form-horizontal">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><?php echo sprintf($hesklang['editing_status_x'], $status['TextColor'], mfh_getDisplayTextForStatusId($statusId)); ?></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4><?php echo $hesklang['status_name_title']; ?></h4>
                                <div class="footerWithBorder blankSpace"></div>
                                <?php foreach ($languages as $language => $languageCode):
                                    $warning = '';
                                    if (isset($textArray[$language])) {
                                        $text = $textArray[$language];
                                    } else {
                                        $text = $hesklang[$status['Key']];
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
                                                   value="<?php echo htmlspecialchars($text); ?>">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="col-md-6">
                                <h4><?php echo $hesklang['properties']; ?></h4>
                                <div class="footerWithBorder blankSpace"></div>
                                <div class="form-group">
                                    <label for="text-color" class="col-sm-4 control-label"><?php echo $hesklang['textColor']; ?></label>
                                    <div class="col-sm-8">
                                        <input type="text" name="text-color" class="form-control"
                                               value="<?php echo $status['TextColor']; ?>"
                                               placeholder="<?php echo htmlspecialchars($hesklang['textColor']); ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="closable" class="col-sm-4 control-label"><?php echo $hesklang['closable']; ?></label>
                                    <div class="col-sm-8">
                                        <?php
                                        $yesSelected = $status['Closable'] == 'yes' ? 'selected' : '';
                                        $customersOnlySelected = $status['Closable'] == 'conly' ? 'selected' : '';
                                        $staffOnlySelected = $status['Closable'] == 'sonly' ? 'selected' : '';
                                        $noSelected = $status['Closable'] == 'no' ? 'selected' : '';
                                        ?>
                                        <select name="closable" class="form-control">
                                            <option value="yes" <?php echo $yesSelected; ?>><?php echo $hesklang['yes_title_case']; ?></option>
                                            <option value="conly" <?php echo $customersOnlySelected; ?>><?php echo $hesklang['customers_only']; ?></option>
                                            <option value="sonly" <?php echo $staffOnlySelected; ?>><?php echo $hesklang['staff_only']; ?></option>
                                            <option value="no" <?php echo $noSelected; ?>><?php echo $hesklang['no_title_case']; ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="closed" class="col-sm-4 control-label"><?php echo $hesklang['closed_title']; ?></label>
                                    <div class="col-sm-8">
                                        <?php
                                        $yes = $status['IsClosed'] == 1 ? 'selected' : '';
                                        $no = $status['IsClosed'] == 1 ? '' : 'selected';
                                        ?>
                                        <select name="closed" class="form-control">
                                            <option value="1" <?php echo $yes; ?>><?php echo $hesklang['yes_title_case']; ?></option>
                                            <option value="0" <?php echo $no; ?>><?php echo $hesklang['no_title_case']; ?></option>
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
                            <input type="submit" class="btn btn-success" value="<?php echo $hesklang['save_changes']; ?>">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $hesklang['close_modal_without_saving']; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

function echoWarningForStatus() {
    global $hesklang;

    echo '<i class="fa fa-exclamation-triangle" data-toggle="tooltip" title="'.htmlspecialchars($hesklang['status_not_in_database']).'"></i> ';
}

function createStatus() {
    global $hesklang, $hesk_settings;

    hesk_dbConnect();

    // Create the new status record
    $isClosed = hesk_POST('closed');
    $closable = hesk_POST('closable');
    $textColor = hesk_POST('text-color');
    $insert = "INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` (`Key`, `TextColor`, `IsClosed`, `Closable`)
		VALUES ('STORED IN XREF TABLE', '".hesk_dbEscape($textColor)."', ".intval($isClosed).", '".hesk_dbEscape($closable)."')";
    hesk_dbQuery($insert);

    $newStatusId = hesk_dbInsertID();

    // For each language, create a value in the xref table
    foreach (hesk_POST_array('name') as $language => $translation) {
        hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."text_to_status_xref` (`language`, `text`, `status_id`)
            VALUES ('".hesk_dbEscape($language)."', '".hesk_dbEscape($translation)."', ".intval($newStatusId).")");
    }

    hesk_process_messages($hesklang['new_status_created'],'manage_statuses.php','SUCCESS');
}

function updateStatus() {
    global $hesklang, $hesk_settings;

    $statusId = hesk_POST('status-id');
    $isClosed = hesk_POST('closed');
    $closable = hesk_POST('closable');
    $textColor = hesk_POST('text-color');
    $update = "UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses`
        SET `TextColor` = '".hesk_dbEscape($textColor)."',
            `IsClosed` = ".intval($isClosed).",
            `Closable` = '".hesk_dbEscape($closable)."'
        WHERE `ID` = ".intval($statusId);
    hesk_dbQuery($update);

    // For each language, delete the xref record and insert the new ones
    hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."text_to_status_xref` WHERE `status_id` = ".intval($statusId));
    foreach (hesk_POST_array('name') as $language => $translation) {
        hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."text_to_status_xref` (`language`, `text`, `status_id`)
            VALUES ('".hesk_dbEscape($language)."', '".hesk_dbEscape($translation)."', ".intval($newStatusId).")");
    }

    hesk_process_messages($hesklang['ticket_status_updated'],'manage_statuses.php','SUCCESS');
}

function deleteStatus() {
    global $hesklang, $hesk_settings;

    $statusId = hesk_GET('id');

    hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."text_to_status_xref` WHERE `status_id` = ".intval($statusId));
    hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` WHERE `ID` = ".intval($statusId));

    hesk_process_messages($hesklang['ticket_status_deleted'],'manage_statuses.php','SUCCESS');
}

function moveStatus($direction) {
    die(var_dump($_GET));

    // Get the current position of the status
    $statusId = hesk_GET('id');
    $rs = hesk_dbQuery("SELECT `sort` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` WHERE `ID` = ".intval($statusId));
    $record = hesk_dbFetchAssoc($rs);

    if ($direction == 'up') {
        $newSort = intval($record['sort']) - 1;
    } else {
        $newSort = intval($record['sort']) + 1;
    }
}

function save() {
    global $hesklang, $hesk_settings;

    //-- Update default status for actions
    $defaultQuery = "UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` SET ";

    hesk_dbQuery($defaultQuery . "`IsNewTicketStatus` = 0");
    $updateQuery = $defaultQuery . "`IsNewTicketStatus` = 1 WHERE `ID` = ".intval($_POST['newTicket']);
    hesk_dbQuery($updateQuery);

    hesk_dbQuery($defaultQuery . "`IsClosedByClient` = 0");
    $updateQuery = $defaultQuery . "`IsClosedByClient` = 1 WHERE `ID` = ".intval($_POST['closedByClient']);
    hesk_dbQuery($updateQuery);

    hesk_dbQuery($defaultQuery . "`IsCustomerReplyStatus` = 0");
    $updateQuery = $defaultQuery . "`IsCustomerReplyStatus` = 1 WHERE `ID` = ".intval($_POST['replyFromClient']);
    hesk_dbQuery($updateQuery);

    hesk_dbQuery($defaultQuery . "`IsStaffClosedOption` = 0");
    $updateQuery = $defaultQuery . "`IsStaffClosedOption` = 1 WHERE `ID` = ".intval($_POST['staffClosedOption']);
    hesk_dbQuery($updateQuery);

    hesk_dbQuery($defaultQuery . "`IsStaffReopenedStatus` = 0");
    $updateQuery = $defaultQuery . "`IsStaffReopenedStatus` = 1 WHERE `ID` = ".intval($_POST['staffReopenedStatus']);
    hesk_dbQuery($updateQuery);

    hesk_dbQuery($defaultQuery . "`IsDefaultStaffReplyStatus` = 0");
    $updateQuery = $defaultQuery . "`IsDefaultStaffReplyStatus` = 1 WHERE `ID` = ".intval($_POST['defaultStaffReplyStatus']);
    hesk_dbQuery($updateQuery);

    hesk_dbQuery($defaultQuery . "`LockedTicketStatus` = 0");
    $updateQuery = $defaultQuery . "`LockedTicketStatus` = 1 WHERE `ID` = ".intval($_POST['lockedTicketStatus']);
    hesk_dbQuery($updateQuery);

    hesk_dbQuery($defaultQuery . "`IsAutocloseOption` = 0");
    $updateQuery = $defaultQuery . "`IsAutocloseOption` = 1 WHERE `ID` = ".intval($_POST['autocloseTicketOption']);
    hesk_dbQuery($updateQuery);

    hesk_process_messages($hesklang['statuses_saved'],'manage_statuses.php','SUCCESS');
}