<?php

namespace BusinessLogic\Tickets;


use DataAccess\Tickets\TicketGateway;
use PHPUnit\Framework\TestCase;

class TicketRetrieverTest extends TestCase {
    /* @var $ticketRetriever TicketRetriever */
    private $ticketRetriever;

    /* @var $ticketGateway \PHPUnit_Framework_MockObject_MockObject */
    private $ticketGateway;

    /* @var $heskSettings array */
    private $heskSettings;

    protected function setUp() {
        $this->ticketGateway = $this->createMock(TicketGateway::class);
        $this->heskSettings = array('email_view_ticket' => 0);

        $this->ticketRetriever = new TicketRetriever($this->ticketGateway);
    }

    function testItGetsTheTicketByTrackingId() {
        //-- Arrange
        $ticket = new Ticket();
        $trackingId = '12345';
        $this->ticketGateway->method('getTicketByTrackingId')->with($trackingId, $this->heskSettings)->willReturn($ticket);

        //-- Act
        $actual = $this->ticketRetriever->getTicketByTrackingIdAndEmail($trackingId, null, $this->heskSettings);

        //-- Assert
        self::assertThat($actual, self::equalTo($ticket));
    }

    function testItGetsTheParentTicketIfTheUserEntersInAMergedTicketId() {
        //-- Arrange
        $ticket = new Ticket();
        $trackingId = '12345';
        $this->ticketGateway->method('getTicketByTrackingId')->willReturn(null);
        $this->ticketGateway->method('getTicketByMergedTrackingId')->with($trackingId, $this->heskSettings)->willReturn($ticket);

        //-- Act
        $actual = $this->ticketRetriever->getTicketByTrackingIdAndEmail($trackingId, null, $this->heskSettings);

        //-- Assert
        self::assertThat($actual, self::equalTo($ticket));
    }

    function testItChecksTheTicketsEmailIfThePageRequiresIt() {
        //-- Arrange
        $ticket = new Ticket();
        $email = 'email@example.com';
        $ticket->email = array('email2@example.com;email3@example.com,email4@example.com');
        $trackingId = '12345';
        $this->heskSettings['email_view_ticket'] = 1;
        $this->ticketGateway->method('getTicketByTrackingId')->with($trackingId, $this->heskSettings)->willReturn($ticket);

        //-- Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Email 'email@example.com' entered in for ticket '12345' does not match!");

        //-- Act
        $this->ticketRetriever->getTicketByTrackingIdAndEmail($trackingId, $email, $this->heskSettings);
    }

    function testItCanHandleTicketsWithMultipleEmails() {
        //-- Arrange
        $ticket = new Ticket();
        $email = 'email2@example.com';
        $ticket->email = array('email2@example.com','email3@example.com','email4@example.com');
        $trackingId = '12345';
        $this->heskSettings['email_view_ticket'] = 1;
        $this->ticketGateway->method('getTicketByTrackingId')->with($trackingId, $this->heskSettings)->willReturn($ticket);

        //-- Act
        $actual = $this->ticketRetriever->getTicketByTrackingIdAndEmail($trackingId, $email, $this->heskSettings);

        //-- Assert
        self::assertThat($actual, self::equalTo($ticket));
    }

    //-- TODO Validation tests
}
