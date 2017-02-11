<?php
/**
 * Created by PhpStorm.
 * User: mkoch
 * Date: 2/4/2017
 * Time: 9:32 PM
 */

namespace BusinessLogic\Tickets;


use BusinessLogic\Categories\Category;
use BusinessLogic\Categories\CategoryRetriever;
use BusinessLogic\Exceptions\ValidationException;
use BusinessLogic\Security\BanRetriever;
use BusinessLogic\Security\UserContext;
use Core\Constants\CustomField;
use Core\Constants\Priority;
use PHPUnit\Framework\TestCase;

class TicketCreatorTest extends TestCase {
    /**
     * @var $ticketCreator TicketCreator
     */
    private $ticketCreator;

    /**
     * @var $banRetriever \PHPUnit_Framework_MockObject_MockObject
     */
    private $banRetriever;

    /**
     * @var $categoryRetriever \PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryRetriever;

    /**
     * @var $ticketValidators \PHPUnit_Framework_MockObject_MockObject
     */
    private $ticketValidators;

    /**
     * @var $ticketRequest CreateTicketByCustomerModel
     */
    private $ticketRequest;

    /**
     * @var $userContext UserContext
     */
    private $userContext;

    private $heskSettings = array();
    private $modsForHeskSettings = array();

    function setUp() {
        $this->banRetriever = $this->createMock(BanRetriever::class);
        $this->categoryRetriever = $this->createMock(CategoryRetriever::class);
        $this->ticketValidators = $this->createMock(TicketValidators::class);
        $this->ticketCreator = new TicketCreator($this->categoryRetriever, $this->banRetriever, $this->ticketValidators);
        $this->userContext = new UserContext();

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

        $category = new Category();
        $category->accessible = true;
        $category->id = 1;
        $categories = array();
        $categories[1] = $category;
        $this->categoryRetriever->method('getAllCategories')
                ->willReturn($categories);
    }

    function testItAddsTheProperValidationErrorWhenNameIsNull() {
        //-- Arrange
        $this->ticketRequest->name = null;

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['NO_NAME'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }

    function testItAddsTheProperValidationErrorWhenNameIsBlank() {
        //-- Arrange
        $this->ticketRequest->name = '';

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['NO_NAME'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }

    function testItAddsTheProperValidationErrorWhenEmailIsNull() {
        //-- Arrange
        $this->ticketRequest->email = null;

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['INVALID_OR_MISSING_EMAIL'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }

    function testItAddsTheProperValidationErrorWhenEmailIsBlank() {
        //-- Arrange
        $this->ticketRequest->email = '';

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['INVALID_OR_MISSING_EMAIL'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }

    function testItAddsTheProperValidationErrorWhenEmailIsInvalid() {
        //-- Arrange
        $this->ticketRequest->email = 'something@';

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['INVALID_OR_MISSING_EMAIL'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }

    function testItSupportsMultipleEmails() {
        //-- Arrange
        $this->ticketRequest->email = 'something@email.com;another@valid.email';
        $this->heskSettings['multi_eml'] = true;

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            var_dump($e->validationModel->errorKeys);
            $this->fail('Should not have thrown a ValidationException! Validation error keys are above.');
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(false));
    }

    function testItAddsTheProperValidationErrorWhenCategoryIsNotANumber() {
        //-- Arrange
        $this->ticketRequest->category = 'something';

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['NO_CATEGORY'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }

    function testItAddsTheProperValidationErrorWhenCategoryIsNegative() {
        //-- Arrange
        $this->ticketRequest->category = -5;

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['NO_CATEGORY'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }

    function testItAddsTheProperValidationErrorWhenTheCategoryDoesNotExist() {
        //-- Arrange
        $this->ticketRequest->category = 10;

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['CATEGORY_DOES_NOT_EXIST'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWithPriorityCritical() {
        //-- Arrange
        $this->ticketRequest->priority = Priority::CRITICAL;
        $this->heskSettings['cust_urgency'] = true;

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['CRITICAL_PRIORITY_FORBIDDEN'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWithNullSubjectAndItIsRequired() {
        //-- Arrange
        $this->ticketRequest->subject = null;
        $this->heskSettings['require_subject'] = 1;

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['SUBJECT_REQUIRED'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWithBlankSubjectAndItIsRequired() {
        //-- Arrange
        $this->ticketRequest->subject = '';
        $this->heskSettings['require_subject'] = 1;

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['SUBJECT_REQUIRED'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWithNullMessageAndItIsRequired() {
        //-- Arrange
        $this->ticketRequest->message = null;
        $this->heskSettings['require_message'] = 1;

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['MESSAGE_REQUIRED'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWithBlankMessageAndItIsRequired() {
        //-- Arrange
        $this->ticketRequest->message = '';
        $this->heskSettings['require_message'] = 1;

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['MESSAGE_REQUIRED'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWithNullRequiredCustomField() {
        //-- Arrange
        $customField = array();
        $customField['req'] = 1;
        $customField['type'] = CustomField::TEXT;
        $customField['use'] = 1;
        $customField['category'] = array();
        $this->heskSettings['custom_fields']['custom1'] = $customField;
        $this->ticketRequest->customFields[1] = null;

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['CUSTOM_FIELD_1_INVALID::NO_VALUE'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWithBlankRequiredCustomField() {
        //-- Arrange
        $customField = array();
        $customField['req'] = 1;
        $customField['type'] = CustomField::TEXT;
        $customField['use'] = 1;
        $customField['category'] = array();
        $this->heskSettings['custom_fields']['custom1'] = $customField;
        $this->ticketRequest->customFields[1] = '';

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['CUSTOM_FIELD_1_INVALID::NO_VALUE'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWithDateCustomFieldThatIsInvalid() {
        //-- Arrange
        $customField = array();
        $customField['req'] = 1;
        $customField['type'] = CustomField::DATE;
        $customField['use'] = 1;
        $customField['category'] = array();
        $this->heskSettings['custom_fields']['custom1'] = $customField;
        $this->ticketRequest->customFields[1] = '2017-30-00';

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['CUSTOM_FIELD_1_INVALID::INVALID_DATE'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWithDateThatIsBeforeMinDate() {
        //-- Arrange
        $customField = array();
        $customField['req'] = 1;
        $customField['type'] = CustomField::DATE;
        $customField['use'] = 1;
        $customField['category'] = array();
        $customField['value'] = array(
            'dmin' => '2017-01-01',
            'dmax' => ''
        );
        $this->heskSettings['custom_fields']['custom1'] = $customField;
        $this->ticketRequest->customFields[1] = '2016-12-31';

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['CUSTOM_FIELD_1_INVALID::DATE_BEFORE_MIN::MIN:2017-01-01::ENTERED:2016-12-31'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWithDateThatIsAfterMaxDate() {
        //-- Arrange
        $customField = array();
        $customField['req'] = 1;
        $customField['type'] = CustomField::DATE;
        $customField['use'] = 1;
        $customField['category'] = array();
        $customField['value'] = array(
            'dmin' => '',
            'dmax' => '2017-01-01'
        );
        $this->heskSettings['custom_fields']['custom1'] = $customField;
        $this->ticketRequest->customFields[1] = '2017-01-02';

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['CUSTOM_FIELD_1_INVALID::DATE_AFTER_MAX::MAX:2017-01-01::ENTERED:2017-01-02'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWithEmailThatIsInvalid() {
        //-- Arrange
        $customField = array();
        $customField['req'] = 1;
        $customField['type'] = CustomField::EMAIL;
        $customField['use'] = 1;
        $customField['category'] = array();
        $customField['value'] = array(
            'multiple' => 0
        );
        $this->heskSettings['custom_fields']['custom1'] = $customField;
        $this->ticketRequest->customFields[1] = 'invalid@';

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['CUSTOM_FIELD_1_INVALID::INVALID_EMAIL'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWithABannedEmail() {
        //-- Arrange
        $this->ticketRequest->email = 'some@banned.email';
        $this->banRetriever->method('isEmailBanned')
                        ->with($this->ticketRequest->email, $this->heskSettings)
                        ->willReturn(true);

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['EMAIL_BANNED'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWhenTheyAreMaxedOut() {
        //-- Arrange
        $this->ticketRequest->email = 'some@maxedout.email';
        $this->ticketValidators->method('isCustomerAtMaxTickets')
            ->with($this->ticketRequest->email, $this->heskSettings)
            ->willReturn(true);

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest,
                $this->heskSettings,
                $this->modsForHeskSettings,
                $this->userContext);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['EMAIL_AT_MAX_OPEN_TICKETS'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }
}
