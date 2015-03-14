<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.6.1 from 26th February 2015
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

define('IN_SCRIPT',1);
define('HESK_PATH','../');

require(HESK_PATH . 'install/install_functions.inc.php');

// Reset installation steps
hesk_session_stop();

hesk_iHeader();
?>
<div class="setupContainer">
    <img src="hesk.png" alt="HESK Logo" />
    <br><br>
    <p>Thank you for downloading HESK. Please choose an option below.</p>
    <br>
    <br>
	<a class="btn btn-default btn-lg" href="install.php?" role="button">Setup</a>
	<p><br/>Install a new copy of HESK on your server</p>
	<br/><br/>
	<a class="btn btn-default btn-lg" href="update.php?" role="button">Upgrade</a>
	<p><br/>Upgrade existing HESK installation to version <?php echo HESK_NEW_VERSION; ?></p>
    <br/><br/>
    <a class="btn btn-default btn-lg" href="mods-for-hesk/modsForHesk.php" role="button">Install / Upgrade Mods for HESK</a>
    <p><br/>Install or upgrade existing Mods for HESK installation to version <?php echo MODS_FOR_HESK_NEW_VERSION; ?></p>
</div>

<?php
hesk_iFooter();
exit();
?>
