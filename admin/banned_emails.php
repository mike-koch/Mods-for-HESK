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
define('PAGE_TITLE', 'ADMIN_BANNED_EMAILS');
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

/* Check permissions for this feature */
hesk_checkPermission('can_ban_emails');
$can_unban = hesk_checkPermission('can_unban_emails', 0);

// Define required constants
define('LOAD_TABS', 1);

// What should we do?
if ($action = hesk_REQUEST('a')) {
    if (defined('HESK_DEMO')) {
        hesk_process_messages($hesklang['ddemo'], 'banned_emails.php', 'NOTICE');
    } elseif ($action == 'ban') {
        ban_email();
    } elseif ($action == 'unban' && $can_unban) {
        unban_email();
    }
}

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
                    <li role="presentation" class="active">
                        <a href="#"><?php echo $hesklang['banemail']; ?> <i class="fa fa-question-circle settingsquestionmark"
                                                                            onclick="javascript:alert('<?php echo hesk_makeJsString($hesklang['banemail_intro']); ?>')"></i></a>
                    </li>
                    <?php
                    // Show a link to banned_ips.php if user has permission to do so
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

                    // Show a link to email tpl management if user has permission to do so
                    if (hesk_checkPermission('can_man_email_tpl', 0)) {
                        echo '
            <li role="presentation">
                <a title="' . $hesklang['email_templates'] . '" href="manage_email_templates.php">' . $hesklang['email_templates'] . '</a>
            </li>
            ';
                    }
                    if (hesk_checkPermission('can_man_ticket_statuses', 0)) {
                        echo '
            <li role="presentation">
                <a title="' . $hesklang['statuses'] . '" href="manage_statuses.php">' . $hesklang['statuses'] . '</a>
            </li>
            ';
                    }
                    if (hesk_checkPermission('can_man_settings', 0)) {
                        echo '<li role="presentation"><a title="' . $hesklang['tab_4'] . '" href="custom_fields.php">' . $hesklang['tab_4'] . '</a></li> ';
                    }
                    ?>
                </ul>
                <div class="tab-content summaryList tabPadding">
                    <script language="javascript" type="text/javascript"><!--
                        function confirm_delete() {
                            if (confirm('<?php echo hesk_makeJsString($hesklang['delban_confirm']); ?>')) {
                                return true;
                            }
                            else {
                                return false;
                            }
                        }
                        //-->
                    </script>
                    <div class="row">
                        <div class="col-md-8">
                            <br><br>
                            <?php
                            /* This will handle error, success and notice messages */
                            hesk_handle_messages();
                            ?>
                            <form action="banned_emails.php" method="post" name="form1" role="form" class="form-horizontal" data-toggle="validator">
                                <div class="form-group">
                                    <label for="text" class="col-sm-3 control-label"><?php echo $hesklang['bananemail']; ?></label>

                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="email" size="30" maxlength="255" data-error="<?php echo htmlspecialchars($hesklang['enterbanemail']); ?>"
                                               placeholder="<?php echo htmlspecialchars($hesklang['email']); ?>" required>
                                        <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>"/>
                                        <input type="hidden" name="a" value="ban"/>
                                        <div class="help-block with-errors"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-9 col-sm-offset-3">
                                        <input type="submit" value="<?php echo $hesklang['savebanemail']; ?>"
                                               class="btn btn-default">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <h6 class="bold"><?php echo $hesklang['banex']; ?></h6>

                            <div class="footerWithBorder blankSpace"></div>
                            <b>john@example.com</b><br/>
                            <b>@example.com</b>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                        <?php

                        // Get banned emails from database
                        $res = hesk_dbQuery('SELECT * FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'banned_emails` ORDER BY `email` ASC');
                        $num = hesk_dbNumRows($res);

                        echo '<h4>' . $hesklang['eperm'] . '</h4>';
                        if ($num < 1) {
                            echo '<p>' . $hesklang['no_banemails'] . '</p>';
                        } else {
                            // List of staff
                            if (!isset($admins)) {
                                $admins = array();
                                $res2 = hesk_dbQuery("SELECT `id`,`name` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users`");
                                while ($row = hesk_dbFetchAssoc($res2)) {
                                    $admins[$row['id']] = $row['name'];
                                }
                            }

                            ?>
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th><?php echo $hesklang['email']; ?></th>
                                    <th><?php echo $hesklang['banby']; ?></th>
                                    <th><?php echo $hesklang['date']; ?></th>
                                    <?php
                                    if ($can_unban) {
                                        ?>
                                        <th><?php echo $hesklang['opt']; ?></th>
                                        <?php
                                    }
                                    ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                while ($ban = hesk_dbFetchAssoc($res)) {
                                    $color = '';
                                    if (isset($_SESSION['ban_email']['id']) && $ban['id'] == $_SESSION['ban_email']['id']) {
                                        $color = 'success';
                                        unset($_SESSION['ban_email']['id']);
                                    }

                                    echo '
                        <tr>
                            <td class="' . $color . ' text-left">' . $ban['email'] . '</td>
                            <td class="' . $color . ' text-left">' . (isset($admins[$ban['banned_by']]) ? $admins[$ban['banned_by']] : $hesklang['e_udel']) . '</td>
                            <td class="' . $color . ' text-left">' . $ban['dt'] . '</td>
                            ';

                                    if ($can_unban) {
                                        echo '
                            <td class="' . $color . ' text-left">
                                <a href="banned_emails.php?a=unban&amp;id=' . $ban['id'] . '&amp;token=' . hesk_token_echo(0) . '" onclick="return confirm_delete();">
                                    <i class="fa fa-times red font-size-16p" data-toggle="tooltip" data-placement="top" data-original-title="' . $hesklang['delban'] . '"></i>
                                </a>
                            </td>
                            ';
                                    }

                                    echo '</tr>';
                                } // End while
                                ?>
                                </tbody>
                            </table>
                            <?php
                        }

                        ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</div>
<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


/*** START FUNCTIONS ***/

function ban_email()
{
    global $hesk_settings, $hesklang;

    // A security check
    hesk_token_check();

    // Get the email
    $email = strtolower(hesk_input(hesk_REQUEST('email')));

    // Nothing entered?
    if (!strlen($email)) {
        hesk_process_messages($hesklang['enterbanemail'], 'banned_emails.php');
    }

    // Only allow one email to be entered
    $email = ($index = strpos($email, ',')) ? substr($email, 0, $index) : $email;
    $email = ($index = strpos($email, ';')) ? substr($email, 0, $index) : $email;

    // Validate email address
    $hesk_settings['multi_eml'] = 0;

    if (!hesk_validateEmail($email, '', 0) && !verify_email_domain($email)) {
        hesk_process_messages($hesklang['validbanemail'], 'banned_emails.php');
    }

    // Redirect either to banned emails or ticket page from now on
    $redirect_to = ($trackingID = hesk_cleanID()) ? 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999) : 'banned_emails.php';

    // Prevent duplicate rows
    if ($_SESSION['ban_email']['id'] = hesk_isBannedEmail($email)) {
        hesk_process_messages(sprintf($hesklang['emailbanexists'], $email), $redirect_to, 'NOTICE');
    }

    // Insert the email address into database
    hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "banned_emails` (`email`,`banned_by`) VALUES ('" . hesk_dbEscape($email) . "','" . intval($_SESSION['id']) . "')");

    // Remember email that got banned
    $_SESSION['ban_email']['id'] = hesk_dbInsertID();

    // Show success
    hesk_process_messages(sprintf($hesklang['email_banned'], $email), $redirect_to, 'SUCCESS');

} // End ban_email()


function unban_email()
{
    global $hesk_settings, $hesklang;

    // A security check
    hesk_token_check();

    // Delete from bans
    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "banned_emails` WHERE `id`=" . intval(hesk_GET('id')));

    // Redirect either to banned emails or ticket page from now on
    $redirect_to = ($trackingID = hesk_cleanID()) ? 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999) : 'banned_emails.php';

    // Show success
    hesk_process_messages($hesklang['email_unbanned'], $redirect_to, 'SUCCESS');

} // End unban_email()


function verify_email_domain($domain)
{
    // Does it start with an @?
    $atIndex = strrpos($domain, "@");
    if ($atIndex !== 0) {
        return false;
    }

    // Get the domain and domain length
    $domain = substr($domain, 1);
    $domainLen = strlen($domain);

    // Check domain part length
    if ($domainLen < 1 || $domainLen > 254) {
        return false;
    }

    // Check domain part characters
    if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
        return false;
    }

    // Domain part mustn't have two consecutive dots
    if (strpos($domain, '..') !== false) {
        return false;
    }

    // All OK
    return true;

} // END verify_email_domain()

?>
