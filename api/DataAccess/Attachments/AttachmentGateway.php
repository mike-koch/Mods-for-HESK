<?php

namespace DataAccess\Attachments;


use BusinessLogic\Attachments\Attachment;
use BusinessLogic\Attachments\TicketAttachment;
use DataAccess\CommonDao;

class AttachmentGateway extends CommonDao {

    /**
     * @param $attachment TicketAttachment
     * @param $heskSettings array
     * @return int The inserted attachment ID
     */
    function createAttachmentForTicket($attachment, $heskSettings) {
        $this->init();

        hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($heskSettings['db_pfix']) . "attachments` 
            (`ticket_id`, `note_id`, `saved_name`, `real_name`, `size`, `type`, `download_count`)
            VALUES ('" . hesk_dbEscape($attachment->ticketTrackingId) . "', NULL, '" . hesk_dbEscape($attachment->savedName) . "',
                '" . hesk_dbEscape($attachment->displayName) . "', " . intval($attachment->fileSize) . ", '" . intval($attachment->type) . "', 0)");

        $attachmentId = hesk_dbInsertID();

        $this->close();

        return $attachmentId;
    }

    function getAttachmentById($id, $heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT * 
          FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "attachments` 
          WHERE `att_id` = " . intval($id));

        if (hesk_dbNumRows($rs) === 0) {
            return null;
        }

        $row = hesk_dbFetchAssoc($rs);

        $attachment = new Attachment();
        $attachment->id = $row['att_id'];
        $attachment->savedName = $row['saved_name'];
        $attachment->displayName = $row['real_name'];
        $attachment->downloadCount = $row['download_count'];
        $attachment->fileSize = $row['size'];

        $this->close();

        return $attachment;
    }
}