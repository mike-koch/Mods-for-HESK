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
use DataAccess\AuditTrail\AuditTrailGateway;
use DataAccess\Categories\CategoryGateway;
use DataAccess\CustomFields\CustomFieldsGateway;
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

    /* @var $auditTrailGateway \PHPUnit_Framework_MockObject_MockObject|AuditTrailGateway */
    private $auditTrailGateway;

    /* @var $customFieldsGateway \PHPUnit_Framework_MockObject_MockObject|CustomFieldsGateway */
    private $customFieldsGateway;

    /* @var $categoryGateway \PHPUnit_Framework_MockObject_MockObject|CategoryGateway */
    private $categoryGateway;

    protected function setUp(): void {
        $this->ticketGateway = $this->createMock(TicketGateway::clazz());
        $this->newTicketValidator = $this->createMock(NewTicketValidator::clazz());
        $this->trackingIdGenerator = $this->createMock(TrackingIdGenerator::clazz());
        $this->autoassigner = $this->createMock(Autoassigner::clazz());
        $this->statusGateway = $this->createMock(StatusGateway::clazz());
        $this->verifiedEmailChecker = $this->createMock(VerifiedEmailChecker::clazz());
        $this->emailSenderHelper = $this->createMock(EmailSenderHelper::clazz());
        $this->userGateway = $this->createMock(UserGateway::clazz());
        $this->modsForHeskSettingsGateway = $this->createMock(ModsForHeskSettingsGateway::clazz());
        $this->auditTrailGateway = $this->createMock(AuditTrailGateway::clazz());
        $this->customFieldsGateway = $this->createMock(CustomFieldsGateway::clazz());
        $this->categoryGateway = $this->createMock(CategoryGateway::clazz());

        $this->ticketCreator = new TicketCreator($this->newTicketValidator, $this->trackingIdGenerator,
            $this->autoassigner, $this->statusGateway, $this->ticketGateway, $this->verifiedEmailChecker,
            $this->emailSenderHelper, $this->userGateway, $this->modsForHeskSettingsGateway, $this->auditTrailGateway, $this->customFieldsGateway,
            $this->categoryGateway);

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
            'timeformat' => 'Y-m-d',
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
        $this->categoryGateway->method('getAllCategories')
            ->willReturn(array());
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
        self::assertThat($ticket->ticket->trackingId, self::equalTo('123-456-7890'));
    }

    function testItSetsTheNextUserForAutoassign() {
        //-- Arrange
        $this->markTestSkipped();
		
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
        self::assertThat($ticket->ticket->ownerId, self::equalTo(1));
    }

    function testItDoesntCallTheAutoassignerWhenDisabledInHesk() {
        //-- Arrange
        $this->modsForHeskSettingsGateway->method('getAllSettings')->willReturn($this->modsForHeskSettings);

        //-- Act
        $ticket = $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->userContext);

        //-- Assert
        self::assertThat($ticket->ticket->ownerId, self::equalTo(null));
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
        self::assertThat($ticket->ticket->name, self::equalTo($this->ticketRequest->name));
        self::assertThat($ticket->ticket->email[0], self::equalTo($this->ticketRequest->email));
        self::assertThat($ticket->ticket->priorityId, self::equalTo($this->ticketRequest->priority));
        self::assertThat($ticket->ticket->categoryId, self::equalTo($this->ticketRequest->category));
        self::assertThat($ticket->ticket->subject, self::equalTo($this->ticketRequest->subject));
        self::assertThat($ticket->ticket->message, self::equalTo($this->ticketRequest->message));
        self::assertThat($ticket->ticket->usesHtml, self::equalTo($this->ticketRequest->html));
        self::assertThat($ticket->ticket->customFields[1], self::equalTo($this->ticketRequest->customFields[1]));
        self::assertThat($ticket->ticket->location, self::equalTo($this->ticketRequest->location));
        self::assertThat($ticket->ticket->suggestedArticles, self::equalTo($this->ticketRequest->suggestedKnowledgebaseArticleIds));
        self::assertThat($ticket->ticket->userAgent, self::equalTo($this->ticketRequest->userAgent));
        self::assertThat($ticket->ticket->screenResolution, self::equalTo($this->ticketRequest->screenResolution));
        self::assertThat($ticket->ticket->ipAddress, self::equalTo($this->ticketRequest->ipAddress));
        self::assertThat($ticket->ticket->language, self::equalTo($this->ticketRequest->language));
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
        self::assertThat($ticket->ticket->dateCreated, self::equalTo($this->ticketGatewayGeneratedFields->dateCreated));
        self::assertThat($ticket->ticket->lastChanged, self::equalTo($this->ticketGatewayGeneratedFields->dateModified));
        self::assertThat($ticket->ticket->id, self::equalTo($this->ticketGatewayGeneratedFields->id));
    }

    function testItSetsTheDefaultStatus() {
        //-- Arrange
        $this->modsForHeskSettingsGateway->method('getAllSettings')->willReturn($this->modsForHeskSettings);

        //-- Act
        $ticket = $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->userContext);

        //-- Assert
        self::assertThat($ticket->ticket->statusId, self::equalTo(1));
    }

    function testItSetsTheDefaultProperties() {
        //-- Arrange
        $this->modsForHeskSettingsGateway->method('getAllSettings')->willReturn($this->modsForHeskSettings);

        //-- Act
        $ticket = $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->userContext);

        //-- Assert
        self::assertThat($ticket->ticket->archived, self::isFalse());
        self::assertThat($ticket->ticket->locked, self::isFalse());
        self::assertThat($ticket->ticket->openedBy, self::equalTo(0));
        self::assertThat($ticket->ticket->numberOfReplies, self::equalTo(0));
        self::assertThat($ticket->ticket->numberOfStaffReplies, self::equalTo(0));
        self::assertThat($ticket->ticket->timeWorked, self::equalTo('00:00:00'));
        self::assertThat($ticket->ticket->lastReplier, self::equalTo(0));
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
