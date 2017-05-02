<?php

namespace DataAccess\Tickets;


use BusinessLogic\Attachments\AttachmentType;
use BusinessLogic\Tickets\Attachment;
use BusinessLogic\Tickets\Ticket;
use BusinessLogic\Tickets\TicketGatewayGeneratedFields;
use DataAccess\CommonDao;

class TicketGateway extends CommonDao {
    /**
     * @param $id int
     * @param $heskSettings array
     * @return Ticket|null
     */
    function getTicketById($id, $heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets` WHERE `id` = " . intval($id));

        if (hesk_dbNumRows($rs) === 0) {
            return null;
        }

        $row = hesk_dbFetchAssoc($rs);
        $linkedTicketsRs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets` WHERE `parent` = " . intval($id));

        $repliesRs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "replies` WHERE `replyto` = " . intval($id) . " ORDER BY `id` ASC");

        $ticket = Ticket::fromDatabaseRow($row, $linkedTicketsRs, $repliesRs, $heskSettings);

        $this->close();

        return $ticket;
    }

    /**
     * @param $emailAddress string
     * @param $heskSettings array
     * @return array|null
     */
    function getTicketsByEmail($emailAddress, $heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets` 
            WHERE `email` = '" . hesk_dbEscape($emailAddress) . "'");

        if (hesk_dbNumRows($rs) === 0) {
            return null;
        }

        $tickets = array();

        while ($row = hesk_dbFetchAssoc($rs)) {
            $linkedTicketsRs =
                hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets` WHERE `parent` = " . intval($row['id']));
            $repliesRs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "replies` WHERE `replyto` = " . intval($row['id']) . " ORDER BY `id` ASC");

            $tickets[] = Ticket::fromDatabaseRow($row, $linkedTicketsRs, $repliesRs, $heskSettings);
        }

        $this->close();

        return $tickets;
    }

    /**
     * @param $trackingId string
     * @param $heskSettings array
     * @return bool
     */
    function doesTicketExist($trackingId, $heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT 1 FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets` 
            WHERE `trackid` = '" . hesk_dbEscape($trackingId) . "'");

        $ticketExists = hesk_dbNumRows($rs) > 0;

        $this->close();

        return $ticketExists;
    }

    /**
     * @param $trackingId string
     * @param $heskSettings array
     * @return Ticket|null
     */
    function getTicketByTrackingId($trackingId, $heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets` WHERE `trackid` = " . intval($trackingId));
        if (hesk_dbNumRows($rs) === 0) {
            return null;
        }

        $row = hesk_dbFetchAssoc($rs);
        $linkedTicketsRs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets` WHERE `parent` = " . intval($trackingId));
        $repliesRs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "replies` WHERE `replyto` = " . intval($row['id']) . " ORDER BY `id` ASC");

        $ticket = Ticket::fromDatabaseRow($row, $linkedTicketsRs, $repliesRs, $heskSettings);

        $this->close();

        return $ticket;
    }

    /**
     * @param $trackingId string
     * @param $heskSettings array
     * @return Ticket|null
     */
    function getTicketByMergedTrackingId($trackingId, $heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT `trackid` FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets` WHERE `merged` LIKE '%#" . hesk_dbEscape($trackingId) . "#%'");
        if (hesk_dbNumRows($rs) === 0) {
            return null;
        }
        $row = hesk_dbFetchAssoc($rs);
        $actualTrackingId = $row['trackid'];

        $this->close();

        return $this->getTicketByTrackingId($actualTrackingId, $heskSettings);
    }

    /**
     * @param $ticket Ticket
     * @param $isEmailVerified
     * @param $heskSettings
     * @return TicketGatewayGeneratedFields
     */
    function createTicket($ticket, $isEmailVerified, $heskSettings) {
        $this->init();

        $dueDate = $ticket->dueDate ? "'{$ticket->dueDate}'" : "NULL";
        // Prepare SQL for custom fields
        $customWhere = '';
        $customWhat  = '';

        for ($i=1; $i<=50; $i++)
        {
            $customWhere .= ", `custom{$i}`";
            $customWhat  .= ", '" . (isset($ticket->customFields[$i]) ? hesk_dbEscape($ticket->customFields[$i]) : '') . "'";
        }

        $suggestedArticles = 'NULL';
        if ($ticket->suggestedArticles !== null && !empty($ticket->suggestedArticles)) {
            $suggestedArticles = "'" .implode(',', $ticket->suggestedArticles) . "'";
        }

        $latitude = $ticket->location !== null
                    && isset($ticket->location[0])
                    && $ticket->location[0] !== null ? $ticket->location[0] : 'E-0';
        $longitude = $ticket->location !== null
                    && isset($ticket->location[1])
                    && $ticket->location[1] !== null ? $ticket->location[1] : 'E-0';
        $userAgent = $ticket->userAgent !== null ? $ticket->userAgent : '';
        $screenResolutionWidth = $ticket->screenResolution !== null
                    && isset($ticket->screenResolution[0])
                    && $ticket->screenResolution[0] !== null ? intval($ticket->screenResolution[0]) : 'NULL';
        $screenResolutionHeight = $ticket->screenResolution !== null
                    && isset($ticket->screenResolution[1])
                    && $ticket->screenResolution[1] !== null ? intval($ticket->screenResolution[1]) : 'NULL';

        $ipAddress = $ticket->ipAddress !== null
                    && $ticket->ipAddress !== '' ? $ticket->ipAddress : '';

        $tableName = $isEmailVerified ? 'tickets' : 'stage_tickets';

        $sql = "INSERT INTO `" . hesk_dbEscape($heskSettings['db_pfix']) . $tableName ."`
        (
            `trackid`,
            `name`,
            `email`,
            `category`,
            `priority`,
            `subject`,
            `message`,
            `dt`,
            `lastchange`,
            `articles`,
            `ip`,
            `language`,
            `openedby`,
            `owner`,
            `attachments`,
            `merged`,
            `status`,
            `latitude`,
            `longitude`,
            `html`,
            `user_agent`,
            `screen_resolution_height`,
            `screen_resolution_width`,
            `due_date`,
            `history`
            {$customWhere}
        )
        VALUES
        (
            '" . hesk_dbEscape($ticket->trackingId) . "',
            '" . hesk_dbEscape($ticket->name) . "',
            '" . hesk_dbEscape($ticket->email) . "',
            '" . intval($ticket->categoryId) . "',
            '" . intval($ticket->priorityId) . "',
            '" . hesk_dbEscape($ticket->subject) . "',
            '" . hesk_dbEscape($ticket->message) . "',
            NOW(),
            NOW(),
            " . $suggestedArticles . ",
            '" . hesk_dbEscape($ipAddress) . "',
            '" . hesk_dbEscape($ticket->language) . "',
            '" . intval($ticket->openedBy) . "',
            '" . intval($ticket->ownerId) . "',
            '" . hesk_dbEscape($ticket->getAttachmentsForDatabase()) . "',
            '',
            " . intval($ticket->statusId) . ",
            '" . hesk_dbEscape($latitude) . "',
            '" . hesk_dbEscape($longitude) . "',
            '" . hesk_dbEscape($ticket->usesHtml) . "',
            '" . hesk_dbEscape($userAgent) . "',
            " . hesk_dbEscape($screenResolutionHeight) . ",
            " . hesk_dbEscape($screenResolutionWidth) . ",
            {$dueDate},
            '" . hesk_dbEscape($ticket->auditTrailHtml) . "'
            {$customWhat}
        )
        ";

        hesk_dbQuery($sql);
        $id = hesk_dbInsertID();

        $rs = hesk_dbQuery('SELECT `dt`, `lastchange` FROM `' . hesk_dbEscape($heskSettings['db_pfix']) . $tableName .'` WHERE `id` = ' . intval($id));
        $row = hesk_dbFetchAssoc($rs);

        $generatedFields = new TicketGatewayGeneratedFields();
        $generatedFields->id = $id;
        $generatedFields->dateCreated = $row['dt'];
        $generatedFields->dateModified = $row['lastchange'];

        $this->close();

        return $generatedFields;
    }

    /**
     * @param $ticketId int
     * @param $attachments Attachment[]
     * @param $heskSettings array
     *
     * Crappy logic that should just be pulled from the attachments table, but using for backwards compatibility
     */
    function updateAttachmentsForTicket($ticketId, $attachments, $heskSettings) {
        $this->init();
        $this->updateAttachmentsFor($ticketId, $attachments, AttachmentType::MESSAGE, $heskSettings);
        $this->close();
    }

    private function updateAttachmentsFor($id, $attachments, $attachmentType, $heskSettings) {
        $attachmentStrings = array();
        foreach ($attachments as $attachment) {
            $attachmentStrings[] = "{$attachment->id}#{$attachment->fileName}#{$attachment->savedName}";
        }
        $attachmentStringToSave = implode(',', $attachmentStrings);

        $tableName = $attachmentType == AttachmentType::MESSAGE ? 'tickets' : 'replies';

        hesk_dbQuery("UPDATE `" . hesk_dbEscape($heskSettings['db_pfix']) . $tableName . "` 
            SET `attachments` = '" . hesk_dbEscape($attachmentStringToSave) . "' 
            WHERE `id` = " . intval($id));
    }

    /**
     * @param $replyId int
     * @param $attachments Attachment[]
     * @param $heskSettings array
     *
     * Crappy logic that should just be pulled from the attachments table, but using for backwards compatibility
     */
    function updateAttachmentsForReply($replyId, $attachments, $heskSettings) {
        $this->init();
        $this->updateAttachmentsFor($replyId, $attachments, AttachmentType::REPLY, $heskSettings);
        $this->close();
    }

    function deleteRepliesForTicket($ticketId, $heskSettings) {
        $this->init();

        hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "replies` WHERE `replyto` = " . intval($ticketId));

        $this->close();
    }

    function deleteReplyDraftsForTicket($ticketId, $heskSettings) {
        $this->init();

        hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "reply_drafts` WHERE `ticket`=" . intval($ticketId));

        $this->close();
    }

    function deleteNotesForTicket($ticketId, $heskSettings) {
        $this->init();

        hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "notes` WHERE `ticket`='" . intval($ticketId) . "'");

        $this->close();
    }

    /**
     * @param $ticketId int
     * @param $heskSettings array
     */
    function deleteTicket($ticketId, $heskSettings) {
        $this->init();

        hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets` WHERE `id` = " . intval($ticketId));

        $this->close();
    }

    /**
     * @param $ticket Ticket
     * @param $heskSettings array
     */
    function updateBasicTicketInfo($ticket, $heskSettings) {
        $this->init();

        // Escaped vars
        $subject = hesk_dbEscape($ticket->subject);
        $message = hesk_dbEscape($ticket->message);
        $language = hesk_dbEscape($ticket->language);
        $name = hesk_dbEscape($ticket->name);
        $email = hesk_dbEscape($ticket->email);

        // Prepare SQL for custom fields
        $customSql = '';

        for ($i=1; $i<=50; $i++)
        {
            $customSql .= ", `custom{$i}` = '" . (isset($ticket->customFields[$i]) ? hesk_dbEscape($ticket->customFields[$i]) : '') . "'";
        }

        hesk_dbQuery("UPDATE `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets` 
            SET `subject` = '{$subject}',
                `message` = '{$message}',
                `language` = '{$language}',
                `name` = '{$name}',
                `email` = '{$email}',
                `html` = " . ($ticket->usesHtml ? 1 : 0) . ",
                {$customSql}
            WHERE `id` = " . intval($ticket->id));

        $this->close();
    }
}