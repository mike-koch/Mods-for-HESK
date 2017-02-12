<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 2/12/2017
 * Time: 12:52 AM
 */

namespace BusinessLogic\Tickets\TicketCreatorTests;


use BusinessLogic\Security\UserContext;
use BusinessLogic\Tickets\CreateTicketByCustomerModel;
use BusinessLogic\Tickets\NewTicketValidator;
use BusinessLogic\Tickets\TicketCreator;
use BusinessLogic\Tickets\TrackingIdGenerator;
use BusinessLogic\ValidationModel;
use Core\Constants\Priority;
use DataAccess\Tickets\TicketGateway;
use PHPUnit\Framework\TestCase;


class CreateTicketTest extends TestCase {
    /**
     * @var $ticketGateway \PHPUnit_Framework_MockObject_MockObject
     */
    private $ticketGateway;

    /**
     * @var $newTicketValidator \PHPUnit_Framework_MockObject_MockObject
     */
    private $newTicketValidator;

    /**
     * @var $trackingIdGenerator \PHPUnit_Framework_MockObject_MockObject
     */
    private $trackingIdGenerator;

    /**
     * @var $ticketRequest CreateTicketByCustomerModel
     */
    private $ticketRequest;

    /**
     * @var $ticketCreator TicketCreator
     */
    private $ticketCreator;

    /**
     * @var $heskSettings array
     */
    private $heskSettings;

    /**
     * @var $modsForHeskSettings array
     */
    private $modsForHeskSettings;

    /**
     * @var $userContext UserContext
     */
    private $userContext;

    protected function setUp() {
        $this->ticketGateway = $this->createMock(TicketGateway::class);
        $this->newTicketValidator = $this->createMock(NewTicketValidator::class);
        $this->trackingIdGenerator = $this->createMock(TrackingIdGenerator::class);

        $this->ticketCreator = new TicketCreator($this->newTicketValidator, $this->trackingIdGenerator, $this->ticketGateway);

        $this->ticketRequest = new CreateTicketByCustomerModel();
        $this->ticketRequest->name = 'Name';
        $this->ticketRequest->email = 'some@e.mail';
        $this->ticketRequest->category = 1;
        $this->ticketRequest->priority = Priority::HIGH;
        $this->ticketRequest->subject = 'Subject';
        $this->ticketRequest->message = 'Message';
        $this->ticketRequest->customFields = array();
        $this->heskSettings = array(
            'multi_eml' => false,
            'cust_urgency' => false,
            'require_subject' => 1,
            'require_message' => 1,
            'custom_fields' => array(),
        );
        $this->modsForHeskSettings = array();
        $this->userContext = new UserContext();

        $this->newTicketValidator->method('validateNewTicketForCustomer')->willReturn(new ValidationModel());
        $this->trackingIdGenerator->method('generateTrackingId')->willReturn('123-456-7890');
    }

    function testItSavesTheTicketToTheDatabase() {
        //-- Assert
        $this->ticketGateway->expects($this->once())->method('createTicket');

        //-- Act
        $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->modsForHeskSettings, $this->userContext);
    }

    function testItSetsTheTrackingIdOnTheTicket() {
        //-- Act
        $ticket = $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->modsForHeskSettings, $this->userContext);

        //-- Assert
        self::assertThat($ticket->trackingId, self::equalTo('123-456-7890'));
    }
}
