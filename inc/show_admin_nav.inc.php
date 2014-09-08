<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.5.5 from 5th August 2014
*  Author: Klemen Stirn
*  Website: http://www.hesk.com
********************************************************************************
*  COPYRIGHT AND TRADEMARK NOTICE
*  Copyright 2005-2013 Klemen Stirn. All Rights Reserved.
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
if (!defined('IN_SCRIPT')) {die('Invalid attempt');} 

$num_mail = hesk_checkNewMail();
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
		    <a class="navbar-brand" href="<?php echo HESK_PATH; ?>"><?php echo $hesk_settings['hesk_title'] ?></a>
	    </div>
	    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav">
          <li><a href="admin_main.php"><i class="fa fa-home" <?php echo $display; ?>></i>&nbsp;<?php echo $hesklang['main_page']; ?></a></li>
          <?php if (hesk_checkPermission('can_man_users',0)){echo '<li><a href="manage_users.php"><i class="fa fa-users" '.$display.'></i>&nbsp;'.$hesklang['menu_users'].'</a></li>';} 
                if (hesk_checkPermission('can_man_cat',0))  {echo '<li><a href="manage_categories.php"><i class="fa fa-pie-chart" '.$display.'></i>&nbsp;'.$hesklang['menu_cat'].'</a></li>';}
                if (hesk_checkPermission('can_man_canned',0)) {echo '<li><a href="manage_canned.php"><i class="fa fa-file-text-o" '.$display.'></i>&nbsp;'.$hesklang['menu_can'].'</a></li>';}
                if (hesk_checkPermission('can_man_kb',0)) {echo '<li><a href="manage_knowledgebase.php"><i class="fa fa-book" '.$display.'></i>&nbsp;'.$hesklang['menu_kb'].'</a></li>';}
                    else {echo '<li><a href="knowledgebase_private.php"><i class="fa fa-book" '.$display.'></i>&nbsp;'.$hesklang['menu_kb'].'</a></li>';} 
                if (hesk_checkPermission('can_run_reports',0)) {echo '<li><a href="reports.php"><i class="fa fa-line-chart" '.$display.'></i>&nbsp;'.$hesklang['reports'].'</a></li>';}
                    elseif (hesk_checkPermission('can_export',0)) {echo '<li><a href="export.php"><i class="fa fa-line-chart" '.$display.'></i>&nbsp;'.$hesklang['reports'].'</a></li>';}
                if (hesk_checkPermission('can_man_settings',0)) {echo '<li><a href="admin_settings.php"><i class="fa fa-cog" '.$display.'></i>&nbsp;'.$hesklang['settings'].'</a></li>';} ?>
          <li><a href="profile.php"><i class="fa fa-user" <?php echo $display; ?>></i>&nbsp;<?php echo $hesklang['menu_profile']; ?></a></li>
          <li><a href="mail.php"><i class="fa fa-envelope-o" <?php echo $display; ?>></i>&nbsp;<?php echo $hesklang['menu_msg']; ?>
              <?php if ($num_mail != 0)
                {
                    echo '<span class="badge">';
                    echo $num_mail;
                    unset($num_mail);
                    echo '</span>';
                } ?>
              </a></li>
          <?php include('custom/show_admin_nav-custom.inc.php'); 
          $display = ($display == '') ? 'style="font-size: 14px"' : $display;
          ?>
          <li><a href="index.php?a=logout&amp;token=<?php echo hesk_token_echo(); ?>"><span class="octicon octicon-sign-out" <?php echo $display; ?>></span>&nbsp;<?php echo $hesklang['logout']; ?></a></li>
        </ul>
      </div><!-- /.navbar-collapse -->
    </nav>
