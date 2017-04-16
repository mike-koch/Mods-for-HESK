<?php


namespace BusinessLogic\Tickets;


use BusinessLogic\Attachments\AttachmentHandler;
use BusinessLogic\Security\UserContext;
use BusinessLogic\Security\UserToTicketChecker;
use DataAccess\Tickets\TicketGateway;
use PHPUnit\Framework\TestCase;

class TicketDeleterTest extends TestCase {
    /* @var $ticketDeleter TicketDeleter */
    private $ticketDeleter;

    /* @var $ticketGateway \PHPUnit_Framework_MockObject_MockObject */
    private $ticketGateway;

    /* @var $attachmentHandler \PHPUnit_Framework_MockObject_MockObject */
    private $attachmentHandler;

    /* @var $userContext UserContext */
    private $userContext;

    /* @var $heskSettings array */
    private $heskSettings;

    /* @var $userToTicketChecker \PHPUnit_Framework_MockObject_MockObject */
    private $userToTicketChecker;

    protected function setUp() {
        $this->userToTicketChecker = $this->createMock(UserToTicketChecker::class);
        $this->ticketGateway = $this->createMock(TicketGateway::class);
        $this->attachmentHandler = $this->createMock(AttachmentHandler::class);

        $this->ticketDeleter = new TicketDeleter($this->ticketGateway, $this->userToTicketChecker, $this->attachmentHandler);
    }

    function testItThrowsAnExceptionWhenTheUserDoesNotHavePermissionToDeleteTheTicket() {
        //-- Arrange
        $this->userToTicketChecker->method('isTicketAccessibleToUser')->willReturn(false);

        //-- Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("User does not have access to ticket 1");

        //-- Act
        $this->ticketDeleter->deleteTicket(1, $this->userContext, $this->heskSettings);
    }

    function testItDeletesAllAttachmentsForTheTicket() {
        //-- Arrange
        $ticket = new Ticket();
        $attachmentOne = new Attachment();
        $attachmentOne->id = 1;
        $attachmentTwo = new Attachment();
        $attachmentTwo->id = 2;
        $attachments = array($attachmentOne, $attachmentTwo);
        $ticket->attachments = $attachments;
        $this->ticketGateway->method('getTicketById')->willReturn($ticket);
        $this->userToTicketChecker->method('isTicketAccessibleToUser')->willReturn(true);

        //-- Assert
        $this->attachmentHandler->expects($this->exactly(2))->method('deleteAttachmentFromTicket');

        //-- Act
        $this->ticketDeleter->deleteTicket(1, $this->userContext, $this->heskSettings);
    }

    function testItDeletesAllRepliesForTheTicket() {
        //-- Arrange
        
        //-- Act

        //-- Assert
    }

    function testItDeletesTheTicket() {
        //-- Arrange
        $ticket = new Ticket();
        $ticket->attachments = array();
        $ticket->id = 1;
        $this->ticketGateway->method('getTicketById')->willReturn($ticket);
        $this->userToTicketChecker->method('isTicketAccessibleToUser')->willReturn(true);

        //-- Assert
        $this->ticketGateway->expects($this->once())->method('deleteTicket')->with(1, $this->heskSettings);

        //-- Act
        $this->ticketDeleter->deleteTicket(1, $this->userContext, $this->heskSettings);
    }
}
