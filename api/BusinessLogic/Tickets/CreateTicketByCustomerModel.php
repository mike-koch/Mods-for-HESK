<?php

namespace BusinessLogic\Tickets;

class CreateTicketByCustomerModel {
    // Metadata
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

    // Message
    /**
     * @var string
     */
    public $subject;

    /**
     * @var string
     */
    public $message;

    /**
     * @var array
     */
    public $customFields;

    /**
     * @var double[]|null
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
}