<?php

namespace BusinessLogic\Tickets;


use DataAccess\Tickets\TicketGateway;
use PHPUnit\Framework\TestCase;

class TicketValidatorsTest extends TestCase {
    /**
     * @var $ticketGateway \PHPUnit_Framework_MockObject_MockObject
     */
    private $ticketGateway;

    /**
     * @var $ticketValidator TicketValidators
     */
    private $ticketValidator;

    protected function setUp():void {
        $this->ticketGateway = $this->createMock(TicketGateway::clazz());
        $this->ticketValidator = new TicketValidators($this->ticketGateway);
    }

    function testItReturnsTrueWhenTheUserIsMaxedOutOnOpenTickets() {
        //-- Arrange
        $tickets = [new Ticket(), new Ticket(), new Ticket()];
        $this->ticketGateway->method('getTicketsByEmail')
                        ->with('my@email.com')
                        ->willReturn($tickets);
        $heskSettings = array(
            'max_open' => 3
        );

        //-- Act
        $result = $this->ticketValidator->isCustomerAtMaxTickets('my@email.com', $heskSettings);

        //-- Assert
        $this->assertThat($result, $this->isTrue(), str_replace('test','',__FUNCTION__));
    }

    function testItReturnsFalseWhenTheUserIsNotMaxedOutOnOpenTickets() {
        //-- Arrange
        $tickets = [new Ticket(), new Ticket(), new Ticket()];
        $this->ticketGateway->method('getTicketsByEmail')
            ->with('my@email.com')
            ->willReturn($tickets);
        $heskSettings = array(
            'max_open' => 10
        );

        //-- Act
        $result = $this->ticketValidator->isCustomerAtMaxTickets('my@email.com', $heskSettings);

        //-- Assert
        $this->assertThat($result, $this->isFalse(), str_replace('test','',__FUNCTION__));
    }

    function testItReturnsFalseWhenMaxOpenIsZero() {
        //-- Arrange
        $tickets = [new Ticket(), new Ticket(), new Ticket()];
        $this->ticketGateway->method('getTicketsByEmail')
            ->with('my@email.com')
            ->willReturn($tickets);
        $heskSettings = array(
            'max_open' => 0
        );

        //-- Act
        $result = $this->ticketValidator->isCustomerAtMaxTickets('my@email.com', $heskSettings);

        //-- Assert
        $this->assertThat($result, $this->isFalse(), str_replace('test','',__FUNCTION__));
    }
}
