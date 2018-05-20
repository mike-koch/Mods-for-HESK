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
$hesk_settings['hesk_license']('HAgc3R5bGU9InRleHQtYWxpZ246Y2VudGVyIj48c3BhbiBjb
GFzcz0ic21hbGxlciI+Jm5ic3A7PGJyIC8+UG93ZXJlZCBieSA8YSBocmVmPSJodHRwczovL3d3dy5oZ
XNrLmNvbSIgY2xhc3M9InNtYWxsZXIiIHRpdGxlPSJGcmVlIFBIUCBIZWxwIERlc2sgU29mdHdhcmUiP
khlbHAgRGVzayBTb2Z0d2FyZTwvYT4gPGI+SEVTSzwvYj4sIGJyb3VnaHQgdG8geW91IGJ5IDxhIGhyZ
WY9Imh0dHBzOi8vd3d3LnN5c2FpZC5jb20vP3V0bV9zb3VyY2U9SGVzayZhbXA7dXRtX21lZGl1bT1jc
GMmYW1wO3V0bV9jYW1wYWlnbj1IZXNrUHJvZHVjdF9Ub19IUCI+U3lzQWlkPC9hPjwvc3Bhbj48L3A+'
,"\120");

include(HESK_PATH . 'footer.txt');

$hesk_settings['security_cleanup']('exit');