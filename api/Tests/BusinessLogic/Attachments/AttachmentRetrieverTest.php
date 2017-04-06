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

    /* @var $heskSettings array */
    private $heskSettings;

    protected function setUp() {
        $this->attachmentGateway = $this->createMock(AttachmentGateway::class);
        $this->fileReader = $this->createMock(FileReader::class);
        $this->heskSettings = array('attach_dir' => 'attachments');

        $this->attachmentRetriever = new AttachmentRetriever($this->attachmentGateway, $this->fileReader);
    }

    function testItGetsTheMetadataFromTheGateway() {
        //-- Arrange
        $attachmentMeta = new Attachment();
        $attachmentMeta->savedName = '5';
        $attachmentContents = 'string';
        $expectedContents = base64_encode($attachmentContents);
        $this->attachmentGateway->method('getAttachmentById')
            ->with(4, $this->heskSettings)
            ->willReturn($attachmentMeta);
        $this->fileReader->method('readFromFile')
            ->with('5', $this->heskSettings['attach_dir'])
            ->willReturn($attachmentContents);

        //-- Act
        $actualContents = $this->attachmentRetriever->getAttachmentContentsForTicket(4, $this->heskSettings);

        //-- Assert
        self::assertThat($actualContents, self::equalTo($expectedContents));
    }
}
