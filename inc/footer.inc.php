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

// The closing div here is to close the content area on each page. Annoying, but necessary.
if (defined('ADMIN_PAGE')) {
	echo '
	</div>
	<footer class="main-footer">';
}


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

if (defined('ADMIN_PAGE')) {
	echo '</footer>';
}

exit();
