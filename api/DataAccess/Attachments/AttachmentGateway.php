<?php

namespace DataAccess\Attachments;


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
}