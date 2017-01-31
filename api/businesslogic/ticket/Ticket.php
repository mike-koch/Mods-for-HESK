<?php

namespace BusinessLogic\Tickets;


class Ticket {
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
     * @var string
     */
    public $email;

    /**
     * @var int
     */
    public $category;

    /**
     * @var int
     */
    public $priority;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var string
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
     * @var string|null
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
     * @var int (convert to enum)
     */
    public $openedByUserId;

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
     * @var int
     */
    public $ownerId;

    /**
     * @var string
     */
    public $timeWorked;

    /**
     * @var int (convert to enum)
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
     * @var array|null (TODO clarify this later)
     */
    public $attachments;

    /**
     * @var int[]|null
     */
    public $mergedTicketIds;

    /**
     * @var string
     */
    public $auditTrailHtml;

    /**
     * @var array (TODO clarify this later)
     */
    public $customFields;

    /**
     * @var int[]
     */
    public $linkedTicketIds;

    /**
     * @var float[2]|null
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
     * @var int[2]|null
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
}