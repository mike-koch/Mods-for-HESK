<?php

function upload_temp_attachment($i, $isTicket) {
    global $hesk_settings;

    $info = hesk_uploadFile($i, $isTicket);
    hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "temp_attachment` (`file_name`,`size`, `type`, `date_uploaded`)
        VALUES ('" . hesk_dbEscape($info['saved_name']) . "','" . hesk_dbEscape($info['size']) . "','" . hesk_dbEscape($isTicket ? 1 : 0) . "', NOW())");

    return hesk_dbInsertID();
}

function delete_temp_attachment($id, $isTicket) {
    global $hesk_settings;

    $attachment_rs = hesk_dbQuery("SELECT `saved_name` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "temp_attachment` WHERE `id` = " . intval($id));
    $attachment = hesk_dbFetchAssoc($attachment_rs);

    if (hesk_removeAttachments(array($attachment), $isTicket)) {
        hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "temp_attachment` WHERE `id` = " . intval($id));
    }
}