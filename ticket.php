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
define('HESK_NO_ROBOTS', 1);
define('WYSIWYG', 1);
define('VALIDATOR', 1);

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/view_attachment_functions.inc.php');

// Are we in maintenance mode?
hesk_check_maintenance();

hesk_load_database_functions();

hesk_session_start();
/* Connect to database */
hesk_dbConnect();
$modsForHesk_settings = mfh_getSettings();

$hesk_error_buffer = array();
$do_remember = '';
$display = 'none';

/* Was this accessed by the form or link? */
$is_form = isset($_GET['f']) ? 1 : 0;

/* Get the tracking ID */
$trackingID = hesk_cleanID();

/* Email required to view ticket? */
$my_email = hesk_getCustomerEmail(1);

/* A message from ticket reminder? */
if (!empty($_GET['remind'])) {
    $display = 'block';
    print_form();
}

/* Any errors? Show the form */
if ($is_form) {
    if (empty($trackingID)) {
        $hesk_error_buffer[] = $hesklang['eytid'];
    }

    if ($hesk_settings['email_view_ticket'] && empty($my_email)) {
        $hesk_error_buffer[] = $hesklang['enter_valid_email'];
    }

    $tmp = count($hesk_error_buffer);
    if ($tmp == 1) {
        $hesk_error_buffer = implode('', $hesk_error_buffer);
        hesk_process_messages($hesk_error_buffer, 'NOREDIRECT');
        print_form();
    } elseif ($tmp == 2) {
        $hesk_error_buffer = $hesklang['pcer'] . '<br /><br /><ul><li>' . $hesk_error_buffer[0] . '</li><li>' . $hesk_error_buffer[1] . '</li></ul>';
        hesk_process_messages($hesk_error_buffer, 'NOREDIRECT');
        print_form();
    }
} elseif (empty($trackingID) || ($hesk_settings['email_view_ticket'] && empty($my_email))) {
    print_form();
}


/* Limit brute force attempts */
hesk_limitBfAttempts();

/* Get ticket info */
$res = hesk_dbQuery("SELECT `t1`.* , `t2`.name AS `repliername`, `ticketStatus`.`IsClosed` AS `isClosed`, `ticketStatus`.`Key` AS `statusKey`  FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` AS `t1` INNER JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` AS `ticketStatus` ON `t1`.`status` = `ticketStatus`.`ID` LEFT JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` AS `t2` ON `t1`.`replierid` = `t2`.`id` WHERE `trackid`='" . hesk_dbEscape($trackingID) . "' LIMIT 1");

/* Ticket found? */
if (hesk_dbNumRows($res) != 1) {
    /* Ticket not found, perhaps it was merged with another ticket? */
    $res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `merged` LIKE '%#" . hesk_dbEscape($trackingID) . "#%' LIMIT 1");

    if (hesk_dbNumRows($res) == 1) {
        /* OK, found in a merged ticket. Get info */
        $ticket = hesk_dbFetchAssoc($res);

        /* If we require e-mail to view tickets check if it matches the one from merged ticket */
        if (hesk_verifyEmailMatch($ticket['trackid'], $my_email, $ticket['email'], 0)) {
            hesk_process_messages(sprintf($hesklang['tme'], $trackingID, $ticket['trackid']), 'NOREDIRECT', 'NOTICE');
            $trackingID = $ticket['trackid'];
        } else {
            hesk_process_messages(sprintf($hesklang['tme1'], $trackingID, $ticket['trackid']) . '<br /><br />' . sprintf($hesklang['tme2'], $ticket['trackid']), 'NOREDIRECT', 'NOTICE');
            $trackingID = $ticket['trackid'];
            print_form();
        }
    } else {
        /* Nothing found, error out */
        hesk_process_messages($hesklang['ticket_not_found'], 'NOREDIRECT');
        print_form();
    }
} else {
    /* We have a match, get ticket info */
    $ticket = hesk_dbFetchAssoc($res);

    /* If we require e-mail to view tickets check if it matches the one in database */
    hesk_verifyEmailMatch($trackingID, $my_email, $ticket['email']);
}

/* Ticket exists, clean brute force attempts */
hesk_cleanBfAttempts();

/* Remember email address? */
if ($is_form) {
    if (!empty($_GET['r'])) {
        setcookie('hesk_myemail', $my_email, strtotime('+1 year'));
        $do_remember = ' checked="checked" ';
    } elseif (isset($_COOKIE['hesk_myemail'])) {
        setcookie('hesk_myemail', '');
    }
}

/* Set last replier name */
if ($ticket['lastreplier']) {
    if (empty($ticket['repliername'])) {
        $ticket['repliername'] = $hesklang['staff'];
    }
} else {
    $ticket['repliername'] = $ticket['name'];
}

// If IP is unknown (tickets via email pipe/pop3 fetching) assume current visitor IP as customer IP
if ($ticket['ip'] == 'Unknown' || $ticket['ip'] == $hesklang['unknown']) {
    hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `ip` = '" . hesk_dbEscape($_SERVER['REMOTE_ADDR']) . "' WHERE `id`=" . intval($ticket['id']) . " LIMIT 1");
}

/* Get category name and ID */
$result = hesk_dbQuery("SELECT `name` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` WHERE `id`='" . intval($ticket['category']) . "' LIMIT 1");

/* If this category has been deleted use the default category with ID 1 */
if (hesk_dbNumRows($result) != 1) {
    $result = hesk_dbQuery("SELECT `name` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` WHERE `id`='1' LIMIT 1");
}

$category = hesk_dbFetchAssoc($result);

/* Get replies */
$result = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` WHERE `replyto`='" . intval($ticket['id']) . "' ORDER BY `id` " . ($hesk_settings['new_top'] ? 'DESC' : 'ASC'));
$replies = hesk_dbNumRows($result);
$unread_replies = array();

// Demo mode
if (defined('HESK_DEMO')) {
    $ticket['email'] = 'hidden@demo.com';
}

/* Print header */
require_once(HESK_PATH . 'inc/header.inc.php');
?>

<ol class="breadcrumb">
    <li><a href="<?php echo $hesk_settings['site_url']; ?>"><?php echo $hesk_settings['site_title']; ?></a></li>
    <li><a href="<?php echo $hesk_settings['hesk_url']; ?>"><?php echo $hesk_settings['hesk_title']; ?></a></li>
    <li><a href="ticket.php"><?php echo $hesklang['view_ticket_nav']; ?></a></li>
    <li class="active"><?php hesk_showTopBar($hesklang['cid'] . ': ' . $trackingID); ?></li>
</ol>

<?php
$columnWidth = 'col-md-8';
$showRs = hesk_dbQuery("SELECT `show` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` WHERE `id` = 3");
$show = hesk_dbFetchAssoc($showRs);
if (!$show['show']) {
    $columnWidth = 'col-md-10 col-md-offset-1';
}
?>
<div class="row">
    <?php if ($columnWidth == 'col-md-8'): ?>
        <div align="left" class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading"><?php echo $hesklang['quick_help']; ?></div>
                <div class="panel-body">
                    <p><?php echo $hesklang['quick_help_ticket']; ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="<?php echo $columnWidth; ?>">
        <?php
        /* This will handle error, success and notice messages */
        hesk_handle_messages();

        /*
        * If the ticket has been reopened by customer:
        * - show the "Add a reply" form on top
        * - and ask them why the form has been reopened
        */
        if (isset($_SESSION['force_form_top'])) {
            hesk_printCustomerReplyForm(1);
            echo ' <p>&nbsp;</p> ';

            unset($_SESSION['force_form_top']);
        }
        ?>
        <h3 align="left"><?php echo $hesklang['view_ticket']; ?>: <?php
            if ($hesk_settings['sequential']) {
                echo $trackingID . ' (' . $hesklang['seqid'] . ': ' . $ticket['id'] . ')';
            } else {
                echo $trackingID;
            }
            ?></h3>

        <div class="footerWithBorder"></div>
        <div class="blankSpace"></div>
        <div class="table-bordered">
            <div class="row">
                <div class="col-md-12">
                    <h2><?php echo $ticket['subject']; ?></h2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 col-sm-12">
                    <p><?php echo $hesklang['created_on']; ?>: <?php echo hesk_date($ticket['dt'], true); ?></p>
                </div>
                <div class="col-md-3 col-sm-12">
                    <p><?php echo $hesklang['last_update']; ?>
                        : <?php echo hesk_date($ticket['lastchange'], true); ?></p>
                </div>
                <div class="col-md-2 col-md-offset-4 col-sm-12 close-ticket">
                    <p><?php
                        $statusRS = hesk_dbQuery('SELECT * FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'statuses` WHERE `ID` = ' . intval($ticket['status']));
                        $status = hesk_dbFetchAssoc($statusRS);
                        $isClosable = $status['Closable'] == 'yes' || $status['Closable'] == 'conly';
                        $random = rand(10000, 99999);
                        if ($ticket['isClosed'] == true && $ticket['locked'] != 1 && $hesk_settings['custopen']) {
                            echo '<a href="change_status.php?track=' . $trackingID . $hesk_settings['e_query'] . '&amp;s=2&amp;Refresh=' . $random . '&amp;token=' . hesk_token_echo(0) . '" title="' . $hesklang['open_action'] . '">' . $hesklang['open_action'] . '</a>';
                        } elseif ($hesk_settings['custclose'] && $isClosable) {
                            echo '<a href="change_status.php?track=' . $trackingID . $hesk_settings['e_query'] . '&amp;s=3&amp;Refresh=' . $random . '&amp;token=' . hesk_token_echo(0) . '" title="' . $hesklang['close_action'] . '">' . $hesklang['close_action'] . '</a>';
                        } ?></p>
                </div>
            </div>
            <div class="row medLowPriority">
                <?php //This entire conditional is all just for priority
                if ($hesk_settings['cust_urgency']) {
                    $repliesColumnWidth = 2;
                    echo '<div class="col-md-2 col-sm-12 ticket-cell ';
                    if ($ticket['priority'] == 0) {
                        echo 'criticalPriority">';
                    } elseif ($ticket['priority'] == 1) {
                        echo 'highPriority">';
                    } else {
                        echo 'medLowPriority">';
                    }
                    echo '<p class="ticketPropertyTitle">' . $hesklang['priority'] . '</p>';

                    if ($ticket['priority'] == 0) {
                        echo '<p class="ticketPropertyText">' . $hesklang['critical'] . '</p>';
                    } elseif ($ticket['priority'] == 1) {
                        echo '<p class="ticketPropertyText">' . $hesklang['high'] . '</p>';
                    } elseif ($ticket['priority'] == 2) {
                        echo '<p class="ticketPropertyText">' . $hesklang['medium'] . '</p>';
                    } else {
                        echo '<p class="ticketPropertyText">' . $hesklang['low'] . '</p>';
                    }
                    echo '</div>';
                } else {
                    $repliesColumnWidth = 3;
                }
                echo '<div class="col-md-3 col-sm-12 ticket-cell"><p class="ticketPropertyTitle">' . $hesklang['status'] . '</p>';
                echo '<p class="ticketPropertyText">' . mfh_getDisplayTextForStatusId($status['ID']) . '</p>';
                echo '</div>';
                echo '<div class="col-md-3 col-sm-12 ticket-cell"><p class="ticketPropertyTitle">' . $hesklang['last_replier'] . '</p>
                        <p class="ticketPropertyText">' . $ticket['repliername'] . '</p></div>';
                echo '<div class="col-md-' . $repliesColumnWidth . ' col-sm-12 ticket-cell"><p class="ticketPropertyTitle">' . $hesklang['category'] . '</p>
                        <p class="ticketPropertyText">' . $category['name'] . '</p></div>';
                echo '<div class="col-md-' . $repliesColumnWidth . ' col-sm-12 ticket-cell"><p class="ticketPropertyTitle">' . $hesklang['replies'] . '</p>
                        <p class="ticketPropertyText">' . $replies . '</p></div>';
                ?>
            </div>
        </div>
        <div class="blankSpace"></div>
        <!-- REPLIES -->

        <?php
        if ($hesk_settings['new_top']) {
            $i = hesk_printCustomerTicketReplies() ? 0 : 1;
        } else {
            $i = 1;
        }

        /* Make sure original message is in correct color if newest are on top */
        $color = 'class="ticketMessageContainer"';
        ?>
        <div class="row ticketMessageContainer">
            <div class="col-md-3 col-xs-12">
                <div class="ticketName"><?php echo $ticket['name']; ?></div>
                <div class="ticketEmail"><?php echo $ticket['email']; ?></div>
            </div>
            <div class="col-md-9 col-xs-12 pushMarginLeft">
                <div class="ticketMessageTop withBorder">
                    <!-- Date and Action buttons -->
                    <p><?php echo $hesklang['date']; ?>: <?php echo hesk_date($ticket['dt'], true); ?><span
                            class="nu-floatRight"><?php echo hesk_getCustomerButtons($i); ?></span></p>
                    <!-- Custom Fields Before Message -->
                    <?php
                    foreach ($hesk_settings['custom_fields'] as $k => $v) {
                        if ($v['use'] && $v['place'] == 0) {
                            if ($modsForHesk_settings['custom_field_setting']) {
                                $v['name'] = $hesklang[$v['name']];
                            }

                            echo '<p>' . $v['name'] . ': ';
                            if ($v['type'] == 'date' && !empty($ticket[$k])) {
                                $dt = date('Y-m-d h:i:s', $ticket[$k]);
                                echo hesk_dateToString($dt, 0);
                            } else {
                                echo $ticket[$k];
                            }
                            echo '</p>';
                        }
                    }
                    ?>
                </div>
                <div class="ticketMessageBottom">
                    <!-- Message -->
                    <p><b><?php echo $hesklang['message']; ?>:</b></p>

                    <div class="message">
                        <?php if ($ticket['html']) {
                            echo hesk_html_entity_decode($ticket['message']);
                        } else {
                            echo $ticket['message'];
                        }
                        ?>
                    </div>
                </div>
                <div class="ticketMessageTop">
                    <!-- Custom Fields after Message -->
                    <?php
                    foreach ($hesk_settings['custom_fields'] as $k => $v) {
                        if ($v['use'] && $v['place']) {
                            if ($modsForHesk_settings['custom_field_setting']) {
                                $v['name'] = $hesklang[$v['name']];
                            }

                            echo '<p>' . $v['name'] . ': ';
                            if ($v['type'] == 'date' && !empty($ticket[$k])) {
                                $dt = date('Y-m-d h:i:s', $ticket[$k]);
                                echo hesk_dateToString($dt, 0);
                            } else {
                                echo $ticket[$k];
                            }
                            echo '</p>';
                        }
                    }
                    /* Attachments */
                    mfh_listAttachments($ticket['attachments'], $i, false);
                    ?>
                </div>
            </div>
        </div>
        <?php
        if (!$hesk_settings['new_top']) {
            hesk_printCustomerTicketReplies();
        }
        ?>
        <!-- END REPLIES -->
        <?php
        // Print "Submit a reply" form?
        if ($ticket['locked'] != 1 && $ticket['status'] != 3 && $hesk_settings['reply_top'] == 1) {
            hesk_printCustomerReplyForm();
        }
        ?>

        <?php
        /* Print "Submit a reply" form? */
        if ($ticket['locked'] != 1 && $ticket['status'] != 3 && !$hesk_settings['reply_top']) {
            hesk_printCustomerReplyForm();
        }

        /* If needed update unread replies as read for staff to know */
        if (count($unread_replies)) {
            hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` SET `read` = '1' WHERE `id` IN ('" . implode("','", $unread_replies) . "')");
        }
        ?>
    </div>
    <!-- End col-md-7 -->
</div> <!-- End row -->


<?php

/* Clear unneeded session variables */
hesk_cleanSessionVars('ticket_message');

require_once(HESK_PATH . 'inc/footer.inc.php');

/*** START FUNCTIONS ***/

function print_form()
{
    global $hesk_settings, $hesklang;
    global $hesk_error_buffer, $my_email, $trackingID, $do_remember, $display;

    /* Print header */
    $hesk_settings['tmp_title'] = $hesk_settings['hesk_title'] . ' - ' . $hesklang['view_ticket'];
    require_once(HESK_PATH . 'inc/header.inc.php');
    ?>
    <ol class="breadcrumb">
        <li><a href="<?php echo $hesk_settings['site_url']; ?>"><?php echo $hesk_settings['site_title']; ?></a></li>
        <li><a href="<?php echo $hesk_settings['hesk_url']; ?>"><?php echo $hesk_settings['hesk_title']; ?></a></li>
        <li class="active"><?php echo $hesklang['view_ticket_nav']; ?></li>
    </ol>

    <?php
    hesk_dbConnect();
    $columnWidth = 'col-md-8';
    $showRs = hesk_dbQuery("SELECT `show` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` WHERE `id` = 2");
    $show = hesk_dbFetchAssoc($showRs);
    if (!$show['show']) {
        $columnWidth = 'col-md-10 col-md-offset-1';
    }
    ?>
    <div class="row">
        <?php if ($columnWidth == 'col-md-8'): ?>
            <div align="left" class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <?php echo $hesklang['quick_help']; ?>
                    </div>
                    <div class="panel-body">
                        <p><?php echo $hesklang['quick_help_view_ticket']; ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="<?php echo $columnWidth; ?>">
            <?php
            /* This will handle error, success and notice messages */
            hesk_handle_messages();
            ?>
            <h3 align="left"><?php echo $hesklang['view_existing']; ?></h3>

            <div class="footerWithBorder"></div>
            <div class="blankSpace"></div>
            <form data-toggle="validator" action="ticket.php" class="form-horizontal" role="form" method="get" name="form2">
                <div class="form-group">
                    <label for="track" class="col-sm-3 control-label"><?php echo $hesklang['ticket_trackID']; ?></label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="track" id="track" maxlength="20" size="35"
                               value="<?php echo $trackingID; ?>"
                               placeholder="<?php echo htmlspecialchars($hesklang['ticket_trackID']); ?>"
                               data-error="<?php echo htmlspecialchars($hesklang['eytid']); ?>" required>
                        <div class="help-block with-errors"></div>
                    </div>
                </div>
                <?php
                $tmp = '';
                if ($hesk_settings['email_view_ticket']) {
                    $tmp = 'document.form1.email.value=document.form2.e.value;';
                    ?>
                    <div class="form-group">
                        <label for="e" class="col-sm-3 control-label"><?php echo $hesklang['email']; ?></label>

                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="e" name="e" size="35"
                                   value="<?php echo $my_email; ?>"
                                   placeholder="<?php echo htmlspecialchars($hesklang['email']); ?>"
                                   data-error="<?php echo htmlspecialchars($hesklang['enter_valid_email']); ?>" required>
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div align="left" class="form-group">
                        <div class="col-sm-offset-3 col-sm-9">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="r"
                                           value="Y" <?php echo $do_remember; ?> /> <?php echo $hesklang['rem_email']; ?>
                                </label>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div align="left" class="form-group">
                    <div class="col-sm-offset-3 col-sm-9">
                        <button type="submit" class="btn btn-default"
                                value="<?php echo $hesklang['view_ticket']; ?>"><?php echo $hesklang['view_ticket']; ?></button>
                        <input type="hidden" name="Refresh" value="<?php echo rand(10000, 99999); ?>"><input
                            type="hidden" name="f" value="1">

                        <div class="blankSpace"></div>
                        <a href="Javascript:void(0)"
                           onclick="javascript:hesk_toggleLayerDisplay('forgot');<?php echo $tmp; ?>"><?php echo $hesklang['forgot_tid']; ?></a>
                    </div>
                </div>
            </form>
            <div align="left" id="forgot" class="alert alert-info" style="display: <?php echo $display; ?>;">
                <p><?php echo $hesklang['tid_mail']; ?></p>

                <div class="blankSpace"></div>
                <form data-toggle="validator" action="index.php" method="post" class="form-horizontal" name="form1">
                    <div class="form-group">
                        <label for="email" class="col-sm-3 control-label"><?php echo $hesklang['email']; ?></label>

                        <div class="col-sm-9">
                            <input type="text" id="email" class="form-control" name="email" size="35"
                                   value="<?php echo $my_email; ?>"
                                   placeholder="<?php echo htmlspecialchars($hesklang['email']); ?>"
                                   data-error="<?php echo htmlspecialchars($hesklang['enter_valid_email']); ?>" required>
                            <div class="help-block with-errors"></div>
                            <input type="hidden" name="a" value="forgot_tid"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-9 col-sm-offset-3">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="open_only"
                                           value="1" <?php echo $hesk_settings['open_only'] ? 'checked="checked"' : ''; ?> /><?php echo $hesklang['oon1']; ?>
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="open_only"
                                           value="0" <?php echo !$hesk_settings['open_only'] ? 'checked="checked"' : ''; ?> /><?php echo $hesklang['oon2']; ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-9">
                            <button type="submit" class="btn btn-default"
                                    value="<?php echo $hesklang['tid_send']; ?>"><?php echo $hesklang['tid_send']; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php
    require_once(HESK_PATH . 'inc/footer.inc.php');
    exit();
} // End print_form()


function hesk_printCustomerReplyForm($reopen = 0)
{
    global $hesklang, $hesk_settings, $trackingID, $my_email, $modsForHesk_settings;

    // Already printed?
    if (defined('REPLY_FORM')) {
        return '';
    }

    ?>

    <h3 class="text-left"><?php echo $hesklang['add_reply']; ?></h3>
    <div class="footerWithBorder"></div>
    <div class="blankSpace"></div>

    <?php
    $onsubmit = '';
    if ($modsForHesk_settings['rich_text_for_tickets_for_customers']) {
        $onsubmit = 'onclick="return validateRichText(\'message-help-block\', \'message-group\', \'message\', \''.htmlspecialchars($hesklang['this_field_is_required']).'\')"';
    }
    ?>
    <form data-toggle="validator" role="form" class="form-horizontal" method="post" action="reply_ticket.php"
          enctype="multipart/form-data" <?php echo $onsubmit; ?>>
        <div class="form-group" id="message-group">
            <label for="message" class="col-sm-3 control-label"><?php echo $hesklang['message']; ?>: <span
                    class="important">*</span></label>

            <div class="col-sm-9">
                <textarea name="message" class="form-control htmlEditor" rows="12"
                          cols="60" data-error="<?php echo htmlspecialchars($hesklang['enter_message']); ?>" required><?php if (isset($_SESSION['ticket_message'])) {
                        echo stripslashes(hesk_input($_SESSION['ticket_message']));
                    } ?></textarea>
                <div class="help-block with-errors" id="message-help-block"></div>
                <?php if ($modsForHesk_settings['rich_text_for_tickets_for_customers']): ?>
                    <script type="text/javascript">
                        /* <![CDATA[ */
                        tinyMCE.init({
                            mode: "textareas",
                            editor_selector: "htmlEditor",
                            elements: "content",
                            theme: "advanced",
                            convert_urls: false,

                            theme_advanced_buttons1: "cut,copy,paste,|,undo,redo,|,formatselect,fontselect,fontsizeselect,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull",
                            theme_advanced_buttons2: "sub,sup,|,charmap,|,bullist,numlist,|,outdent,indent,insertdate,inserttime,preview,|,forecolor,backcolor,|,hr,removeformat,visualaid,|,link,unlink,anchor,image,cleanup",
                            theme_advanced_buttons3: "",

                            theme_advanced_toolbar_location: "top",
                            theme_advanced_toolbar_align: "left",
                            theme_advanced_statusbar_location: "bottom",
                            theme_advanced_resizing: true
                        });
                        /* ]]> */
                    </script>
                <?php endif; ?>
            </div>
        </div>
        <?php
        /* attachments */
        if ($hesk_settings['attachments']['use']) {
            ?>
            <div class="form-group">
                <label for="attachments" class="col-sm-3 control-label"><?php echo $hesklang['attachments']; ?>:</label>

                <div class="col-sm-9 text-left">
                    <?php
                    for ($i = 1; $i <= $hesk_settings['attachments']['max_number']; $i++) {
                        echo '<input type="file" name="attachment[' . $i . ']" size="50" /><br />';
                    }
                    echo '<a href="file_limits.php" target="_blank" onclick="Javascript:hesk_window(\'file_limits.php\',250,500);return false;">' . $hesklang['ful'] . '</a>';
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
        <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>"/>
        <input type="hidden" name="orig_track" value="<?php echo $trackingID; ?>"/>
        <?php
        if ($hesk_settings['email_view_ticket']) {
            echo '<input type="hidden" name="e" value="' . $my_email . '" />';
        }
        if ($reopen) {
            echo '<input type="hidden" name="reopen" value="1" />';
        }
        ?>
        <div class="form-group">
            <div class="col-sm-9 col-sm-offset-3">
                <input type="submit" value="<?php echo $hesklang['submit_reply']; ?>" class="btn btn-default">
            </div>
        </div>
    </form>

    <?php

    // Make sure the form is only printed once per page
    define('REPLY_FORM', true);

} // End hesk_printCustomerReplyForm()


function hesk_printCustomerTicketReplies()
{
    global $hesklang, $hesk_settings, $result, $reply, $trackingID, $unread_replies;

    $i = $hesk_settings['new_top'] ? 0 : 1;

    while ($reply = hesk_dbFetchAssoc($result)) {
        $color = 'class="ticketMessageContainer"';

        /* Store unread reply IDs for later */
        if ($reply['staffid'] && !$reply['read']) {
            $unread_replies[] = $reply['id'];
        }

        $reply['dt'] = hesk_date($reply['dt'], true);
        ?>

        <div class="row ticketMessageContainer">
            <div class="col-md-3 col-xs-12">
                <div class="ticketName"><?php echo $reply['name']; ?></div>
            </div>
            <div class="col-md-9 col-xs-12 pushMarginLeft">
                <div class="ticketMessageTop withBorder">
                    <p><?php echo $hesklang['date']; ?>: <?php echo $reply['dt']; ?><span
                            style="float:  right;"><?php echo hesk_getCustomerButtons($i); ?></span></p>
                    <?php
                    /* Staff rating */
                    if ($hesk_settings['rating'] && $reply['staffid']) {
                        if ($reply['rating'] == 1) {
                            echo '<p class="rate">' . $hesklang['rnh'] . '</p>';
                        } elseif ($reply['rating'] == 5) {
                            echo '<p class="rate">' . $hesklang['rh'] . '</p>';
                        } else {
                            echo '
					            <div id="rating' . $reply['id'] . '" class="rate">
					            ' . $hesklang['r'] . '
					            <a href="Javascript:void(0)" onclick="Javascript:hesk_rate(\'rate.php?rating=5&amp;id=' . $reply['id'] . '&amp;track=' . $trackingID . '\',\'rating' . $reply['id'] . '\')">' . strtolower($hesklang['yes']) . '</a> /
					            <a href="Javascript:void(0)" onclick="Javascript:hesk_rate(\'rate.php?rating=1&amp;id=' . $reply['id'] . '&amp;track=' . $trackingID . '\',\'rating' . $reply['id'] . '\')">' . strtolower($hesklang['no']) . '</a>
					            </div>
					            ';
                        }
                    }
                    ?>
                </div>
                <div class="ticketMessageBottom">
                    <!-- Message -->
                    <p><b><?php echo $hesklang['message']; ?>:</b></p>

                    <div class="message">
                        <?php
                        if ($reply['html']) {
                            echo hesk_html_entity_decode($reply['message']);
                        } else {
                            echo $reply['message'];
                        }
                        ?>
                    </div>
                </div>
                <div class="ticketMessageTop">
                    <?php mfh_listAttachments($reply['attachments'], $i, false); ?>
                </div>
            </div>
        </div>
        <?php
    }

    return $i;

} // End hesk_printCustomerTicketReplies()


function hesk_getCustomerButtons($white = 1)
{
    global $hesk_settings, $hesklang, $trackingID;

    $options = '';

    /* Style and mousover/mousout */
    $tmp = $white ? 'White' : 'Blue';
    $style = 'class="option' . $tmp . 'OFF" onmouseover="this.className=\'option' . $tmp . 'ON\'" onmouseout="this.className=\'option' . $tmp . 'OFF\'"';

    /* Print ticket button */
    $options .= '<a href="print.php?track=' . $trackingID . $hesk_settings['e_query'] . '" title="' . $hesklang['printer_friendly'] . '"><span class="glyphicon glyphicon-print"></span> ' . $hesklang['printer_friendly'] . ' </a> ';

    /* Return generated HTML */
    return $options;

} // END hesk_getCustomerButtons()
?>
