<?php
/*******************************************************************************
 *  Title: Help Desk Software HESK
 *  Version: 2.6.7 from 18th April 2016
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

/* Check if this is a valid include */
if (!defined('IN_SCRIPT')) {
    die('Invalid attempt');
}

$num_mail = hesk_checkNewMail();
?>
<?php
// Show a notice if we are in maintenance mode
if (hesk_check_maintenance(false)) {
    echo '<div style="margin-bottom: -20px">';
    hesk_show_notice($hesklang['mma2'], $hesklang['mma1'], false);
    echo '</div>';
}

// Show a notice if we are in "Knowledgebase only" mode
if (hesk_check_kb_only(false)) {
    echo '<div style="margin-bottom: -20px">';
    hesk_show_notice($hesklang['kbo2'], $hesklang['kbo1'], false);
    echo '</div>';
}
?>
<div class="enclosing">
    <nav class="navbar navbar-default navbar-static-top" role="navigation">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?php echo $modsForHesk_settings['navbar_title_url']; ?>"><?php echo $hesk_settings['hesk_title'] ?></a>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <?php
                $tools_count = 0;
                $dropdown_items = '<ul class="dropdown-menu" role="menu">';
                if (hesk_checkPermission('can_ban_emails', 0)) {
                    $tools_count++;
                    $dropdown_items .= '<li><a href="banned_emails.php">' . $hesklang['manage_banned_emails'] . '</a></li>';
                }
                if (hesk_checkPermission('can_ban_ips', 0)) {
                    $tools_count++;
                    $dropdown_items .= '<li><a href="banned_ips.php">' . $hesklang['manage_banned_ips'] . '</a></li>';
                }
                if (hesk_checkPermission('can_service_msg', 0)) {
                    $tools_count++;
                    $dropdown_items .= '<li><a href="service_messages.php">' . $hesklang['manage_service_messages'] . '</a></li>';
                }
                if (hesk_checkPermission('can_man_email_tpl', 0)) {
                    $tools_count++;
                    $dropdown_items .= '<li><a href="manage_email_templates.php">' . $hesklang['manage_email_templates'] . '</a></li>';
                }
                if (hesk_checkPermission('can_man_ticket_statuses', 0)) {
                    $tools_count++;
                    $dropdown_items .= '<li><a href="manage_statuses.php">' . $hesklang['manage_statuses'] . '</a></li>';
                }
                if (hesk_checkPermission('can_view_logs', 0)) {
                    $tools_count++;
                    $dropdown_items .= '<li><a href="view_message_log.php">' . $hesklang['view_message_log'] . '</a></li>';
                }
                $dropdown_items .= '</ul>';

                if ($tools_count > 1) {
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_TOOLS') {
                        $active = ' active';
                    }
                    echo '<li class="dropdown'.$active.'">
                              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                  <i class="fa fa-wrench" ' . $iconDisplay . '></i>&nbsp;' . $hesklang['tools'] . '<span class="caret"></span>
                              </a>
                              '.$dropdown_items.'
                          </li>';
                } else {
                    if (hesk_checkPermission('can_ban_emails', 0)) {
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_TOOLS') {
                            $active = ' class="active"';
                        }
                        echo '<li'.$active.'><a href="banned_emails.php"><i class="fa fa-wrench" ' . $iconDisplay . '></i>&nbsp;' . $hesklang['tools'] . '</a></li>';
                    } elseif (hesk_checkPermission('can_ban_ips', 0)) {
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_TOOLS') {
                            $active = ' class="active"';
                        }
                        echo '<li'.$active.'><a href="banned_ips.php"><i class="fa fa-wrench" ' . $iconDisplay . '></i>&nbsp;' . $hesklang['tools'] . '</a></li>';
                    } elseif (hesk_checkPermission('can_service_msg', 0)) {
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_TOOLS') {
                            $active = ' class="active"';
                        }
                        echo '<li'.$active.'><a href="service_messages.php"><i class="fa fa-wrench" ' . $iconDisplay . '></i>&nbsp;' . $hesklang['tools'] . '</a></li>';
                    } elseif (hesk_checkPermission('can_man_email_tpl', 0)) {
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_TOOLS') {
                            $active = ' class="active"';
                        }
                        echo '<li'.$active.'><a href="manage_email_templates.php"><i class="fa fa-wrench" ' . $iconDisplay . '></i>&nbsp;' . $hesklang['tools'] . '</a></li>';
                    } elseif (hesk_checkPermission('can_man_ticket_statuses', 0)) {
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_TOOLS') {
                            $active = ' class="active"';
                        }
                        echo '<li'.$active.'><a href="manage_statuses.php"><i class="fa fa-wrench" ' . $iconDisplay . '></i>&nbsp;' . $hesklang['tools'] . '</a></li>';
                    } elseif (hesk_checkPermission('can_view_logs', 0)) {
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_TOOLS') {
                            $active = ' class="active"';
                        }
                        echo '<li'.$active.'><a href="view_message_log.php"><i class="fa fa-wrench" ' . $iconDisplay . '></i>&nbsp;' . $hesklang['tools'] . '</a></li>';
                    }
                }
                if (hesk_checkPermission('can_man_settings', 0)) {
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_SETTINGS') {
                        $active = ' class="active"';
                    }
                    echo '<li'.$active.'><a href="admin_settings.php"><i class="fa fa-cog" ' . $iconDisplay . '></i>&nbsp;' . $hesklang['settings'] . '</a></li>';
                }

                $active = '';
                if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_PROFILE') {
                    $active = ' class="active"';
                }
                ?>
                <li<?php echo $active; ?>><a href="profile.php"><i
                            class="fa fa-user" <?php echo $iconDisplay; ?>></i>&nbsp;<?php echo $hesklang['menu_profile']; ?>
                    </a></li>
                <?php
                $active = '';
                if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_MAIL') {
                    $active = ' class="active"';
                }
                ?>
                <li<?php echo $active; ?>><a href="mail.php"><i
                            class="fa fa-envelope-o" <?php echo $iconDisplay; ?>></i>&nbsp;<?php echo $hesklang['menu_msg']; ?>
                        <?php if ($num_mail != 0) {
                            echo '<span class="badge">';
                            echo $num_mail;
                            unset($num_mail);
                            echo '</span>';
                        } ?>
                    </a></li>
                <?php include('custom/show_admin_nav-custom.inc.php');
                $iconDisplay = ($iconDisplay == '') ? 'style="font-size: 14px"' : $iconDisplay;
                ?>
                <li><a href="index.php?a=logout&amp;token=<?php echo hesk_token_echo(); ?>"><span
                            class="octicon octicon-sign-out" <?php echo $iconDisplay; ?>></span>&nbsp;<?php echo $hesklang['logout']; ?>
                    </a></li>
            </ul>
        </div>
        <!-- /.navbar-collapse -->
    </nav>
