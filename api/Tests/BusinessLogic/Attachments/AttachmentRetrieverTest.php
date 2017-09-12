<?php


namespace BusinessLogic\Attachments;


use BusinessLogic\Security\UserContext;
use BusinessLogic\Security\UserToTicketChecker;
use BusinessLogic\Tickets\Ticket;
use DataAccess\Attachments\AttachmentGateway;
use DataAccess\Files\FileReader;
use DataAccess\Tickets\TicketGateway;
use PHPUnit\Framework\TestCase;

class AttachmentRetrieverTest extends TestCase {
    /* @var $attachmentGateway \PHPUnit_Framework_MockObject_MockObject */
    private $attachmentGateway;

    /* @var $fileReader \PHPUnit_Framework_MockObject_MockObject */
    private $fileReader;

    /* @var $ticketGateway \PHPUnit_Framework_MockObject_MockObject */
    private $ticketGateway;

    /* @var $userToTicketChecker \PHPUnit_Framework_MockObject_MockObject */
    private $userToTicketChecker;

    /* @var $attachmentRetriever AttachmentRetriever */
    private $attachmentRetriever;

    /* @var $heskSettings array */
    private $heskSettings;

    protected function setUp() {
        $this->attachmentGateway = $this->createMock(AttachmentGateway::clazz());
        $this->fileReader = $this->createMock(FileReader::clazz());
        $this->ticketGateway = $this->createMock(TicketGateway::clazz());
        $this->userToTicketChecker = $this->createMock(UserToTicketChecker::clazz());
        $this->heskSettings = array('attach_dir' => 'attachments');

        $this->attachmentRetriever = new AttachmentRetriever($this->attachmentGateway, $this->fileReader,
            $this->ticketGateway, $this->userToTicketChecker);

        $this->userToTicketChecker->method('isTicketAccessibleToUser')->willReturn(true);
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
        $this->ticketGateway->method('getTicketById')
            ->willReturn(new Ticket());
        $this->userToTicketChecker->method('isTicketAccessibleToUser')
            ->willReturn(true);

        //-- Act
        $actualContents = $this->attachmentRetriever->getAttachmentContentsForTicket(0, 4, new UserContext(), $this->heskSettings);

        //-- Assert
        self::assertThat($actualContents, self::equalTo($expectedContents));
    }
}
