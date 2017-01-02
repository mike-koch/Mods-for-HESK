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

// The closing div here is to close the content area on each page. Annoying, but necessary.
if (defined('ADMIN_PAGE')) {
	echo '
	</div>';
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
3dlcmVkIGJ5IDxhIGhyZWY9Imh0dHBzOi8vd3d3Lmhlc2suY29tIiBjbGFzcz0ic21hbGxlciIgdGl0b
GU9IkZyZWUgUEhQIEhlbHAgRGVzayBTb2Z0d2FyZSI+SGVscCBEZXNrIFNvZnR3YXJlPC9hPiA8Yj5IR
VNLPC9iPiwgYnJvdWdodCB0byB5b3UgYnkgPGEgaHJlZj0iaHR0cHM6Ly93d3cuc3lzYWlkLmNvbS8/d
XRtX3NvdXJjZT1IZXNrJmFtcDt1dG1fbWVkaXVtPWNwYyZhbXA7dXRtX2NhbXBhaWduPUhlc2tQcm9kd
WN0X1RvX0hQIj5TeXNBaWQ8L2E+PC9zcGFuPjwvcD4nOw0KfQ0KZWNobyAnPC90ZD48L3RyPjwvdGFib
GU+PC9kaXY+JzsNCmluY2x1ZGUoSEVTS19QQVRIIC4gJ2Zvb3Rlci50eHQnKTsNCmVjaG8gJzwvYm9ke
T48L2h0bWw+Jzs=',"\112");

exit();
