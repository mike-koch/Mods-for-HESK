<?php

namespace BusinessLogic\Tickets\TicketCreatorTests;


use BusinessLogic\Emails\Addressees;
use BusinessLogic\Emails\EmailSenderHelper;
use BusinessLogic\Emails\EmailTemplateRetriever;
use BusinessLogic\Security\UserContext;
use BusinessLogic\Security\UserContextNotifications;
use BusinessLogic\Statuses\Status;
use BusinessLogic\Tickets\Autoassigner;
use BusinessLogic\Tickets\CreateTicketByCustomerModel;
use BusinessLogic\Tickets\NewTicketValidator;
use BusinessLogic\Tickets\TicketCreator;
use BusinessLogic\Tickets\TicketGatewayGeneratedFields;
use BusinessLogic\Tickets\TrackingIdGenerator;
use BusinessLogic\Tickets\VerifiedEmailChecker;
use BusinessLogic\ValidationModel;
use Core\Constants\Priority;
use DataAccess\Security\UserGateway;
use DataAccess\Settings\ModsForHeskSettingsGateway;
use DataAccess\Statuses\StatusGateway;
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
     * @var $statusGateway \PHPUnit_Framework_MockObject_MockObject
     */
    private $statusGateway;

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
     * @var $userContext UserContext
     */
    private $userContext;

    /**
     * @var $ticketGatewayGeneratedFields TicketGatewayGeneratedFields
     */
    private $ticketGatewayGeneratedFields;

    /**
     * @var $verifiedEmailChecker \PHPUnit_Framework_MockObject_MockObject
     */
    private $verifiedEmailChecker;

    /**
     * @var $emailSenderHelper \PHPUnit_Framework_MockObject_MockObject
     */
    private $emailSenderHelper;

    /**
     * @var $userGateway \PHPUnit_Framework_MockObject_MockObject
     */
    private $userGateway;

    /* @var $modsForHeskSettingsGateway \PHPUnit_Framework_MockObject_MockObject */
    private $modsForHeskSettingsGateway;

    /* @var $modsForHeskSettings array */
    private $modsForHeskSettings;

    protected function setUp() {
        $this->ticketGateway = $this->createMock(TicketGateway::class);
        $this->newTicketValidator = $this->createMock(NewTicketValidator::class);
        $this->trackingIdGenerator = $this->createMock(TrackingIdGenerator::class);
        $this->autoassigner = $this->createMock(Autoassigner::class);
        $this->statusGateway = $this->createMock(StatusGateway::class);
        $this->verifiedEmailChecker = $this->createMock(VerifiedEmailChecker::class);
        $this->emailSenderHelper = $this->createMock(EmailSenderHelper::class);
        $this->userGateway = $this->createMock(UserGateway::class);
        $this->modsForHeskSettingsGateway = $this->createMock(ModsForHeskSettingsGateway::class);

        $this->ticketCreator = new TicketCreator($this->newTicketValidator, $this->trackingIdGenerator,
            $this->autoassigner, $this->statusGateway, $this->ticketGateway, $this->verifiedEmailChecker,
            $this->emailSenderHelper, $this->userGateway, $this->modsForHeskSettingsGateway);

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
            'autoassign' => 0
        );
        $this->modsForHeskSettings = array(
            'customer_email_verification_required' => false
        );
        $this->userContext = new UserContext();

        $this->newTicketValidator->method('validateNewTicketForCustomer')->willReturn(new ValidationModel());
        $this->trackingIdGenerator->method('generateTrackingId')->willReturn('123-456-7890');
        $this->ticketGatewayGeneratedFields = new TicketGatewayGeneratedFields();
        $this->ticketGateway->method('createTicket')->willReturn($this->ticketGatewayGeneratedFields);
        $this->userGateway->method('getUsersForNewTicketNotification')->willReturn(array());

        $status = new Status();
        $status->id = 1;
        $this->statusGateway->method('getStatusForDefaultAction')
            ->willReturn($status);
    }

    function testItSavesTheTicketToTheDatabase() {
        //-- Arrange
        $this->modsForHeskSettingsGateway->method('getAllSettings')->willReturn($this->modsForHeskSettings);

        //-- Assert
        $this->ticketGateway->expects($this->once())->method('createTicket');

        //-- Act
        $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->userContext);
    }

    function testItSetsTheTrackingIdOnTheTicket() {
        //-- Arrange
        $this->modsForHeskSettingsGateway->method('getAllSettings')->willReturn($this->modsForHeskSettings);

        //-- Act
        $ticket = $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->userContext);

        //-- Assert
        self::assertThat($ticket->trackingId, self::equalTo('123-456-7890'));
    }

    function testItSetsTheNextUserForAutoassign() {
        //-- Arrange
        $this->heskSettings['autoassign'] = 1;
        $autoassignUser = new UserContext();
        $notificationSettings = new UserContextNotifications();
        $notificationSettings->newAssignedToMe = true;
        $autoassignUser->notificationSettings = $notificationSettings;
        $autoassignUser->id = 1;
        $this->autoassigner->method('getNextUserForTicket')->willReturn($autoassignUser);
        $this->userGateway->method('getUserById')->willReturn($autoassignUser);
        $this->modsForHeskSettingsGateway->method('getAllSettings')->willReturn($this->modsForHeskSettings);

        //-- Act
        $ticket = $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->userContext);

        //-- Assert
        self::assertThat($ticket->ownerId, self::equalTo(1));
    }

    function testItDoesntCallTheAutoassignerWhenDisabledInHesk() {
        //-- Arrange
        $this->modsForHeskSettingsGateway->method('getAllSettings')->willReturn($this->modsForHeskSettings);

        //-- Act
        $ticket = $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->userContext);

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
        $this->ticketRequest->ipAddress = '127.0.0.1';
        $this->ticketRequest->language = 'English';
        $this->modsForHeskSettingsGateway->method('getAllSettings')->willReturn($this->modsForHeskSettings);

        //-- Act
        $ticket = $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->userContext);

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
        $this->ticketGatewayGeneratedFields->id = 50;
        $this->modsForHeskSettingsGateway->method('getAllSettings')->willReturn($this->modsForHeskSettings);

        //-- Act
        $ticket = $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->userContext);

        //-- Assert
        self::assertThat($ticket->dateCreated, self::equalTo($this->ticketGatewayGeneratedFields->dateCreated));
        self::assertThat($ticket->lastChanged, self::equalTo($this->ticketGatewayGeneratedFields->dateModified));
        self::assertThat($ticket->id, self::equalTo($this->ticketGatewayGeneratedFields->id));
    }

    function testItSetsTheDefaultStatus() {
        //-- Arrange
        $this->modsForHeskSettingsGateway->method('getAllSettings')->willReturn($this->modsForHeskSettings);

        //-- Act
        $ticket = $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->userContext);

        //-- Assert
        self::assertThat($ticket->statusId, self::equalTo(1));
    }

    function testItSetsTheDefaultProperties() {
        //-- Arrange
        $this->modsForHeskSettingsGateway->method('getAllSettings')->willReturn($this->modsForHeskSettings);

        //-- Act
        $ticket = $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->userContext);

        //-- Assert
        self::assertThat($ticket->archived, self::isFalse());
        self::assertThat($ticket->locked, self::isFalse());
        self::assertThat($ticket->openedBy, self::equalTo(0));
        self::assertThat($ticket->numberOfReplies, self::equalTo(0));
        self::assertThat($ticket->numberOfStaffReplies, self::equalTo(0));
        self::assertThat($ticket->timeWorked, self::equalTo('00:00:00'));
        self::assertThat($ticket->lastReplier, self::equalTo(0));
    }

    function testItChecksIfTheEmailIsVerified() {
        //-- Arrange
        $this->modsForHeskSettings['customer_email_verification_required'] = true;
        $this->modsForHeskSettingsGateway->method('getAllSettings')->willReturn($this->modsForHeskSettings);

        //-- Assert
        $this->verifiedEmailChecker->expects($this->once())->method('isEmailVerified');

        //-- Act
        $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->userContext);
    }

    function testItSendsAnEmailToTheCustomerWhenTheTicketIsCreated() {
        //-- Arrange
        $this->ticketRequest->sendEmailToCustomer = true;
        $this->ticketRequest->language = 'English';
        $expectedAddressees = new Addressees();
        $expectedAddressees->to = array($this->ticketRequest->email);
        $this->modsForHeskSettingsGateway->method('getAllSettings')->willReturn($this->modsForHeskSettings);

        //-- Assert
        $this->emailSenderHelper->expects($this->once())->method('sendEmailForTicket')
            ->with(EmailTemplateRetriever::NEW_TICKET, 'English', $expectedAddressees, $this->anything(), $this->heskSettings, $this->anything());

        //-- Act
        $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->userContext);
    }

    function testItDoesNotSendsAnEmailToTheCustomerWhenTheTicketIsCreatedAndSendToCustomerIsFalse() {
        //-- Arrange
        $this->ticketRequest->sendEmailToCustomer = false;
        $this->ticketRequest->language = 'English';
        $expectedAddressees = new Addressees();
        $expectedAddressees->to = array($this->ticketRequest->email);
        $this->modsForHeskSettingsGateway->method('getAllSettings')->willReturn($this->modsForHeskSettings);

        //-- Assert
        $this->emailSenderHelper->expects($this->never())->method('sendEmailForTicket');

        //-- Act
        $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->userContext);
    }

    function testItSendsAnEmailToTheAssignedToOwnerWhenTheTicketIsCreated() {
        //-- Arrange
        $this->ticketRequest->sendEmailToCustomer = true;
        $this->ticketRequest->language = 'English';
        $expectedAddressees = new Addressees();
        $expectedAddressees->to = array($this->ticketRequest->email);
        $this->modsForHeskSettingsGateway->method('getAllSettings')->willReturn($this->modsForHeskSettings);

        //-- Assert
        $this->emailSenderHelper->expects($this->once())->method('sendEmailForTicket')
            ->with(EmailTemplateRetriever::NEW_TICKET, 'English', $expectedAddressees, $this->anything(), $this->heskSettings, $this->anything());

        //-- Act
        $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->userContext);
    }
}
