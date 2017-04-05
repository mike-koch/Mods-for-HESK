<?php


namespace BusinessLogic\Attachments;


use DataAccess\Attachments\AttachmentGateway;
use DataAccess\Files\FileReader;
use PHPUnit\Framework\TestCase;

class AttachmentRetrieverTest extends TestCase {
    /* @var $attachmentGateway \PHPUnit_Framework_MockObject_MockObject */
    private $attachmentGateway;

    /* @var $fileReader \PHPUnit_Framework_MockObject_MockObject */
    private $fileReader;

    /* @var $attachmentRetriever AttachmentRetriever */
    private $attachmentRetriever;

    protected function setUp() {
        $this->attachmentGateway = $this->createMock(AttachmentGateway::class);
        $this->fileReader = $this->createMock(FileReader::class);

        $this->attachmentRetriever = new AttachmentRetriever($this->attachmentGateway, $this->fileReader);
    }

    function testItGetsTheAttachmentFromTheFilesystem() {

    }
}
