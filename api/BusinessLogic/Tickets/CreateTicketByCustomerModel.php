<?php

namespace BusinessLogic\Tickets;

class CreateTicketByCustomerModel {
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $email;

    /**
     * @var integer
     */
    public $priority;

    /**
     * @var integer
     */
    public $category;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var string
     */
    public $message;

    /**
     * @var bool
     */
    public $html;

    /**
     * @var array
     */
    public $customFields;

    /**
     * @var string[]|null The latitude/longitude pair, or relevant error code (E-#)
     */
    public $location;

    /**
     * @var int[]|null
     */
    public $suggestedKnowledgebaseArticleIds;

    /**
     * @var string|null
     */
    public $userAgent;

    /**
     * @var int[]|null
     */
    public $screenResolution;

    /**
     * @var int|null
     */
    public $ipAddress;

    /**
     * @var string
     */
    public $language;
}