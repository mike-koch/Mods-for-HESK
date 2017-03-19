<?php


namespace BusinessLogic\Attachments;


use BusinessLogic\Exceptions\ValidationException;
use BusinessLogic\Tickets\Ticket;
use DataAccess\Attachments\AttachmentGateway;
use DataAccess\Files\FileWriter;
use DataAccess\Tickets\TicketGateway;
use PHPUnit\Framework\TestCase;

class AttachmentHandlerTest extends TestCase {

    /* @var $attachmentHandler AttachmentHandler */
    private $attachmentHandler;

    /* @var $createAttachmentModel CreateAttachmentForTicketModel */
    private $createAttachmentForTicketModel;

    /* @var $ticketGateway \PHPUnit_Framework_MockObject_MockObject */
    private $ticketGateway;

    /* @var $attachmentGateway \PHPUnit_Framework_MockObject_MockObject */
    private $attachmentGateway;

    /* @var $fileWriter \PHPUnit_Framework_MockObject_MockObject */
    private $fileWriter;

    /* @var $heskSettings array */
    private $heskSettings;

    protected function setUp() {
        $this->ticketGateway = $this->createMock(TicketGateway::class);
        $this->attachmentGateway = $this->createMock(AttachmentGateway::class);
        $this->fileWriter = $this->createMock(FileWriter::class);
        $this->heskSettings = array(
            'attach_dir' => 'attachments'
        );

        $this->attachmentHandler = new AttachmentHandler($this->ticketGateway, $this->attachmentGateway, $this->fileWriter);
        $this->createAttachmentForTicketModel = new CreateAttachmentForTicketModel();
        $this->createAttachmentForTicketModel->attachmentContents = base64_encode('string');
        $this->createAttachmentForTicketModel->displayName = 'DisplayName';
        $this->createAttachmentForTicketModel->ticketId = 1;
        $this->createAttachmentForTicketModel->type = AttachmentType::MESSAGE;
    }

    function testThatValidateThrowsAnExceptionWhenTheAttachmentBodyIsNull() {
        //-- Arrange
        $this->createAttachmentForTicketModel->attachmentContents = null;

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/CONTENTS_EMPTY/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->heskSettings);
    }

    function testThatValidateThrowsAnExceptionWhenTheAttachmentBodyIsEmpty() {
        //-- Arrange
        $this->createAttachmentForTicketModel->attachmentContents = '';

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/CONTENTS_EMPTY/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->heskSettings);
    }

    function testThatValidateThrowsAnExceptionWhenTheAttachmentBodyIsInvalidBase64() {
        //-- Arrange
        $this->createAttachmentForTicketModel->attachmentContents = 'invalid base 64';

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/CONTENTS_NOT_BASE_64/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->heskSettings);
    }

    function testThatValidateThrowsAnExceptionWhenTheDisplayNameIsNull() {
        //-- Arrange
        $this->createAttachmentForTicketModel->displayName = null;

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/DISPLAY_NAME_EMPTY/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->heskSettings);
    }

    function testThatValidateThrowsAnExceptionWhenTheDisplayNameIsEmpty() {
        //-- Arrange
        $this->createAttachmentForTicketModel->displayName = '';

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/DISPLAY_NAME_EMPTY/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->heskSettings);
    }

    function testThatValidateThrowsAnExceptionWhenTheTicketIdIsNull() {
        //-- Arrange
        $this->createAttachmentForTicketModel->ticketId = null;

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/TICKET_ID_MISSING/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->heskSettings);
    }

    function testThatValidateThrowsAnExceptionWhenTheTicketIdIsANonPositiveInteger() {
        //-- Arrange
        $this->createAttachmentForTicketModel->ticketId = 0;

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/TICKET_ID_MISSING/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->heskSettings);
    }

    function testThatValidateThrowsAnExceptionWhenTheAttachmentTypeIsNeitherMessageNorReply() {
        //-- Arrange
        $this->createAttachmentForTicketModel->type = 5;

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/INVALID_ATTACHMENT_TYPE/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->heskSettings);
    }

    function testItSavesATicketWithTheProperProperties() {
        //-- Arrange
        $this->createAttachmentForTicketModel->ticketId = 1;
        $ticket = new Ticket();
        $ticket->trackingId = 'ABC-DEF-1234';
        $this->ticketGateway->method('getTicketById')->with(1, $this->anything())->willReturn($ticket);

        $ticketAttachment = new TicketAttachment();
        $ticketAttachment->displayName = $this->createAttachmentForTicketModel->displayName;
        $ticketAttachment->ticketTrackingId = $ticket->trackingId;
        $ticketAttachment->type = $this->createAttachmentForTicketModel->type;
        $ticketAttachment->downloadCount = 0;
        $ticketAttachment->id = 50;

        $this->attachmentGateway->method('createAttachmentForTicket')->willReturn(50);


        //-- Act
        $actual = $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->heskSettings);

        //-- Assert
        self::assertThat($actual->id, self::equalTo(50));
        self::assertThat($actual->downloadCount, self::equalTo(0));
        self::assertThat($actual->type, self::equalTo($this->createAttachmentForTicketModel->type));
        self::assertThat($actual->ticketTrackingId, self::equalTo($ticket->trackingId));
        self::assertThat($actual->displayName, self::equalTo($this->createAttachmentForTicketModel->displayName));
    }

    function testItSavesTheFileToTheFileSystem() {
        //-- Arrange
        $this->createAttachmentForTicketModel->ticketId = 1;
        $ticket = new Ticket();
        $ticket->trackingId = 'ABC-DEF-1234';
        $this->ticketGateway->method('getTicketById')->with(1, $this->anything())->willReturn($ticket);

        $ticketAttachment = new TicketAttachment();
        $ticketAttachment->displayName = $this->createAttachmentForTicketModel->displayName;
        $ticketAttachment->ticketTrackingId = $ticket->trackingId;
        $ticketAttachment->type = $this->createAttachmentForTicketModel->type;
        $ticketAttachment->downloadCount = 0;
        $ticketAttachment->id = 50;

        $this->fileWriter->method('writeToFile')->willReturn(1024);
        $this->attachmentGateway->method('createAttachmentForTicket')->willReturn(50);


        //-- Act
        $actual = $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->heskSettings);

        //-- Assert
        self::assertThat($actual->fileSize, self::equalTo(1024));
    }
}
