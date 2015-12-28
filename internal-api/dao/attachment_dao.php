<?php

function upload_temp_attachment($i, $isTicket) {
    global $hesk_settings;

    $info = hesk_uploadFile($i, $isTicket);
    $type = $isTicket ? 1 : 0;

    // `type`: 0: ticket, 1: kb
    hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "temp_attachment` (`file_name`,`size`, `type`, `date_uploaded`, `saved_name`)
        VALUES ('" . hesk_dbEscape($info['real_name']) . "','" . hesk_dbEscape($info['size']) . "','" . $type . "',
            NOW(), '" . hesk_dbEscape($info['saved_name']) . "')");

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