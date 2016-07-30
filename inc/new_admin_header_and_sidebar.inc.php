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

$mails = mfh_get_mail_headers_for_dropdown($_SESSION['id'], $hesk_settings, $hesklang);

// Show a notice if we are in maintenance mode
/*if (hesk_check_maintenance(false)) {
    echo '<div style="margin-bottom: -20px">';
    hesk_show_notice($hesklang['mma2'], $hesklang['mma1'], false);
    echo '</div>';
}

// Show a notice if we are in "Knowledgebase only" mode
if (hesk_check_kb_only(false)) {
    echo '<div style="margin-bottom: -20px">';
    hesk_show_notice($hesklang['kbo2'], $hesklang['kbo1'], false);
    echo '</div>';
}*/
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
                                        <a href="#">
                                            <!-- TODO User avatars -->
                                            <!--<div class="pull-left">
                                                <img src="dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
                                            </div>-->
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
                            <li class="footer"><a href="#">See All Messages</a></li>
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
            <!-- Sidebar user panel -->
            <div class="user-panel">
                <div class="pull-left info" style="left: 0">
                    <p>Welcome, <?php echo hesk_SESSION('name'); ?></p>
                </div>
                <div class="pull-left image">
                    <!--<img src="dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">-->
                    &nbsp;
                </div>
            </div>
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
                    <a href="admin_new_main.php">
                        <i class="fa fa-home" <?php echo $iconDisplay; ?>></i> <span><?php echo $hesklang['main_page']; ?></span>
                    </a>
                </li>
                <?php if (hesk_checkPermission('can_man_users', 0) && hesk_checkPermission('can_man_permission_tpl', 0)) {
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_USERS') {
                        $active = ' active';
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
                } elseif (hesk_checkPermission('can_man_users', 0)) {
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_USERS') {
                        $active = ' class="active"';
                    }
                ?>
                <li class="<?php echo $active; ?> treeview">
                    <a href="manage_users.php">
                        <i class="fa fa-users" <?php echo $iconDisplay; ?>></i>
                        <span><?php echo $hesklang['menu_users']; ?></span>
                    </a>
                </li>
                <?php
                } elseif (hesk_checkPermission('can_man_permission_tpl', 0)) {
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'ADMIN_USERS') {
                        $active = ' class="active"';
                    }
                ?>
                <li class="<?php echo $active; ?> treeview">
                    <a href="manage_permission_templates.php">
                        <i class="fa fa-users" <?php echo $iconDisplay; ?>></i>
                        <span><?php echo $hesklang['permission_templates']; ?></span>
                    </a>
                </li>
                <?php } ?>
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-files-o"></i>
                        <span>Layout Options</span>
                        <span class="pull-right-container">
                            <span class="label label-primary pull-right">4</span>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="pages/layout/top-nav.html"><i class="fa fa-circle-o"></i> Top Navigation</a></li>
                        <li><a href="pages/layout/boxed.html"><i class="fa fa-circle-o"></i> Boxed</a></li>
                        <li><a href="pages/layout/fixed.html"><i class="fa fa-circle-o"></i> Fixed</a></li>
                        <li><a href="pages/layout/collapsed-sidebar.html"><i class="fa fa-circle-o"></i> Collapsed Sidebar</a></li>
                    </ul>
                </li>
            </ul>
        </section>
        <!-- /.sidebar -->
    </aside>
    <div class="content-wrapper">