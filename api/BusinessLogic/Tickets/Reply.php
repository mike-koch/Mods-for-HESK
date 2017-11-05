<?php
/**
 * Created by PhpStorm.
 * User: mkoch
 * Date: 2/28/2017
 * Time: 9:17 PM
 */

namespace BusinessLogic\Tickets;


class Reply extends \BaseClass {
    /**
     * @var $id int
     */
    public $id;

    /**
     * @var $ticketId int
     */
    public $ticketId;

    /**
     * @var $replierName string
     */
    public $replierName;

    /**
     * @var $message string
     */
    public $message;

    /**
     * @var $dateCreated string
     */
    public $dateCreated;

    /**
     * @var $attachments Attachment[]
     */
    public $attachments;

    /**
     * @var $staffId int|null
     */
    public $staffId;

    /**
     * @var $rating int|null
     */
    public $rating;

    /**
     * @var $isRead bool
     */
    public $isRead;

    /**
     * @var $usesHtml bool
     */
    public $usesHtml;
}