</div>
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

// Check if this is a valid include
if (!defined('IN_SCRIPT')) {die('Invalid attempt');}

// Auto-select first empty or error field on non-staff pages?
if (defined('AUTOFOCUS'))
{
?>
<script language="javascript">
(function(){
	var forms = document.forms || [];
	for(var i = 0; i < forms.length; i++)
    {
		for(var j = 0; j < forms[i].length; j++)
        {
			if(
				!forms[i][j].readonly != undefined &&
				forms[i][j].type != "hidden" &&
				forms[i][j].disabled != true &&
				forms[i][j].style.display != 'none' &&
				(forms[i][j].className == 'isError' || forms[i][j].className == 'isNotice' || forms[i][j].value == '')
			)
	        {
				forms[i][j].focus();
				return;
			}
		}
	}
})();
</script>
<?php
}

// Users online
if (defined('SHOW_ONLINE'))
{
	hesk_printOnline();
}
?>
<footer class="main-footer">
	<?php
/*******************************************************************************
The code below handles HESK licensing. Removing or modifying this code without
purchasing a HESK license is strictly prohibited.

To purchase a HESK license and support future HESK development please visit:
https://www.hesk.com/buy.php
 *******************************************************************************/
$hesk_settings['hesk_license']('HMgPSAxOw0KaWYgKGZpbGVfZXhpc3RzKEhFU0tfUEFUSCAuI
CdoZXNrX2xpY2Vuc2UucGhwJykpDQp7DQokaCA9ICghZW1wdHkoJF9TRVJWRVJbJ0hUVFBfSE9TVCddK
SkgPyAkX1NFUlZFUlsnSFRUUF9IT1NUJ10gOiAoKCFlbXB0eSgkX1NFUlZFUlsnU0VSVkVSX05BTUUnX
SkpID8gJF9TRVJWRVJbJ1NFUlZFUl9OQU1FJ10gOiBnZXRlbnYoJ1NFUlZFUl9OQU1FJykpOw0KJGggP
SBzdHJfcmVwbGFjZSgnd3d3LicsJycsc3RydG9sb3dlcigkaCkpOw0KaW5jbHVkZShIRVNLX1BBVEggL
iAnaGVza19saWNlbnNlLnBocCcpOw0KaWYgKGlzc2V0KCRoZXNrX3NldHRpbmdzWydsaWNlbnNlJ10pI
CYmIHN0cnBvcygkaGVza19zZXR0aW5nc1snbGljZW5zZSddLHNoYTEoJGguJ2gzJkZwMiNMYUEmNTkhd
yg4LlpjXSordVI1MTInKSkgIT09IGZhbHNlKQ0Kew0KJHMgPSAwOw0KfQ0KZWxzZQ0Kew0KZWNobyAnP
HAgc3R5bGU9InRleHQtYWxpZ246Y2VudGVyO2NvbG9yOnJlZDsiPklOVkFMSUQgTElDRU5TRSAoTk9UI
FJFR0lTVEVSRUQgRk9SICcuJGguJykhPC9wPic7DQp9DQp9DQppZiAoJHMpDQp7DQplY2hvICc8cCBzd
HlsZT0idGV4dC1hbGlnbjpjZW50ZXIiPjxzcGFuIGNsYXNzPSJzbWFsbGVyIj4mbmJzcDs8YnIgLz5Qb
3dlcmVkIGJ5IDxhIGhyZWY9Imh0dHA6Ly93d3cuaGVzay5jb20iIGNsYXNzPSJzbWFsbGVyIiB0aXRsZ
T0iRnJlZSBQSFAgSGVscCBEZXNrIFNvZnR3YXJlIj5IZWxwIERlc2sgU29mdHdhcmU8L2E+IDxiPkhFU
0s8L2I+LCBicm91Z2h0IHRvIHlvdSBieSA8YSBocmVmPSJodHRwczovL3d3dy5zeXNhaWQuY29tLz91d
G1fc291cmNlPUhlc2smYW1wO3V0bV9tZWRpdW09Y3BjJmFtcDt1dG1fY2FtcGFpZ249SGVza1Byb2R1Y
3RfVG9fSFAiPlN5c0FpZDwvYT48L3NwYW4+PC9wPic7DQp9DQplY2hvICc8L3RkPjwvdHI+PC90YWJsZ
T48L2Rpdj4nOw0KaW5jbHVkZShIRVNLX1BBVEggLiAnZm9vdGVyLnR4dCcpOw0KZWNobyAnPC9ib2R5P
jwvaHRtbD4nOw==',"\112");
	?>
</footer>
<?php 
echo '</div>';

exit();
