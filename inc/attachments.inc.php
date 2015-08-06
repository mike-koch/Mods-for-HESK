<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.6.4 from 22nd June 2015
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
if (!defined('IN_SCRIPT')) {die('Invalid attempt');} 

/***************************
Function hesk_uploadFiles()
***************************/
function hesk_uploadFile($i)
{
	global $hesk_settings, $hesklang, $trackingID, $hesk_error_buffer;

	/* Return if name is empty */
	if (empty($_FILES['attachment']['name'][$i])) {return '';}

    /* Parse the name */
	$file_realname = hesk_cleanFileName($_FILES['attachment']['name'][$i]);

	/* Check file extension */
	$ext = strtolower(strrchr($file_realname, "."));
	if ( ! in_array($ext,$hesk_settings['attachments']['allowed_types']))
	{
        return hesk_fileError(sprintf($hesklang['type_not_allowed'], $ext, $file_realname));
	}

	/* Check file size */
	if ($_FILES['attachment']['size'][$i] > $hesk_settings['attachments']['max_size'])
	{
	    return hesk_fileError(sprintf($hesklang['file_too_large'], $file_realname));
	}
	else
	{
	    $file_size = $_FILES['attachment']['size'][$i];
	}

	/* Generate a random file name */
	$useChars='AEUYBDGHJLMNPQRSTVWXZ123456789';
	$tmp = uniqid();
	for($j=1;$j<10;$j++)
	{
	    $tmp .= $useChars{mt_rand(0,29)};
	}

    if (defined('KB'))
    {
    	$file_name = substr(md5($tmp . $file_realname), 0, 200) . $ext;
    }
    else
    {
    	$file_name = substr($trackingID . '_' . md5($tmp . $file_realname), 0, 200) . $ext;
    }

    // Does the temporary file exist? If not, probably server-side configuration limits have been reached
    // Uncomment this for debugging purposes
    /*
    if ( ! file_exists($_FILES['attachment']['tmp_name'][$i]) )
    {
		return hesk_fileError($hesklang['fnuscphp']);
    }
    */

	/* If upload was successful let's create the headers */
	if ( ! move_uploaded_file($_FILES['attachment']['tmp_name'][$i], dirname(dirname(__FILE__)).'/'.$hesk_settings['attach_dir'].'/'.$file_name))
	{
	    return hesk_fileError($hesklang['cannot_move_tmp']);
	}

	$info = array(
	    'saved_name'=> $file_name,
	    'real_name' => $file_realname,
	    'size'      => $file_size
	);

	return $info;
} // End hesk_uploadFile()


function hesk_fileError($error)
{
	global $hesk_settings, $hesklang, $trackingID;
    global $hesk_error_buffer;

	$hesk_error_buffer['attachments'] = $error;

	return false;
} // End hesk_fileError()


function hesk_removeAttachments($attachments)
{
	global $hesk_settings, $hesklang;

	$hesk_settings['server_path'] = dirname(dirname(__FILE__)).'/'.$hesk_settings['attach_dir'].'/';

	foreach ($attachments as $myatt)
	{
		hesk_unlink($hesk_settings['server_path'].$myatt['saved_name']);
	}

	return true;
} // End hesk_removeAttachments()
