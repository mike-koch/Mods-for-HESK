<?php


namespace BusinessLogic\Attachments;


use PHPUnit\Framework\TestCase;

class AttachmentHandlerTest extends TestCase {

    /* @var $attachmentHandler AttachmentHandler */
    private $attachmentHandler;

    protected function setUp() {
        $this->attachmentHandler = new AttachmentHandler();
    }
}
