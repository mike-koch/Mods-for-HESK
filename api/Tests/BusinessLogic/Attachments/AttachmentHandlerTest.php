<?php


namespace BusinessLogic\Attachments;


use BusinessLogic\Exceptions\ValidationException;
use BusinessLogic\Security\UserContext;
use BusinessLogic\Security\UserPrivilege;
use BusinessLogic\Security\UserToTicketChecker;
use BusinessLogic\Tickets\Reply;
use BusinessLogic\Tickets\Ticket;
use DataAccess\Attachments\AttachmentGateway;
use DataAccess\Files\FileDeleter;
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

    /* @var $userToTicketChecker \PHPUnit_Framework_MockObject_MockObject */
    private $userToTicketChecker;

    /* @var $fileWriter \PHPUnit_Framework_MockObject_MockObject */
    private $fileWriter;

    /* @var $fileDeleter \PHPUnit_Framework_MockObject_MockObject */
    private $fileDeleter;

    /* @var $userContext UserContext */
    private $userContext;

    /* @var $heskSettings array */
    private $heskSettings;

    protected function setUp() {
        $this->ticketGateway = $this->createMock(TicketGateway::class);
        $this->attachmentGateway = $this->createMock(AttachmentGateway::class);
        $this->fileWriter = $this->createMock(FileWriter::class);
        $this->fileDeleter = $this->createMock(FileDeleter::class);
        $this->userToTicketChecker = $this->createMock(UserToTicketChecker::class);
        $this->heskSettings = array(
            'attach_dir' => 'attachments',
            'attachments' => array(
                'allowed_types' => array('.txt'),
                'max_size' => 999
            )
        );

        $this->attachmentHandler = new AttachmentHandler($this->ticketGateway,
            $this->attachmentGateway,
            $this->fileWriter,
            $this->userToTicketChecker,
            $this->fileDeleter);
        $this->createAttachmentForTicketModel = new CreateAttachmentForTicketModel();
        $this->createAttachmentForTicketModel->attachmentContents = base64_encode('string');
        $this->createAttachmentForTicketModel->displayName = 'DisplayName.txt';
        $this->createAttachmentForTicketModel->ticketId = 1;
        $this->createAttachmentForTicketModel->type = AttachmentType::MESSAGE;
        $this->userContext = new UserContext();
    }

    function testThatValidateThrowsAnExceptionWhenTheAttachmentBodyIsNull() {
        //-- Arrange
        $this->userToTicketChecker->method('isTicketAccessibleToUser')->willReturn(true);
        $this->createAttachmentForTicketModel->attachmentContents = null;

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/CONTENTS_EMPTY/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->userContext, $this->heskSettings);
    }

    function testThatValidateThrowsAnExceptionWhenTheAttachmentBodyIsEmpty() {
        //-- Arrange
        $this->userToTicketChecker->method('isTicketAccessibleToUser')->willReturn(true);
        $this->createAttachmentForTicketModel->attachmentContents = '';

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/CONTENTS_EMPTY/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->userContext, $this->heskSettings);
    }

    function testThatValidateThrowsAnExceptionWhenTheAttachmentBodyIsInvalidBase64() {
        //-- Arrange
        $this->userToTicketChecker->method('isTicketAccessibleToUser')->willReturn(true);
        $this->createAttachmentForTicketModel->attachmentContents = 'invalid base 64';

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/CONTENTS_NOT_BASE_64/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->userContext, $this->heskSettings);
    }

    function testThatValidateThrowsAnExceptionWhenTheDisplayNameIsNull() {
        //-- Arrange
        $this->userToTicketChecker->method('isTicketAccessibleToUser')->willReturn(true);
        $this->createAttachmentForTicketModel->displayName = null;

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/DISPLAY_NAME_EMPTY/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->userContext, $this->heskSettings);
    }

    function testThatValidateThrowsAnExceptionWhenTheDisplayNameIsEmpty() {
        //-- Arrange
        $this->userToTicketChecker->method('isTicketAccessibleToUser')->willReturn(true);
        $this->createAttachmentForTicketModel->displayName = '';

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/DISPLAY_NAME_EMPTY/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->userContext, $this->heskSettings);
    }

    function testThatValidateThrowsAnExceptionWhenTheTicketIdIsNull() {
        //-- Arrange
        $this->userToTicketChecker->method('isTicketAccessibleToUser')->willReturn(true);
        $this->createAttachmentForTicketModel->ticketId = null;

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/TICKET_ID_MISSING/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->userContext, $this->heskSettings);
    }

    function testThatValidateThrowsAnExceptionWhenTheTicketIdIsANonPositiveInteger() {
        //-- Arrange
        $this->userToTicketChecker->method('isTicketAccessibleToUser')->willReturn(true);
        $this->createAttachmentForTicketModel->ticketId = 0;

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/TICKET_ID_MISSING/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->userContext, $this->heskSettings);
    }

    function testThatValidateThrowsAnExceptionWhenTheFileExtensionIsNotPermitted() {
        //-- Arrange
        $this->userToTicketChecker->method('isTicketAccessibleToUser')->willReturn(true);
        $this->heskSettings['attachments']['allowed_types'] = array('.gif');
        $this->createAttachmentForTicketModel->ticketId = 0;

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/EXTENSION_NOT_PERMITTED/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->userContext, $this->heskSettings);
    }

    function testThatValidateThrowsAnExceptionWhenTheFileSizeIsLargerThanMaxPermitted() {
        //-- Arrange
        $this->userToTicketChecker->method('isTicketAccessibleToUser')->willReturn(true);
        $this->createAttachmentForTicketModel->attachmentContents = base64_encode("msg");
        $this->heskSettings['attachments']['max_size'] = 1;

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/FILE_SIZE_TOO_LARGE/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->userContext, $this->heskSettings);
    }

    function testItSavesATicketWithTheProperProperties() {
        //-- Arrange
        $this->userToTicketChecker->method('isTicketAccessibleToUser')->willReturn(true);
        $this->createAttachmentForTicketModel->ticketId = 1;
        $ticket = new Ticket();
        $ticket->trackingId = 'ABC-DEF-1234';
        $this->ticketGateway->method('getTicketById')->with(1, $this->anything())->willReturn($ticket);

        $ticketAttachment = new TicketAttachment();
        $ticketAttachment->displayName = $this->createAttachmentForTicketModel->displayName;
        $ticketAttachment->ticketTrackingId = $ticket->trackingId;
        $ticketAttachment->type = 0;
        $ticketAttachment->downloadCount = 0;
        $ticketAttachment->id = 50;

        $this->attachmentGateway->method('createAttachmentForTicket')->willReturn(50);


        //-- Act
        $actual = $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->userContext, $this->heskSettings);

        //-- Assert
        self::assertThat($actual->id, self::equalTo(50));
        self::assertThat($actual->downloadCount, self::equalTo(0));
        self::assertThat($actual->type, self::equalTo(AttachmentType::MESSAGE));
        self::assertThat($actual->ticketTrackingId, self::equalTo($ticket->trackingId));
        self::assertThat($actual->displayName, self::equalTo($this->createAttachmentForTicketModel->displayName));
    }

    function testItSavesTheFileToTheFileSystem() {
        //-- Arrange
        $this->userToTicketChecker->method('isTicketAccessibleToUser')->willReturn(true);
        $this->createAttachmentForTicketModel->ticketId = 1;
        $ticket = new Ticket();
        $ticket->trackingId = 'ABC-DEF-1234';
        $this->ticketGateway->method('getTicketById')->with(1, $this->anything())->willReturn($ticket);

        $ticketAttachment = new TicketAttachment();
        $ticketAttachment->displayName = $this->createAttachmentForTicketModel->displayName;
        $ticketAttachment->ticketTrackingId = $ticket->trackingId;
        $ticketAttachment->type = AttachmentType::MESSAGE;
        $ticketAttachment->downloadCount = 0;
        $ticketAttachment->id = 50;

        $this->fileWriter->method('writeToFile')->willReturn(1024);
        $this->attachmentGateway->method('createAttachmentForTicket')->willReturn(50);


        //-- Act
        $actual = $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel, $this->userContext, $this->heskSettings);

        //-- Assert
        self::assertThat($actual->fileSize, self::equalTo(1024));
    }

    //-- TODO Test UserToTicketChecker

    function testDeleteThrowsAnExceptionWhenTheUserDoesNotHaveAccessToTheTicket() {
        //-- Arrange
        $ticketId = 1;
        $ticket = new Ticket();
        $this->ticketGateway->method('getTicketById')
            ->with($ticketId, $this->heskSettings)->willReturn($ticket);
        $this->userToTicketChecker->method('isTicketAccessibleToUser')
            ->with($this->userContext, $ticket, $this->heskSettings, array(UserPrivilege::CAN_EDIT_TICKETS))
            ->willReturn(false);

        //-- Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("User does not have access to ticket {$ticketId} being created / edited!");

        //-- Act
        $this->attachmentHandler->deleteAttachmentFromTicket($ticketId, 1, $this->userContext, $this->heskSettings);
    }

    function testDeleteActuallyDeletesTheFile() {
        //-- Arrange
        $ticketId = 1;
        $ticket = new Ticket();
        $attachment = new Attachment();
        $attachment->id = 5;
        $attachment->savedName = 'foobar.txt';
        $this->heskSettings['attach_dir'] = 'attach-dir';
        $ticket->attachments = array($attachment);
        $ticket->replies = array();
        $this->ticketGateway->method('getTicketById')->willReturn($ticket);
        $this->userToTicketChecker->method('isTicketAccessibleToUser')->willReturn(true);

        //-- Assert
        $this->fileDeleter->expects($this->once())->method('deleteFile')->with('foobar.txt', 'attach-dir');

        //-- Act
        $this->attachmentHandler->deleteAttachmentFromTicket($ticketId, 5, $this->userContext, $this->heskSettings);
    }

    function testDeleteUpdatesTheTicketItselfAndSavesIt() {
        //-- Arrange
        $ticketId = 1;
        $ticket = new Ticket();
        $ticket->replies = array();
        $attachment = new Attachment();
        $attachment->id = 5;
        $attachment->savedName = 'foobar.txt';
        $this->heskSettings['attach_dir'] = 'attach-dir';
        $ticket->attachments = array($attachment);
        $this->ticketGateway->method('getTicketById')->willReturn($ticket);
        $this->userToTicketChecker->method('isTicketAccessibleToUser')->willReturn(true);

        //-- Assert
        $this->ticketGateway->expects($this->once())->method('updateAttachmentsForTicket');

        //-- Act
        $this->attachmentHandler->deleteAttachmentFromTicket($ticketId, 5, $this->userContext, $this->heskSettings);
    }

    function testDeleteHandlesReplies() {
        //-- Arrange
        $ticketId = 1;
        $ticket = new Ticket();
        $reply = new Reply();
        $reply->id = 10;
        $attachment = new Attachment();
        $attachment->id = 5;
        $attachment->savedName = 'foobar.txt';
        $this->heskSettings['attach_dir'] = 'attach-dir';
        $reply->attachments = array($attachment);
        $ticket->replies = array(10 => $reply);
        $this->ticketGateway->method('getTicketById')->willReturn($ticket);
        $this->userToTicketChecker->method('isTicketAccessibleToUser')->willReturn(true);

        //-- Assert
        $this->ticketGateway->expects($this->once())->method('updateAttachmentsForReply');

        //-- Act
        $this->attachmentHandler->deleteAttachmentFromTicket($ticketId, 5, $this->userContext, $this->heskSettings);
    }
}
