<?php
/**
 * Created by PhpStorm.
 * User: mkoch
 * Date: 2/11/2017
 * Time: 4:32 PM
 */

namespace BusinessLogic\Tickets;


use DataAccess\Tickets\TicketGateway;
use PHPUnit\Framework\TestCase;

class TrackingIdGeneratorTest extends TestCase {
    /**
     * @var $ticketGateway \PHPUnit_Framework_MockObject_MockObject
     */
    private $ticketGateway;

    private $trackingIdGenerator;

    function setUp() {
        $this->ticketGateway = $this->createMock(TicketGateway::class);

        $this->trackingIdGenerator = new TrackingIdGenerator($this->ticketGateway);
    }

    function testItReturnsTrackingIdInTheProperFormat() {
        //-- Arrange
        $format = '';

        //-- Act

        //-- Assert
    }
}
