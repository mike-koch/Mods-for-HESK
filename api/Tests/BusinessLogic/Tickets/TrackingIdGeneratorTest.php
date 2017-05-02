<?php

namespace BusinessLogic\Tickets;


use BusinessLogic\Tickets\Exceptions\UnableToGenerateTrackingIdException;
use DataAccess\Tickets\TicketGateway;
use PHPUnit\Framework\TestCase;

class TrackingIdGeneratorTest extends TestCase {
    /**
     * @var $ticketGateway \PHPUnit_Framework_MockObject_MockObject
     */
    private $ticketGateway;

    /**
     * @var $trackingIdGenerator TrackingIdGenerator
     */
    private $trackingIdGenerator;

    function setUp() {
        $this->ticketGateway = $this->createMock(TicketGateway::class);

        $this->trackingIdGenerator = new TrackingIdGenerator($this->ticketGateway);
    }

    function testItReturnsTrackingIdInTheProperFormat() {
        //-- Arrange
        $this->ticketGateway->method('getTicketByTrackingId')
                    ->willReturn(null);
        $acceptableCharacters = '[AEUYBDGHJLMNPQRSTVWXZ123456789]';
        $format = "/^{$acceptableCharacters}{3}-{$acceptableCharacters}{3}-{$acceptableCharacters}{4}$/";

        //-- Act
        $trackingId = $this->trackingIdGenerator->generateTrackingId(array());

        //-- Assert
        $this->assertThat($trackingId, $this->matchesRegularExpression($format));
    }

    function testItThrowsAnExceptionWhenItWasUnableToGenerateAValidTrackingId() {
        //-- Arrange
        $exceptionThrown = false;
        $this->ticketGateway->method('getTicketByTrackingId')
                    ->willReturn(new Ticket());

        //-- Act
        try {
            $this->trackingIdGenerator->generateTrackingId(array());
        } catch (UnableToGenerateTrackingIdException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->isTrue());
    }

    //-- Trying to test the database logic is tricky, so no tests here.
}
