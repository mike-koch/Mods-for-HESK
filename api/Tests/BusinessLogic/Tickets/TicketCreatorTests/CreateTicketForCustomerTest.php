<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 2/12/2017
 * Time: 12:52 AM
 */

namespace BusinessLogic\Tickets\TicketCreatorTests;


use BusinessLogic\Security\UserContext;
use BusinessLogic\Tickets\Autoassigner;
use BusinessLogic\Tickets\CreateTicketByCustomerModel;
use BusinessLogic\Tickets\NewTicketValidator;
use BusinessLogic\Tickets\TicketCreator;
use BusinessLogic\Tickets\TicketGatewayGeneratedFields;
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
     * @var $autoassigner \PHPUnit_Framework_MockObject_MockObject
     */
    private $autoassigner;

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

    /**
     * @var $ticketGatewayGeneratedFields TicketGatewayGeneratedFields
     */
    private $ticketGatewayGeneratedFields;

    protected function setUp() {
        $this->ticketGateway = $this->createMock(TicketGateway::class);
        $this->newTicketValidator = $this->createMock(NewTicketValidator::class);
        $this->trackingIdGenerator = $this->createMock(TrackingIdGenerator::class);
        $this->autoassigner = $this->createMock(Autoassigner::class);

        $this->ticketCreator = new TicketCreator($this->newTicketValidator, $this->trackingIdGenerator, $this->autoassigner, $this->ticketGateway);

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
            'autoassign' => 0,
        );
        $this->modsForHeskSettings = array();
        $this->userContext = new UserContext();

        $this->newTicketValidator->method('validateNewTicketForCustomer')->willReturn(new ValidationModel());
        $this->trackingIdGenerator->method('generateTrackingId')->willReturn('123-456-7890');
        $this->autoassigner->method('getNextUserForTicket')->willReturn(1);
        $this->ticketGatewayGeneratedFields = new TicketGatewayGeneratedFields();
        $this->ticketGateway->method('createTicket')->willReturn($this->ticketGatewayGeneratedFields);
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

    function testItSetsTheNextUserForAutoassign() {
        //-- Arrange
        $this->heskSettings['autoassign'] = 1;

        //-- Act
        $ticket = $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->modsForHeskSettings, $this->userContext);

        //-- Assert
        self::assertThat($ticket->ownerId, self::equalTo(1));
    }

    function testItDoesntCallTheAutoassignerWhenDisabledInHesk() {
        //-- Act
        $ticket = $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->modsForHeskSettings, $this->userContext);

        //-- Assert
        self::assertThat($ticket->ownerId, self::equalTo(null));
    }

    function testItTransformsTheBasicProperties() {
        //-- Arrange
        $this->ticketRequest->name = 'Name';
        $this->ticketRequest->email = 'some@email.test';
        $this->ticketRequest->priority = Priority::MEDIUM;
        $this->ticketRequest->category = 1;
        $this->ticketRequest->subject = 'Subject';
        $this->ticketRequest->message = 'Message';
        $this->ticketRequest->html = false;
        $this->ticketRequest->customFields = array(
            1 => 'something'
        );
        $this->ticketRequest->location = ['10.157', '-10.177'];
        $this->ticketRequest->suggestedKnowledgebaseArticleIds = [1, 2, 3];
        $this->ticketRequest->userAgent = 'UserAgent';
        $this->ticketRequest->screenResolution = [1400, 900];
        $this->ticketRequest->ipAddress = ip2long('127.0.0.1');
        $this->ticketRequest->language = 'English';

        //-- Act
        $ticket = $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->modsForHeskSettings, $this->userContext);

        //-- Assert
        self::assertThat($ticket->name, self::equalTo($this->ticketRequest->name));
        self::assertThat($ticket->email, self::equalTo($this->ticketRequest->email));
        self::assertThat($ticket->priorityId, self::equalTo($this->ticketRequest->priority));
        self::assertThat($ticket->categoryId, self::equalTo($this->ticketRequest->category));
        self::assertThat($ticket->subject, self::equalTo($this->ticketRequest->subject));
        self::assertThat($ticket->message, self::equalTo($this->ticketRequest->message));
        self::assertThat($ticket->usesHtml, self::equalTo($this->ticketRequest->html));
        self::assertThat($ticket->customFields[1], self::equalTo($this->ticketRequest->customFields[1]));
        self::assertThat($ticket->location, self::equalTo($this->ticketRequest->location));
        self::assertThat($ticket->suggestedArticles, self::equalTo($this->ticketRequest->suggestedKnowledgebaseArticleIds));
        self::assertThat($ticket->userAgent, self::equalTo($this->ticketRequest->userAgent));
        self::assertThat($ticket->screenResolution, self::equalTo($this->ticketRequest->screenResolution));
        self::assertThat($ticket->ipAddress, self::equalTo($this->ticketRequest->ipAddress));
        self::assertThat($ticket->language, self::equalTo($this->ticketRequest->language));
    }

    function testItReturnsTheGeneratedPropertiesOnTheTicket() {
        //-- Arrange
        $this->ticketGatewayGeneratedFields->dateCreated = 'date created';
        $this->ticketGatewayGeneratedFields->dateModified = 'date modified';


        //-- Act
        $ticket = $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->modsForHeskSettings, $this->userContext);

        //-- Assert
        self::assertThat($ticket->dateCreated, self::equalTo($this->ticketGatewayGeneratedFields->dateCreated));
        self::assertThat($ticket->lastChanged, self::equalTo($this->ticketGatewayGeneratedFields->dateModified));
    }
}
