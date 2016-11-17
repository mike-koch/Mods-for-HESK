<?php
/*******************************************************************************
 *  Title: Help Desk Software HESK
 *  Version: 2.6.8 from 10th August 2016
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

define('IN_SCRIPT', 1);
define('HESK_PATH', './');

// Get all the required files and functions
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');

// Feature enabled?
if (!$hesk_settings['detect_typos']) {
    die('');
}

// Print XML header
header('Content-Type: text/html; charset=' . $hesklang['ENCODING']);
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Get the search query composed of the subject and message
$address = hesk_REQUEST('e') or die('');
$email_field = hesk_REQUEST('ef') or die('');
$display_div = hesk_REQUEST('dd') or die('');
$pad_div = hesk_REQUEST('pd') ? 1 : 0;
$div = 1;

// Do we allow multiple emails? If yes, check all
if ($hesk_settings['multi_eml'] || hesk_REQUEST('am')) {
    // Make sure the format is correct
    $address = preg_replace('/\s/', '', $address);
    $address = str_replace(';', ',', $address);

    // Loops through emails and check for typos
    $div = 1;
    $all = explode(',', $address);
    foreach ($all as $address) {
        if (($suggest = hesk_emailTypo($address)) !== false) {
            hesk_emailTypoShow($address, $suggest, $div);
            $div++;
        }
    }
} // If multiple emails are not allowed, check just first one
elseif (($suggest = hesk_emailTypo($address)) !== false) {
    hesk_emailTypoShow($address, $suggest);
}

exit();


function hesk_emailTypoShow($address, $suggest, $div = '')
{
    global $hesk_settings, $hesklang, $email_field, $display_div, $pad_div;
    ?>
    <div id="emailtypo<?php echo $display_div.$div; ?>" style="display:block">
        <table border="0" width="100%">
            <tr>
                <td width="150">&nbsp;</td>
                <td width="80%">
                    <div class="alert alert-info">
                        <?php echo sprintf($hesklang['didum'], str_replace('@', '@<b>', $suggest . '</b>')); ?>
                        <br/><br/>
                        <a class="btn btn-default" href="javascript:void(0);" onclick="var eml=document.getElementById('<?php echo $email_field; ?>').value;document.getElementById('<?php echo $email_field; ?>').value=eml.replace(/<?php echo preg_quote($address, '/'); ?>/gi, '<?php echo addslashes($suggest); ?>' );document.getElementById('emailtypo<?php echo $display_div.$div; ?>').style.display='none';"><?php echo $hesklang['yfix']; ?></a>
                        <a class="btn btn-default" href="javascript:void(0);" onclick="document.getElementById('emailtypo<?php echo $display_div.$div; ?>').style.display='none';"><?php echo $hesklang['nole']; ?></a>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <?php
} // END hesk_emailTypoShow()


function hesk_emailTypo($address)
{
    global $hesk_settings;

    // Remove anything more than a single address
    $address = str_replace(strstr($address, ','), '', $address);
    $address = str_replace(strstr($address, ';'), '', $address);
    $address = strtolower(trim($address));

    // Get email domain
    $domain = substr(strrchr($address, '@'), 1);

    // If no domain return false
    if (!$domain) {
        return false;
    }

    // If we have an exact match return false
    if (in_array($domain, $hesk_settings['email_providers'])) {
        return false;
    }


    $shortest = -1;
    $closest = '';

    foreach ($hesk_settings['email_providers'] as $provider) {
        $similar = levenshtein($domain, $provider, 2, 1, 3);

        if ($similar < 1) {
            return false;
        }

        if ($similar < $shortest || $shortest < 0) {
            $closest = $provider;
            $shortest = $similar;
        }
    }

    if ($shortest < 4) {
        return str_replace($domain, $closest, $address);
    } else {
        return false;
    }
}  // END hesk_emailTypo()
?>
