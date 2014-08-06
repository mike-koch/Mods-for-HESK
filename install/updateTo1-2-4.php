<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.5.5 from 5th August 2014
*  Author: Klemen Stirn
*  Website: http://www.hesk.com
********************************************************************************
*  COPYRIGHT AND TRADEMARK NOTICE
*  Copyright 2005-2014 Klemen Stirn. All Rights Reserved.
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
define('INSTALL',1);
define('HESK_NEW_VERSION','2.5.5');
define('HESK_OLD_VERSION','2.5.3');
define('HESK_PATH','../');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');

if ($hesk_settings['hesk_version'] == HESK_NEW_VERSION)
{
die('
<pre>

<span style="color:darkorange;font-weight:bold;">NOTICE:</span> You already have version 1.2.3 (Hesk version ' . HESK_NEW_VERSION . ') installed.

</pre>');
}

if ($hesk_settings['hesk_version'] != HESK_OLD_VERSION)
{
die('
<pre>

<span style="color:red;font-weight:bold;">ERROR: You do not have version 1.2.3 (Hesk version ' . HESK_OLD_VERSION . ')</span>

This patch may only be used with NuMods version 1.2.3 (Hesk version ' . HESK_OLD_VERSION . '), your HESK version is ' . $hesk_settings['hesk_version'] . '

You should <a href="http://www.hesk.com/download.php">Download full version ' . HESK_NEW_VERSION .'</a> instead, and NuMods from <a href="http://numods.mkochcs.com">http://numods.mkochcs.com</a>.

</pre>');
}

define('HIDE_ONLINE',1);

/* Debugging should be enabled in installation mode */
$hesk_settings['debug_mode'] = 1;
error_reporting(E_ALL);
$__maindir = dirname(dirname(__FILE__)) . '/';

hesk_iHeader();
?>
	<br />

    <div align="center">
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td width="7" height="7"><img src="../img/roundcornerslt.jpg" width="7" height="7" alt="" /></td>
		<td class="roundcornerstop"></td>
		<td><img src="../img/roundcornersrt.jpg" width="7" height="7" alt="" /></td>
	</tr>
	<tr>
		<td class="roundcornersleft">&nbsp;</td>
		<td>


<h3>Verifying HESK installation</h3>

<p>&nbsp;</p>

<p>Verifying your HESK installation...</p>

<?php
if ( hesk_verifyInstall() )
{
	?>
	<p><font color="#008000"><b>OK: new files uploaded.</b></font></p>

	<p>&nbsp;</p>

	<?php
	if (isset($hesk_settings['hesk_version']) && $hesk_settings['hesk_version'] == HESK_OLD_VERSION)
	{
    	if (is_writable($__maindir.'hesk_settings.inc.php'))
        {
	    	$sold = file_get_contents($__maindir.'hesk_settings.inc.php');
	        $snew = str_replace('$hesk_settings[\'hesk_version\']=\''. HESK_OLD_VERSION . '\';', '$hesk_settings[\'hesk_version\']=\''.HESK_NEW_VERSION.'\';', $sold);
	        file_put_contents($__maindir.'hesk_settings.inc.php', $snew, LOCK_EX);
			?>
			<p>&raquo; Your HESK has been updated to version <?php echo HESK_NEW_VERSION; ?>.</p>
		    <p>&raquo; <font color="#FF0000">To complete setup delete the <b>/install</b> folder from your server.</font></p>
			<?php
        }
        else
        {
        	?>
   			<p><font color="#FF0000"><b>Update failed:</b> Cannot write to your hesk_settings.inc.php file.</font></p>

            <p>Please make sure the hesk_settings.inc.php file is writable by PHP then click the <b>Try again</b> button below.</p>

			<form method="post" action="index.php">
			<p align="center"><input type="submit" value="Try again" class="orangebutton" onmouseover="hesk_btn(this,'orangebuttonover');" onmouseout="hesk_btn(this,'orangebutton');" /></p>
			</form>
            <?php
        }
	}
}
else
{
	?>
	<p><font color="#FF0000"><b>Missing files!</b></font></p>

	<p>&nbsp;</p>

	<p>Please upload all HESK <?php echo HESK_NEW_VERSION; ?> patch files to your server.</p>

	<form method="post" action="index.php">
	<p align="center"><input type="submit" value="Test again" class="orangebutton" onmouseover="hesk_btn(this,'orangebuttonover');" onmouseout="hesk_btn(this,'orangebutton');" /></p>
	</form>

	<?php
}
?>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>

		</td>
		<td class="roundcornersright">&nbsp;</td>
	</tr>
	<tr>
		<td><img src="../img/roundcornerslb.jpg" width="7" height="7" alt="" /></td>
		<td class="roundcornersbottom"></td>
		<td width="7" height="7"><img src="../img/roundcornersrb.jpg" width="7" height="7" alt="" /></td>
	</tr>
	</table>
    </div>

<?php
hesk_iFooter();
exit();


function hesk_verifyInstall()
{
	global $__maindir;

    if ( file_exists(HESK_PATH . 'inc/tiny_mce/3.5.11/tiny_mce.js') )
    {
    	return true;
    }

    return false;
}


function hesk_iHeader() {
    global $hesk_settings;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Install Hesk <?php echo HESK_NEW_VERSION; ?></title>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
	<link href="../hesk_style_v25.css" type="text/css" rel="stylesheet" />
	<script language="Javascript" type="text/javascript" src="../hesk_javascript_v25.js"></script>
    </head>
<body>


<div align="center">
<table border="0" cellspacing="0" cellpadding="5" class="enclosing">
<tr>
<td>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td width="3"><img src="../img/headerleftsm.jpg" width="3" height="25" alt="" /></td>
	<td class="headersm">HESK <?php echo HESK_NEW_VERSION; ?> installation script</td>
	<td width="3"><img src="../img/headerrightsm.jpg" width="3" height="25" alt="" /></td>
	</tr>
	</table>

	</td>
	</tr>
	<tr>
	<td>

<?php
} // End hesk_iHeader()


function hesk_iFooter() {
    global $hesk_settings;
?>
	<p style="text-align:center"><span class="smaller">&nbsp;<br />Powered by <a href="http://www.hesk.com" class="smaller" title="Free PHP Help Desk Software">Help Desk Software</a> <b>HESK</b> - brought to you by <a href="http://www.sysaid.com">Help Desk Software</a> SysAid</span></p></td></tr></table></div></body></html>
<?php
} // End hesk_iFooter()
?>

