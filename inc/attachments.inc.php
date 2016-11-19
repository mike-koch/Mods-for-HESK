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

/* Check if this is a valid include */
if (!defined('IN_SCRIPT')) {
    die('Invalid attempt');
}

/***************************
 * Function hesk_uploadFiles()
 ***************************/
function hesk_uploadFile($i, $isTicket = true)
{
    global $hesk_settings, $hesklang, $trackingID, $hesk_error_buffer, $modsForHesk_settings;

    /* Return if name is empty */
    $name = $i == -1
        ? $_FILES['attachment']['name']
        : $_FILES['attachment']['name'][$i];
    if (empty($name)) {
        return '';
    }


    /* Parse the name */
    $file_realname = hesk_cleanFileName($name);

    /* Check file extension */
    $ext = strtolower(strrchr($file_realname, "."));
    if (!in_array($ext, $hesk_settings['attachments']['allowed_types'])) {
        return hesk_fileError(sprintf($hesklang['type_not_allowed'], $ext, $file_realname));
    }

    /* Check file size */
    $size = $i == -1
        ? $_FILES['attachment']['size']
        : $_FILES['attachment']['size'][$i];
    if ($size > $hesk_settings['attachments']['max_size']) {
        return hesk_fileError(sprintf($hesklang['file_too_large'], $file_realname));
    } else {
        $file_size = $size;
    }

    /* Generate a random file name */
    $useChars = 'AEUYBDGHJLMNPQRSTVWXZ123456789';
    $tmp = uniqid();
    for ($j = 1; $j < 10; $j++) {
        $tmp .= $useChars{mt_rand(0, 29)};
    }


    $file_name = substr(md5($tmp . $file_realname), 0, 200) . $ext;

    // Does the temporary file exist? If not, probably server-side configuration limits have been reached
    // Uncomment this for debugging purposes
    /*
    if ( ! file_exists($_FILES['attachment']['tmp_name'][$i]) )
    {
		return hesk_fileError($hesklang['fnuscphp']);
    }
    */

    /* If upload was successful let's create the headers */
    $directory = $hesk_settings['attach_dir'];
    if (!$isTicket) {
        $directory = $modsForHesk_settings['kb_attach_dir'];
    }
    $file_to_move = $i == -1
        ? $_FILES['attachment']['tmp_name']
        : $_FILES['attachment']['tmp_name'][$i];
    if (!move_uploaded_file($file_to_move, dirname(dirname(__FILE__)) . '/' . $directory . '/' . $file_name)) {
        return hesk_fileError($hesklang['cannot_move_tmp']);
    }

    $info = array(
        'saved_name' => $file_name,
        'real_name' => $file_realname,
        'size' => $file_size
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


function hesk_removeAttachments($attachments, $isTicket)
{
    global $hesk_settings, $hesklang, $modsForHesk_settings;

    $directory = $hesk_settings['attach_dir'];
    if (!$isTicket) {
        $directory = $modsForHesk_settings['kb_attach_dir'];
    }

    $hesk_settings['server_path'] = dirname(dirname(__FILE__)) . '/' . $directory . '/';

    foreach ($attachments as $myatt) {
        hesk_unlink($hesk_settings['server_path'] . $myatt['saved_name']);
    }

    return true;
} // End hesk_removeAttachments()


function mfh_getTemporaryAttachment($id) {
    global $hesk_settings;

    $rs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "temp_attachment` WHERE `id` = " . intval($id));
    if (hesk_dbNumRows($rs) == 0) {
        return NULL;
    }
    $row = hesk_dbFetchAssoc($rs);

    $info = array(
        'saved_name' => $row['saved_name'],
        'real_name' => $row['file_name'],
        'size' => $row['size']
    );

    return $info;
}


function mfh_deleteTemporaryAttachment($id) {
    global $hesk_settings;

    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "temp_attachment` WHERE `id` = ".intval($id));
}
