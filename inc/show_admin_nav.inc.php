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

/* Check if this is a valid include */
if (!defined('IN_SCRIPT')) {
    die('Invalid attempt');
}

$mails = mfh_get_mail_headers_for_dropdown($_SESSION['id'], $hesk_settings, $hesklang);
?>
<div class="wrapper">
    <header class="main-header">
        <?php if (defined('MFH_PAGE_LAYOUT') && MFH_PAGE_LAYOUT == 'TOP_AND_SIDE'): ?>
        <a href="<?php echo $modsForHesk_settings['navbar_title_url']; ?>" class="logo">
            <span class="logo-mini">&nbsp;</span>
            <span class="logo-lg"><?php echo $hesk_settings['hesk_title'] ?></span>
        </a>
        <?php endif; ?>

        <nav class="navbar navbar-static-top">
            <?php if(defined('MFH_PAGE_LAYOUT') && MFH_PAGE_LAYOUT == 'TOP_AND_SIDE'): ?>
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>
            <?php endif; ?>
            <div class="navbar-header">
                <?php if (defined('MFH_PAGE_LAYOUT') && MFH_PAGE_LAYOUT == 'TOP_ONLY'): ?>
                <a href="<?php echo $modsForHesk_settings['navbar_title_url']; ?>" class="navbar-brand logo">
                    <?php echo $hesk_settings['hesk_title'] ?>
                </a>
                <?php endif; ?>
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
                    <i class="fa fa-bars"></i>
                </button>
            </div>
            <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
                <ul class="nav navbar-nav">
                    <?php
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_HOME') {
                        $active = 'class="active"';
                    }
                    ?>
                    <li <?php echo $active; ?>>
                        <a href="admin_main.php">
                            <i class="fa fa-home" <?php echo $iconDisplay; ?>></i> <span><?php echo $hesklang['main_page']; ?></span>
                        </a>
                    </li>
                    <?php
                    if ($hesk_settings['kb_enable']):
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_KB') {
                            $active = 'class="active"';
                        }
                        if (hesk_checkPermission('can_man_kb', 0)):
                            ?>
                            <li <?php echo $active; ?>>
                                <a href="manage_knowledgebase.php">
                                    <i class="fa fa-book" <?php echo $iconDisplay; ?>></i>
                                    <span><?php echo $hesklang['menu_kb']; ?></span>
                                </a>
                            </li>
                        <?php else: ?>
                            <li <?php echo $active; ?>>
                                <a href="knowledgebase_private.php">
                                    <i class="fa fa-book" <?php echo $iconDisplay; ?>></i>
                                    <span><?php echo $hesklang['menu_kb']; ?></span>
                                </a>
                            </li>
                        <?php
                        endif;
                    endif;

                    if ($modsForHesk_settings['enable_calendar'] != 0):
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_CALENDAR') {
                            $active = 'class="active"';
                        }
                        ?>
                        <li <?php echo $active; ?>>
                            <a href="calendar.php">
                                <i class="fa fa-calendar" <?php echo $iconDisplay; ?>></i>
                                <span><?php echo $hesklang['calendar_title_case']; ?></span>
                            </a>
                        </li>
                        <?php
                    endif;

                    if (hesk_checkPermission('can_run_reports', 0)):
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_REPORTS') {
                            $active = 'class="active"';
                        }
                        ?>
                        <li <?php echo $active; ?>>
                            <a href="reports.php">
                                <i class="fa fa-line-chart" <?php echo $iconDisplay; ?>></i>
                                <span><?php echo $hesklang['reports']; ?></span>
                            </a>
                        </li>
                        <?php
                    elseif (hesk_checkPermission('can_export', 0)):
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_REPORTS') {
                            $active = 'class="active"';
                        }
                        ?>
                        <li <?php echo $active; ?>>
                            <a href="export.php">
                                <i class="fa fa-line-chart" <?php echo $iconDisplay; ?>></i>
                                <span><?php echo $hesklang['reports']; ?></span>
                            </a>
                        </li>
                        <?php
                    endif;
                    ?>
                </ul>
            </div>
            <!-- Navbar Right Menu -->
            <div class="navbar-custom-menu" id="header-right-side">
                <ul class="nav navbar-nav">
                    <!-- Messages: style can be found in dropdown.less-->
                    <?php
                    $number_of_maintenance_warnings = 0;
                    if (hesk_check_maintenance(false)) {
                        $number_of_maintenance_warnings++;
                    }
                    if (hesk_check_kb_only(false)) {
                        $number_of_maintenance_warnings++;
                    }
                    if ($number_of_maintenance_warnings > 0): ?>
                        <li class="dropdown messages-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-exclamation-triangle"></i>
                                <?php echo sprintf($hesklang['x_system_warnings'],
                                    $number_of_maintenance_warnings,
                                    $number_of_maintenance_warnings == 1
                                        ? $hesklang['warning_title_case']
                                        : $hesklang['warnings_title_case']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="header"><?php echo sprintf($hesklang['x_system_warnings'],
                                        $number_of_maintenance_warnings,
                                        $number_of_maintenance_warnings == 1
                                            ? $hesklang['warning_title_case']
                                            : $hesklang['warnings_title_case']); ?></li>
                                <li>
                                    <ul class="menu">
                                        <?php if (hesk_check_maintenance(false)): ?>
                                            <li>
                                                <a href="#">
                                                    <div class="pull-left">
                                                        <i class="fa fa-exclamation-triangle orange fa-2x"></i>
                                                    </div>
                                                    <h4>
                                                        <?php echo $hesklang['mma1']; ?>
                                                    </h4>
                                                    <p><?php echo $hesklang['mma2']; ?></p>
                                                </a>
                                            </li>
                                        <?php
                                        endif;
                                        if (hesk_check_kb_only(false)):
                                        ?>
                                            <li>
                                                <a href="#">
                                                    <div class="pull-left">
                                                        <i class="fa fa-exclamation-triangle orange fa-2x"></i>
                                                    </div>
                                                    <h4>
                                                        <?php echo $hesklang['kbo1']; ?>
                                                    </h4>
                                                    <p><?php echo $hesklang['kbo2']; ?></p>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    <?php
                    endif;

                    // Users online
                    if (defined('SHOW_ONLINE')) {
                        hesk_printOnline();
                    }
                    ?>
                    <li class="dropdown messages-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-envelope-o"></i>
                            <?php if (count($mails) > 0): ?>
                            <span class="label label-success"><?php echo count($mails); ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if (count($mails) > 0): ?>
                            <li class="header"><?php echo sprintf($hesklang['you_have_x_messages'],
                                    count($mails),
                                    count($mails) == 1
                                        ? $hesklang['message_lower_case']
                                        : $hesklang['messages_lower_case']); ?></li>
                            <?php endif; ?>
                            <!-- Begin New Messages -->
                            <li>
                                <!-- inner menu: contains the actual data -->
                                <ul class="menu">
                                    <?php if (count($mails) == 0): ?>
                                        <div class="text-center dropdown-empty">
                                            <i class="fa fa-envelope-o fa-3x"></i><br>
                                            <span class="fa-2x">
                                                <?php echo sprintf($hesklang['you_have_x_messages'],
                                                    count($mails),
                                                    $hesklang['messages_lower_case']); ?>
                                            </span>
                                        </div>
                                    <?php
                                        endif;
                                        foreach ($mails as $mail):
                                    ?>
                                    <li><!-- start message -->
                                        <a href="mail.php?a=read&id=<?php echo $mail['id']; ?>">
                                            <h4>
                                                <?php echo $mail['from']; ?>
                                                <small><i class="fa fa-clock-o"></i> <?php echo hesk_dateToString($mail['date'], 0, 0, 0, true); ?></small>
                                            </h4>
                                            <p><?php echo $mail['subject']; ?></p>
                                        </a>
                                    </li>
                                    <!-- end message -->
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                            <li class="footer"><a href="mail.php"><?php echo $hesklang['see_all_messages'] ?></a></li>
                        </ul>
                    </li>
                    <li class="dropdown user user-menu">
                        <a href="profile.php">
                            <!--<img src="dist/img/user2-160x160.jpg" class="user-image" alt="User Image">-->
                            <i class="fa fa-user"></i>
                            <span class="hidden-xs"><?php echo hesk_SESSION('name'); ?></span>
                        </a>
                    </li>
                    <?php
                    $number_of_settings = 0;
                    $markup = '';

                    if (hesk_checkPermission('can_man_users', 0)) {
                        $number_of_settings++;
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_USERS') {
                            $active = 'active ';
                        }
                        $markup .= '
                        <li class="' . $active . '">
                            <a href="manage_users.php">
                                <i class="fa fa-fw fa-users" ' . $iconDisplay . '></i>
                                <span>' . $hesklang['manage_users'] . '</span>
                            </a>
                        </li>';
                    }
                    if (hesk_checkPermission('can_man_permission_tpl', 0)) {
                        $number_of_settings++;
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_PERMISSION_TPL') {
                            $active = 'active';
                        }

                        $markup .= '
                        <li class="' . $active . '">
                            <a href="manage_permission_templates.php">
                                <i class="fa fa-fw fa-users" ' . $iconDisplay . '></i>
                                <span>' . $hesklang['permission_templates'] . '</span>
                            </a>
                        </li>';
                    }
                    if (hesk_checkPermission('can_man_cat', 0)) {
                        $number_of_settings++;
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_CATEGORIES') {
                            $active = 'active';
                        }

                        $markup .= '
                        <li class="' . $active . '">
                            <a href="manage_categories.php">
                                <i class="fa fa-fw fa-pie-chart" ' . $iconDisplay . '></i>
                                <span>' . $hesklang['menu_cat'] . '</span>
                            </a>
                        </li>';
                    }
                    if (hesk_checkPermission('can_man_canned', 0)) {
                        $number_of_settings++;
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_CANNED') {
                            $active = 'active';
                        }

                        $markup .= '
                        <li class="' . $active . '">
                            <a href="manage_canned.php">
                                <i class="fa fa-fw fa-file-text-o" ' . $iconDisplay . '></i>
                                <span>' . $hesklang['canned_responses_dropdown_title'] . '</span>
                            </a>
                        </li>';
                    }
                    if (hesk_checkPermission('can_man_ticket_tpl', 0)) {
                        $number_of_settings++;
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_TICKET_TPL') {
                            $active = 'active';
                        }

                        $markup .= '
                        <li class="' . $active . '">
                            <a href="manage_ticket_templates.php">
                                <i class="fa fa-fw fa-ticket" ' . $iconDisplay . '></i>
                                <span>' . $hesklang['ticket_tpl'] . '</span>
                            </a>
                        </li>';
                    }
                    if (hesk_checkPermission('can_ban_emails', 0)) {
                        $number_of_settings++;
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_BANNED_EMAILS') {
                            $active = 'active';
                        }

                        $markup .= '
                        <li class="' . $active . '">
                            <a href="banned_emails.php">
                                <i class="fa fa-fw fa-ban" ' . $iconDisplay . '></i>
                                <span>' . $hesklang['manage_banned_emails'] . '</span>
                            </a>
                        </li>';
                    }
                    if (hesk_checkPermission('can_ban_ips', 0)) {
                        $number_of_settings++;
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_BANNED_IPS') {
                            $active = 'active';
                        }

                        $markup .= '
                        <li class="' . $active . '">
                            <a href="banned_ips.php">
                                <i class="fa fa-fw fa-ban" ' . $iconDisplay . '></i>
                                <span>' . $hesklang['manage_banned_ips'] . '</span>
                            </a>
                        </li>';
                    }
                    if (hesk_checkPermission('can_service_msg', 0)) {
                        $number_of_settings++;
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_SERVICE_MESSAGES') {
                            $active = 'active';
                        }

                        $markup .= '
                        <li class="' . $active . '">
                            <a href="service_messages.php">
                                <i class="fa fa-fw fa-sticky-note-o" ' . $iconDisplay . '></i>
                                <span>' . $hesklang['sm_title'] . '</span>
                            </a>
                        </li>';
                    }
                    if (hesk_checkPermission('can_man_email_tpl', 0)) {
                        $number_of_settings++;
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_EMAIL_TEMPLATES') {
                            $active = 'active';
                        }

                        $markup .= '
                        <li class="' . $active . '">
                            <a href="manage_email_templates.php">
                                <i class="fa fa-fw fa-envelope-o" ' . $iconDisplay . '></i>
                                <span>' . $hesklang['manage_email_templates'] . '</span>
                            </a>
                        </li>';
                    }
                    if (hesk_checkPermission('can_man_ticket_statuses', 0)) {
                        $number_of_settings++;
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_STATUSES') {
                            $active = 'active';
                        }

                        $markup .= '
                        <li class="' . $active . '">
                            <a href="manage_statuses.php">
                                <i class="fa fa-fw fa-exchange" ' . $iconDisplay . '></i>
                                <span>' . $hesklang['manage_statuses'] . '</span>
                            </a>
                        </li>';
                    }
                    if (hesk_checkPermission('can_man_settings', 0)) {
                        $number_of_settings++;
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_CUSTOM_FIELDS') {
                            $active = 'active';
                        }

                        $markup .= '
                        <li class="' . $active . '">
                            <a href="custom_fields.php">
                                <i class="fa fa-fw fa-plus-square-o" ' . $iconDisplay . '></i>
                                <span>' . $hesklang['manage_custom_fields'] . '</span>
                            </a>
                        </li>';
                    }
                    if ($number_of_settings > 0 &&
                        (hesk_checkPermission('can_view_logs', 0) || hesk_checkPermission('can_man_settings', 0))) {
                        $markup .= '<li class="divider"></li>';
                    }
                    if (hesk_checkPermission('can_view_logs', 0)) {
                        $number_of_settings++;
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_LOGS') {
                            $active = 'active';
                        }

                        $markup .= '
                        <li class="' . $active . '">
                            <a href="view_message_log.php">
                                <i class="fa fa-fw fa-heartbeat" ' . $iconDisplay . '></i>
                                <span>' . $hesklang['view_message_log'] . '</span>
                            </a>
                        </li>';
                    }
                    if (hesk_checkPermission('can_man_settings', 0)) {
                        $number_of_settings++;
                        $active = '';
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_SETTINGS') {
                            $active = 'active';
                        }

                        $markup .= '
                        <li class="' . $active . '">
                            <a href="admin_settings.php">
                                <i class="fa fa-fw fa-cog" ' . $iconDisplay . '></i>
                                <span>' . $hesklang['helpdesk_settings'] . '</span>
                            </a>
                        </li>';
                    }

                    if ($number_of_settings == 1) :
                        echo $markup;
                    elseif ($number_of_settings > 1): ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button">
                            <i class="fa fa-cogs"></i> <?php echo $hesklang['settings']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php echo $markup; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="index.php?a=logout&amp;token=<?php echo hesk_token_echo(); ?>">
                            <i class="octicon octicon-sign-out"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>