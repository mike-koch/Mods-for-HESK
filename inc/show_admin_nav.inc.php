<?php
/**
 *
 * This file is part of HESK - PHP Help Desk Software.
 *
 * (c) Copyright Klemen Stirn. All rights reserved.
 * http://www.hesk.com
 *
 * For the full copyright and license agreement information visit
 * http://www.hesk.com/eula.php
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

        <!-- Logo -->
        <a href="<?php echo $modsForHesk_settings['navbar_title_url']; ?>" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini"><!-- TODO Add setting for "Mini Title" --></span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><?php echo $hesk_settings['hesk_title'] ?></span>
        </a>

        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>
            <!-- Navbar Right Menu -->
            <div class="navbar-custom-menu">
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
                    <?php endif; ?>
                    <li class="dropdown messages-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-envelope-o"></i>
                            <?php if (count($mails) > 0): ?>
                            <span class="label label-success"><?php echo count($mails); ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header"><?php echo sprintf($hesklang['you_have_x_messages'],
                                    count($mails),
                                    count($mails) == 1
                                        ? $hesklang['message_lower_case']
                                        : $hesklang['messages_lower_case']); ?></li>
                            <!-- Begin New Messages -->
                            <li>
                                <!-- inner menu: contains the actual data -->
                                <ul class="menu">
                                    <?php foreach ($mails as $mail): ?>
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
                            <li class="footer"><a href="mail.php">See All Messages</a></li>
                        </ul>
                    </li>
                    <!-- User Account: style can be found in dropdown.less -->
                    <li class="dropdown user user-menu">
                        <a href="profile.php">
                            <!--<img src="dist/img/user2-160x160.jpg" class="user-image" alt="User Image">-->
                            <i class="fa fa-user"></i>
                            <span class="hidden-xs"><?php echo hesk_SESSION('name'); ?></span>
                        </a>
                    </li>
                    <!-- Control Sidebar Toggle Button -->
                    <li>
                        <a href="index.php?a=logout&amp;token=<?php echo hesk_token_echo(); ?>">
                            <i class="octicon octicon-sign-out"></i>
                        </a>
                    </li>
                </ul>
            </div>

        </nav>
    </header>
    <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
            <!-- sidebar menu: : style can be found in sidebar.less -->
            <ul class="sidebar-menu">
                <li class="header"><?php echo $hesklang['main_navigation_uppercase']; ?></li>
                <?php
                $active = '';
                if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_HOME') {
                    $active = 'active';
                }
                ?>
                <li class="<?php echo $active; ?> treeview">
                    <a href="admin_main.php">
                        <i class="fa fa-home" <?php echo $iconDisplay; ?>></i> <span><?php echo $hesklang['main_page']; ?></span>
                    </a>
                </li>
                <?php if (hesk_checkPermission('can_man_users', 0) && hesk_checkPermission('can_man_permission_tpl', 0)) :
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_USERS') {
                        $active = 'active';
                    }
                ?>
                <li class="<?php echo $active; ?> treeview">
                    <a href="#">
                        <i class="fa fa-users" <?php echo $iconDisplay; ?>></i>
                        <span><?php echo $hesklang['menu_users']; ?></span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li>
                            <a href="manage_users.php"><i class="fa fa-circle-o"></i> <?php echo $hesklang['manage_users']; ?></a>
                        </li>
                        <li>
                            <a href="manage_permission_templates.php"><i class="fa fa-circle-o"></i> <?php echo $hesklang['permission_tpl_man']; ?></a>
                        </li>
                    </ul>
                </li>
                <?php
                elseif (hesk_checkPermission('can_man_users', 0)) :
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_USERS') {
                        $active = 'active';
                    }
                ?>
                <li class="<?php echo $active; ?> treeview">
                    <a href="manage_users.php">
                        <i class="fa fa-users" <?php echo $iconDisplay; ?>></i>
                        <span><?php echo $hesklang['menu_users']; ?></span>
                    </a>
                </li>
                <?php
                elseif (hesk_checkPermission('can_man_permission_tpl', 0)) :
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_USERS') {
                        $active = 'active';
                    }
                ?>
                <li class="<?php echo $active; ?> treeview">
                    <a href="manage_permission_templates.php">
                        <i class="fa fa-users" <?php echo $iconDisplay; ?>></i>
                        <span><?php echo $hesklang['permission_templates']; ?></span>
                    </a>
                </li>
                <?php
                endif;
                if (hesk_checkPermission('can_man_cat', 0)):
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_CATEGORIES') {
                        $active = 'active';
                    }
                ?>
                    <li class="<?php echo $active; ?> treeview">
                        <a href="manage_categories.php">
                            <i class="fa fa-pie-chart" <?php echo $iconDisplay; ?>></i>
                            <span><?php echo $hesklang['menu_cat']; ?></span>
                        </a>
                    </li>
                <?php
                endif;
                if (hesk_checkPermission('can_man_canned', 0) && hesk_checkPermission('can_man_ticket_tpl', 0)):
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_CANNED') {
                        $active = 'active';
                    }
                ?>
                    <li class="<?php echo $active; ?> treeview">
                        <a href="#">
                            <i class="fa fa-file-text-o" <?php echo $iconDisplay; ?>></i>
                            <span><?php echo $hesklang['menu_can']; ?></span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                        </a>
                        <ul class="treeview-menu">
                            <li>
                                <a href="manage_canned.php"><i class="fa fa-circle-o"></i> <?php echo $hesklang['can_man_canned']; ?></a>
                            </li>
                            <li>
                                <a href="manage_ticket_templates.php"><i class="fa fa-circle-o"></i> <?php echo $hesklang['ticket_tpl_man']; ?></a>
                            </li>
                        </ul>
                    </li>
                <?php
                elseif (hesk_checkPermission('can_man_canned', 0)):
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_CANNED') {
                        $active = 'active';
                    }
                ?>
                    <li class="<?php echo $active; ?> treeview">
                        <a href="manage_canned.php">
                            <i class="fa fa-file-text-o" <?php echo $iconDisplay; ?>></i>
                            <span><?php echo $hesklang['menu_can']; ?></span>
                        </a>
                    </li>
                <?php
                elseif (hesk_checkPermission('can_man_ticket_tpl', 0)):
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_CANNED') {
                        $active = 'active';
                    }
                ?>
                    <li class="<?php echo $active; ?> treeview">
                        <a href="manage_ticket_templates.php">
                            <i class="fa fa-file-text-o" <?php echo $iconDisplay; ?>></i>
                            <span><?php echo $hesklang['menu_can']; ?></span>
                        </a>
                    </li>
                <?php
                endif;
                if ($hesk_settings['kb_enable']):
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_KB') {
                        $active = 'active';
                    }
                    if (hesk_checkPermission('can_man_kb', 0)):
                ?>
                        <li class="<?php echo $active; ?> treeview">
                            <a href="manage_knowledgebase.php">
                                <i class="fa fa-book" <?php echo $iconDisplay; ?>></i>
                                <span><?php echo $hesklang['menu_kb']; ?></span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="<?php echo $active; ?> treeview">
                            <a href="knowledgebase_private.php">
                                <i class="fa fa-book" <?php echo $iconDisplay; ?>></i>
                                <span><?php echo $hesklang['menu_kb']; ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php
                endif;
                if ($modsForHesk_settings['enable_calendar'] != 0):
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_CALENDAR') {
                        $active = 'active';
                    }
                ?>
                    <li class="<?php echo $active; ?> treeview">
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
                        $active = 'active';
                    }
                ?>
                    <li class="<?php echo $active; ?> treeview">
                        <a href="reports.php">
                            <i class="fa fa-line-chart" <?php echo $iconDisplay; ?>></i>
                            <span><?php echo $hesklang['reports']; ?></span>
                        </a>
                    </li>
                <?php
                elseif (hesk_checkPermission('can_export', 0)):
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_REPORTS') {
                        $active = 'active';
                    }
                ?>
                    <li class="<?php echo $active; ?> treeview">
                        <a href="export.php">
                            <i class="fa fa-line-chart" <?php echo $iconDisplay; ?>></i>
                            <span><?php echo $hesklang['reports']; ?></span>
                        </a>
                    </li>
                <?php
                endif;

                $tools_count = 0;
                $dropdown_items = array();
                if (hesk_checkPermission('can_ban_emails', 0)) {
                    $tools_count++;
                    $dropdown_items['banned_emails'] = $hesklang['manage_banned_emails'];
                }
                if (hesk_checkPermission('can_ban_ips', 0)) {
                    $tools_count++;
                    $dropdown_items['banned_ips'] = $hesklang['manage_banned_ips'];
                }
                if (hesk_checkPermission('can_service_msg', 0)) {
                    $tools_count++;
                    $dropdown_items['service_messages'] = $hesklang['manage_service_messages'];
                }
                if (hesk_checkPermission('can_man_email_tpl', 0)) {
                    $tools_count++;
                    $dropdown_items['manage_email_templates'] = $hesklang['manage_email_templates'];
                }
                if (hesk_checkPermission('can_man_ticket_statuses', 0)) {
                    $tools_count++;
                    $dropdown_items['manage_statuses'] = $hesklang['manage_statuses'];
                }
                if (hesk_checkPermission('can_man_settings', 0)) {
                    $tools_count++;
                    $dropdown_items['custom_fields'] = $hesklang['manage_custom_fields'];
                }
                if (hesk_checkPermission('can_view_logs', 0)) {
                    $tools_count++;
                    $dropdown_items['view_message_log'] = $hesklang['view_message_log'];
                }

                if (count($dropdown_items) > 1):
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_TOOLS') {
                        $active = 'active';
                    }
                ?>
                    <li class="<?php echo $active; ?> treeview">
                        <a href="#">
                            <i class="fa fa-wrench" <?php echo $iconDisplay; ?>></i>
                            <span><?php echo $hesklang['tools']; ?></span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                        </a>
                        <ul class="treeview-menu">
                            <?php foreach($dropdown_items as $path => $text): ?>
                                <li>
                                    <a href="<?php echo $path; ?>.php"><i class="fa fa-circle-o"></i> <?php echo $text; ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php
                elseif (count($dropdown_items) == 1):
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_TOOLS') {
                        $active = 'active';
                    }
                    reset($dropdown_items);
                    $page = key($dropdown_items);
                ?>
                    <li class="<?php echo $active; ?> treeview">
                        <a href="<?php echo $page; ?>.php">
                            <i class="fa fa-wrench" <?php echo $iconDisplay; ?>></i>
                            <span><?php echo $dropdown_items[$page]; ?></span>
                        </a>
                    </li>
                <?php
                endif;
                if (hesk_checkPermission('can_man_settings', 0)):
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_SETTINGS') {
                        $active = 'active';
                    }
                ?>
                    <li class="<?php echo $active; ?> treeview">
                        <a href="admin_settings.php">
                            <i class="fa fa-cog" <?php echo $iconDisplay; ?>></i>
                            <span><?php echo $hesklang['settings']; ?></span>
                        </a>
                    </li>
                <?php
                endif;
                $active = '';
                if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_PROFILE') {
                    $active = 'active';
                }
                ?>
                <li class="<?php echo $active; ?> treeview">
                    <a href="profile.php">
                        <i class="fa fa-user" <?php echo $iconDisplay; ?>></i>
                        <span><?php echo $hesklang['menu_profile']; ?></span>
                    </a>
                </li>
            </ul>
        </section>
        <!-- /.sidebar -->
    </aside>
    <div class="content-wrapper">