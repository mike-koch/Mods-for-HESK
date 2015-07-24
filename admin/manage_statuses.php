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
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'save') {
        save();
    }
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

                $statusesSql = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'statuses`';
                $closedStatusesSql = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'statuses` WHERE `IsClosed` = 1';
                $openStatusesSql = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'statuses` WHERE `IsClosed` = 0';
                $statusesRS = hesk_dbQuery($statusesSql);
                ?>
                <form class="form-horizontal" method="post" action="manage_statuses.php" role="form">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4>
                                <?php echo $hesklang['statuses']; ?>
                                <span class="nu-floatRight" style="margin-top: -7px">
                                    <a href="#" class="btn btn-success">
                                        <i class="fa fa-plus-circle"></i>
                                        <?php
                                            echo $hesklang['new_status'];
                                            buildCreateStatusModal();
                                        ?>
                                    </a>
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
                                        <?php echo $hesklang[$row['Key']]; ?>
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
                                            echo $hesklang['yes_title_case'];
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <!-- TODO Localize this -->
                                        <a href="#">
                                            <i class="fa fa-pencil icon-link" style="color: orange"
                                               data-toggle="tooltip" title="<?php echo $hesklang['edit']; ?>"></i></a>
                                        <?php echoArrows($j, $numberOfStatuses); ?>
                                        <a href="#">
                                            <i class="fa fa-times icon-link" style="color: red"
                                               data-toggle="tooltip" title="<?php echo $hesklang['delete']; ?>"></i></a>
                                    </td>
                                </tr>
                            <?php $j++; endwhile; ?>
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
                                            echo '<option value="'.$row['ID'].'" '.$selectedEcho.'>'.$hesklang[$row['Key']].'</option>';
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
                                            echo '<option value="'.$row['ID'].'" '.$selectedEcho.'>'.$hesklang[$row['Key']].'</option>';
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
                                            echo '<option value="'.$row['ID'].'" '.$selectedEcho.'>'.$hesklang[$row['Key']].'</option>';
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
                                            echo '<option value="'.$row['ID'].'" '.$selectedEcho.'>'.$hesklang[$row['Key']].'</option>';
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
                                            echo '<option value="'.$row['ID'].'" '.$selectedEcho.'>'.$hesklang[$row['Key']].'</option>';
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
                                            echo '<option value="'.$row['ID'].'" '.$selectedEcho.'>'.$hesklang[$row['Key']].'</option>';
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
                                            echo '<option value="'.$row['ID'].'" '.$selectedEcho.'>'.$hesklang[$row['Key']].'</option>';
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
                                            echo '<option value="'.$row['ID'].'" '.$selectedEcho.'>'.$hesklang[$row['Key']].'</option>';
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
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();

function echoArrows($index, $numberOfStatuses) {
    global $hesklang;

    if ($index !== 1) {
        // Display move up
        echo '<a href="#"><i class="fa fa-arrow-up icon-link" style="color: green" data-toggle="tooltip" title="'.htmlspecialchars($hesklang['move_up']).'"></i></a> ';
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

function buildCreateStatusModal() {
    echo '';
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