<?php
/*******************************************************************************
 *  Title: Help Desk Software HESK
 *  Version: 2.6.6 from 2nd February 2016
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
hesk_checkPermission('can_ban_ips');
$can_unban = hesk_checkPermission('can_unban_ips', 0);

// Define required constants
define('LOAD_TABS', 1);

// What should we do?
if ($action = hesk_REQUEST('a')) {
    if (defined('HESK_DEMO')) {
        hesk_process_messages($hesklang['ddemo'], 'banned_ips.php', 'NOTICE');
    } elseif ($action == 'ban') {
        ban_ip();
    } elseif ($action == 'unban' && $can_unban) {
        unban_ip();
    } elseif ($action == 'unbantemp' && $can_unban) {
        unban_temp_ip();
    }
}

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>

<div class="row pad-20">
    <ul class="nav nav-tabs" role="tablist">
        <?php
        // Show a link to banned_emails.php if user has permission to do so
        if (hesk_checkPermission('can_ban_emails', 0)) {
            echo '
            <li role="presentation">
                <a title="' . $hesklang['banemail'] . '" href="banned_emails.php">' . $hesklang['banemail'] . '</a>
            </li>';
        }
        ?>
        <li role="presentation" class="active">
            <a href="#"><?php echo $hesklang['banip']; ?> <i class="fa fa-question-circle settingsquestionmark"
                                                             onclick="javascript:alert('<?php echo hesk_makeJsString($hesklang['banip_intro']); ?>')"></i></a>
        </li>
        <?php
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
                <?php
                /* This will handle error, success and notice messages */
                hesk_handle_messages();
                ?>
                <form action="banned_ips.php" method="post" name="form1" role="form" class="form-horizontal" data-toggle="validator">
                    <div class="form-group">
                        <label for="ip" class="col-sm-3 control-label"><?php echo $hesklang['bananip']; ?></label>

                        <div class="col-sm-9">
                            <input type="text" name="ip" size="30" maxlength="255" class="form-control" data-error="<?php echo htmlspecialchars($hesklang['enterbanip']); ?>"
                                   placeholder="<?php echo htmlspecialchars($hesklang['iprange']); ?>" required>
                            <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>"/>
                            <input type="hidden" name="a" value="ban"/>
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-9 col-sm-offset-3">
                            <input type="submit" value="<?php echo $hesklang['savebanip']; ?>" class="btn btn-default">
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-4">
                <h6 class="bold"><?php echo $hesklang['banex']; ?></h6>

                <div class="footerWithBorder blankSpace"></div>
                <b>123.0.0.0</b><br/>
                <b>123.0.0.1 - 123.0.0.53</b><br/>
                <b>123.0.0.0/24</b><br/>
                <b>123.0.*.*</b>
            </div>
        </div>
        <div class="row">
            <?php

            // Get login failures
            $res = hesk_dbQuery("SELECT `ip`, TIMESTAMPDIFF(MINUTE, NOW(), DATE_ADD(`last_attempt`, INTERVAL " . intval($hesk_settings['attempt_banmin']) . " MINUTE) ) AS `minutes` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "logins` WHERE `number` >= " . intval($hesk_settings['attempt_limit']) . " AND `last_attempt` > (NOW() -  INTERVAL " . intval($hesk_settings['attempt_banmin']) . " MINUTE)");
            $num = hesk_dbNumRows($res);

            echo '<h4>' . $hesklang['iptemp'] . '</h4>';

            if ($num > 0) {
                ?>
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th><?php echo $hesklang['ip']; ?></th>
                        <th><?php echo $hesklang['m2e']; ?></th>
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
                        echo '
                                    <tr>
                                    <td>' . $ban['ip'] . '</td>
                                    <td>' . $ban['minutes'] . '</td>
                                ';

                        if ($can_unban) {
                            echo '
                                    <td>
                                        <a href="banned_ips.php?a=ban&amp;ip=' . urlencode($ban['ip']) . '&amp;token=' . hesk_token_echo(0) . '">
                                            <i class="fa fa-ban red font-size-16p" data-toggle="tooltip" data-placement="top" data-original-title="' . $hesklang['ippermban'] . '"></i></a>
                                        <a href="banned_ips.php?a=unbantemp&amp;ip=' . urlencode($ban['ip']) . '&amp;token=' . hesk_token_echo(0) . '" onclick="return confirm_delete();">
                                            <i class="fa fa-times red font-size-16p" data-toggle="tooltip" data-placement="top" data-original-title="' . $hesklang['delban'] . '"></i></a>
                                    </td>
                                    ';
                        }

                        echo '</tr>';
                    } // End while

                    ?>
                    </tbody>
                </table>
                <?php
            } else {
                echo '<p>' . $hesklang['no_banips'] . '</p>';
            }

            // Get banned ips from database
            $res = hesk_dbQuery('SELECT * FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'banned_ips` ORDER BY `ip_from` ASC');
            $num = hesk_dbNumRows($res);

            echo '<br><h4>' . $hesklang['ipperm'] . '</h4>';

            if ($num < 1) {
                echo '<p>' . $hesklang['no_banips'] . '</p>';
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
                        <th><?php echo $hesklang['ip']; ?></th>
                        <th><?php echo $hesklang['iprange']; ?></th>
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
                        if (isset($_SESSION['ban_ip']['id']) && $ban['id'] == $_SESSION['ban_ip']['id']) {
                            $color = 'success';
                            unset($_SESSION['ban_ip']['id']);
                        }

                        echo '
                                <tr>
                                    <td class="' . $color . '">' . $ban['ip_display'] . '</td>
                                    <td class="' . $color . '">' . (($ban['ip_to'] == $ban['ip_from']) ? long2ip($ban['ip_to']) : long2ip($ban['ip_from']) . ' - ' . long2ip($ban['ip_to'])) . '</td>
                                    <td class="' . $color . '">' . (isset($admins[$ban['banned_by']]) ? $admins[$ban['banned_by']] : $hesklang['e_udel']) . '</td>
                                    <td class="' . $color . '">' . $ban['dt'] . '</td>
                            ';

                        if ($can_unban) {
                            echo '
                                <td class="' . $color . ' text-left">
                                    <a href="banned_ips.php?a=unban&amp;id=' . $ban['id'] . '&amp;token=' . hesk_token_echo(0) . '" onclick="return confirm_delete();">
                                        <i class="fa fa-times red font-size-16p" data-toggle="tooltip" data-placement="top" data-original-title="' . $hesklang['delban'] . '"></i></a>
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

<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


/*** START FUNCTIONS ***/

function ban_ip()
{
    global $hesk_settings, $hesklang;

    // A security check
    hesk_token_check();

    // Get the ip
    $ip = preg_replace('/[^0-9\.\-\/\*]/', '', hesk_REQUEST('ip'));
    $ip_display = str_replace('-', ' - ', $ip);

    // Nothing entered?
    if (!strlen($ip)) {
        hesk_process_messages($hesklang['enterbanip'], 'banned_ips.php');
    }

    // Convert asterisk to ranges
    if (strpos($ip, '*') !== false) {
        $ip = str_replace('*', '0', $ip) . '-' . str_replace('*', '255', $ip);
    }

    $ip_regex = '(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])';

    // Is this a single IP address?
    if (preg_match('/^' . $ip_regex . '$/', $ip)) {
        $ip_from = ip2long($ip);
        $ip_to = $ip_from;
    } // Is this an IP range?
    elseif (preg_match('/^' . $ip_regex . '\-' . $ip_regex . '$/', $ip)) {
        list($ip_from, $ip_to) = explode('-', $ip);
        $ip_from = ip2long($ip_from);
        $ip_to = ip2long($ip_to);
    } // Is this an IP with CIDR?
    elseif (preg_match('/^' . $ip_regex . '\/([0-9]{1,2})$/', $ip, $matches) && $matches[4] >= 0 && $matches[4] <= 32) {
        list($ip_from, $ip_to) = hesk_cidr_to_range($ip);
    } // Not a valid input
    else {
        hesk_process_messages($hesklang['validbanip'], 'banned_ips.php');
    }

    // Make sure we have valid ranges
    if ($ip_from < 0) {
        $ip_from += 4294967296;
    } elseif ($ip_from > 4294967296) {
        $ip_from = 4294967296;
    }
    if ($ip_to < 0) {
        $ip_to += 4294967296;
    } elseif ($ip_to > 4294967296) {
        $ip_to = 4294967296;
    }

    // Make sure $ip_to is not lower that $ip_from
    if ($ip_to < $ip_from) {
        $tmp = $ip_to;
        $ip_to = $ip_from;
        $ip_from = $tmp;
    }

    // Is this IP address already banned?
    $res = hesk_dbQuery("SELECT `id` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "banned_ips` WHERE {$ip_from} BETWEEN `ip_from` AND `ip_to` AND {$ip_to} BETWEEN `ip_from` AND `ip_to` LIMIT 1");
    if (hesk_dbNumRows($res) == 1) {
        $_SESSION['ban_ip']['id'] = hesk_dbResult($res);
        $hesklang['ipbanexists'] = ($ip_to == $ip_from) ? sprintf($hesklang['ipbanexists'], long2ip($ip_to)) : sprintf($hesklang['iprbanexists'], long2ip($ip_from) . ' - ' . long2ip($ip_to));
        hesk_process_messages($hesklang['ipbanexists'], 'banned_ips.php', 'NOTICE');
    }

    // Delete any duplicate banned IP or ranges that are within the new banned range
    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "banned_ips` WHERE `ip_from` >= {$ip_from} AND `ip_to` <= {$ip_to}");

    // Delete temporary bans from logins table
    if ($ip_to == $ip_from) {
        hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "logins` WHERE `ip`='" . hesk_dbEscape($ip_display) . "' LIMIT 1");
    }

    // Redirect either to banned ips or ticket page from now on
    $redirect_to = ($trackingID = hesk_cleanID()) ? 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999) : 'banned_ips.php';

    // Insert the ip address into database
    hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "banned_ips` (`ip_from`,`ip_to`,`ip_display`,`banned_by`) VALUES ({$ip_from}, {$ip_to},'" . hesk_dbEscape($ip_display) . "','" . intval($_SESSION['id']) . "')");

    // Remember ip that got banned
    $_SESSION['ban_ip']['id'] = hesk_dbInsertID();

    // Generate success message
    $hesklang['ip_banned'] = ($ip_to == $ip_from) ? sprintf($hesklang['ip_banned'], long2ip($ip_to)) : sprintf($hesklang['ip_rbanned'], long2ip($ip_from) . ' - ' . long2ip($ip_to));

    // Show success
    hesk_process_messages(sprintf($hesklang['ip_banned'], $ip), $redirect_to, 'SUCCESS');

} // End ban_ip()


function unban_temp_ip()
{
    global $hesk_settings, $hesklang;

    // A security check
    hesk_token_check();

    // Get the ip
    $ip = preg_replace('/[^0-9\.\-\/\*]/', '', hesk_REQUEST('ip'));

    // Delete from bans
    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "logins` WHERE `ip`='" . hesk_dbEscape($ip) . "' LIMIT 1");

    // Show success
    hesk_process_messages($hesklang['ip_tempun'], 'banned_ips.php', 'SUCCESS');

} // End unban_temp_ip()


function unban_ip()
{
    global $hesk_settings, $hesklang;

    // A security check
    hesk_token_check();

    // Delete from bans
    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "banned_ips` WHERE `id`=" . intval(hesk_GET('id')) . " LIMIT 1");

    // Redirect either to banned ips or ticket page from now on
    $redirect_to = ($trackingID = hesk_cleanID()) ? 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999) : 'banned_ips.php';

    // Show success
    hesk_process_messages($hesklang['ip_unbanned'], $redirect_to, 'SUCCESS');

} // End unban_ip()


function hesk_cidr_to_range($cidr)
{
    $range = array();
    $cidr = explode('/', $cidr);
    $range[0] = (ip2long($cidr[0])) & ((-1 << (32 - (int)$cidr[1])));
    $range[1] = (ip2long($cidr[0])) + pow(2, (32 - (int)$cidr[1])) - 1;
    return $range;
} // END hesk_cidr_to_range()

?>
