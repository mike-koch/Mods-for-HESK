<?php

namespace BusinessLogic\Tickets;


class Ticket {
    static function fromDatabaseRow($row, $linkedTicketsRs, $repliesRs, $heskSettings) {
        $ticket = new Ticket();
        $ticket->id = intval($row['id']);
        $ticket->trackingId = $row['trackid'];
        $ticket->name = $row['name'];
        $ticket->email = $row['email'];
        $ticket->categoryId = intval($row['category']);
        $ticket->priorityId = intval($row['priority']);
        $ticket->subject = $row['subject'];
        $ticket->message = $row['message'];
        $ticket->dateCreated = $row['dt'];
        $ticket->lastChanged = $row['lastchange'];
        $ticket->firstReplyDate = $row['firstreply'];
        $ticket->closedDate = $row['closedat'];

        if (trim($row['articles']) !== '') {
            $ticket->suggestedArticles = explode(',', $row['articles']);
        }

        $ticket->ipAddress = $row['ip'];
        $ticket->language = $row['language'];
        $ticket->statusId = intval($row['status']);
        $ticket->openedBy = intval($row['openedby']);
        $ticket->firstReplyByUserId = $row['firstreplyby'] === null ? null : intval($row['firstreplyby']);
        $ticket->closedByUserId = $row['closedby'] === null ? null : intval($row['closedby']);
        $ticket->numberOfReplies = intval($row['replies']);
        $ticket->numberOfStaffReplies = intval($row['staffreplies']);
        $ticket->ownerId = intval($row['owner']);
        $ticket->timeWorked = $row['time_worked'];
        $ticket->lastReplyBy = intval($row['lastreplier']);
        $ticket->lastReplier = $row['replierid'] === null ? null : intval($row['replierid']);
        $ticket->archived = intval($row['archive']) === 1;
        $ticket->locked = intval($row['locked']) === 1;

        if (trim($row['attachments']) !== '') {
            $attachments = explode(',', $row['attachments']);
            $attachmentArray = array();
            foreach ($attachments as $attachment) {
                if (trim($attachment) === '') {
                    continue;
                }

                $attachmentRow = explode('#', $attachment);
                $attachmentModel = new Attachment();

                $attachmentModel->id = $attachmentRow[0];
                $attachmentModel->fileName = $attachmentRow[1];
                $attachmentModel->savedName = $attachmentRow[2];

                $attachmentArray[] = $attachmentModel;
            }
            $ticket->attachments = $attachmentArray;
        }

        if (trim($row['merged']) !== '') {
            $ticket->mergedTicketIds = explode(',', $row['merged']);
        }

        $ticket->auditTrailHtml = $row['history'];

        $ticket->customFields = array();
        foreach ($heskSettings['custom_fields'] as $key => $value) {
            if ($value['use'] && hesk_is_custom_field_in_category($key, intval($ticket->categoryId))) {
                $ticket->customFields[str_replace('custom', '', $key)] = $row[$key];
            }
        }

        $ticket->linkedTicketIds = array();
        while ($linkedTicketsRow = hesk_dbFetchAssoc($linkedTicketsRs)) {
            $ticket->linkedTicketIds[] = $linkedTicketsRow['id'];
        }

        if ($row['latitude'] !== '' && $row['longitude'] !== '') {
            $ticket->location = array();
            $ticket->location[0] = $row['latitude'];
            $ticket->location[1] = $row['longitude'];
        }

        $ticket->usesHtml = intval($row['html']) === 1;

        if ($row['user_agent'] !== null && trim($row['user_agent']) !== '') {
            $ticket->userAgent = $row['user_agent'];
        }

        if ($row['screen_resolution_height'] !== null && $row['screen_resolution_width'] !== null){
            $ticket->screenResolution = array();
            $ticket->screenResolution[0] = $row['screen_resolution_width'];
            $ticket->screenResolution[1] = $row['screen_resolution_height'];
        }

        $ticket->dueDate = $row['due_date'];
        $ticket->dueDateOverdueEmailSent = $row['overdue_email_sent'] !== null && intval($row['overdue_email_sent']) === 1;

        $replies = array();
        while ($replyRow = hesk_dbFetchAssoc($repliesRs)) {
            $reply = new Reply();
            $reply->id = $replyRow['id'];
            $reply->ticketId = $replyRow['replyto'];
            $reply->replierName = $replyRow['name'];
            $reply->message = $replyRow['message'];
            $reply->dateCreated = $replyRow['dt'];

            if (trim($replyRow['attachments']) !== '') {
                $attachments = explode(',', $replyRow['attachments']);
                $attachmentArray = array();
                foreach ($attachments as $attachment) {
                    $attachmentRow = explode('#', $attachment);
                    $attachmentModel = new Attachment();

                    $attachmentModel->id = $attachmentRow[0];
                    $attachmentModel->fileName = $attachmentRow[1];
                    $attachmentModel->savedName = $attachmentRow[2];

                    $attachmentArray[] = $attachmentModel;
                }
                $reply->attachments = $attachmentArray;
            }

            $reply->staffId = $replyRow['staffid'] > 0 ? $replyRow['staffid'] : null;
            $reply->rating = $replyRow['rating'];
            $reply->isRead = $replyRow['read'];
            $reply->usesHtml = $replyRow['html'];

            $replies[$reply->id] = $reply;
        }
        $ticket->replies = $replies;

        return $ticket;
    }

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $trackingId;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string|null
     */
    public $email;

    /**
     * @var int
     */
    public $categoryId;

    /**
     * @var int
     */
    public $priorityId;

    /**
     * @var string|null
     */
    public $subject;

    /**
     * @var string|null
     */
    public $message;

    /**
     * @var string
     */
    public $dateCreated;

    /**
     * @var string
     */
    public $lastChanged;

    /**
     * @var string|null
     */
    public $firstReplyDate;

    /**
     * @var string|null
     */
    public $closedDate;

    /**
     * @var string[]|null
     */
    public $suggestedArticles;

    /**
     * @var string
     */
    public $ipAddress;

    /**
     * @var string|null
     */
    public $language;

    /**
     * @var int
     */
    public $statusId;

    /**
     * @var int
     */
    public $openedBy;

    /**
     * @var int|null
     */
    public $firstReplyByUserId;

    /**
     * @var int|null
     */
    public $closedByUserId;

    /**
     * @var int
     */
    public $numberOfReplies;

    /**
     * @var int
     */
    public $numberOfStaffReplies;

    /**
     * @var int|null
     */
    public $ownerId;

    /**
     * @var string
     */
    public $timeWorked;

    /**
     * @var int
     */
    public $lastReplyBy;

    /**
     * @var int|null
     */
    public $lastReplier;

    /**
     * @var bool
     */
    public $archived;

    /**
     * @var bool
     */
    public $locked;

    /**
     * @var Attachment[]|null
     */
    public $attachments;

    function getAttachmentsForDatabase() {
        $attachmentArray = array();

        if ($this->attachments !== null) {
            foreach ($this->attachments as $attachment) {
                $attachmentArray[] = $attachment->id . '#' . $attachment->fileName . '#' . $attachment->savedName;
            }
        }

        return implode(',', $attachmentArray);
    }

    /**
     * @var int[]|null
     */
    public $mergedTicketIds;

    /**
     * @var string
     */
    public $auditTrailHtml;

    /**
     * @var string[]
     */
    public $customFields;

    /**
     * @var int[]
     */
    public $linkedTicketIds;

    /**
     * @var float[]|null
     */
    public $location;

    /**
     * @var bool
     */
    public $usesHtml;

    /**
     * @var string|null
     */
    public $userAgent;

    /**
     * 0 => width
     * 1 => height
     *
     * @var int[]|null
     */
    public $screenResolution;

    /**
     * @var string|null
     */
    public $dueDate;

    /**
     * @var bool|null
     */
    public $dueDateOverdueEmailSent;

    /**
     * @var Reply[]
     */
    public $replies;
}