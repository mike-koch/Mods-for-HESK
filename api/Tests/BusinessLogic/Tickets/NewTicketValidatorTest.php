<?php

namespace BusinessLogic\Tickets\TicketCreatorTests;


use BusinessLogic\Categories\Category;
use BusinessLogic\Categories\CategoryRetriever;
use BusinessLogic\Security\BanRetriever;
use BusinessLogic\Security\UserContext;
use BusinessLogic\Tickets\CreateTicketByCustomerModel;
use BusinessLogic\Tickets\NewTicketValidator;
use BusinessLogic\Tickets\TicketValidators;
use Core\Constants\CustomField;
use Core\Constants\Priority;
use PHPUnit\Framework\TestCase;

class NewTicketValidatorTest extends TestCase {
    /**
     * @var $newTicketValidator NewTicketValidator
     */
    private $newTicketValidator;

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

    function setUp(): void {
        $this->banRetriever = $this->createMock(BanRetriever::clazz());
        $this->categoryRetriever = $this->createMock(CategoryRetriever::clazz());
        $this->ticketValidators = $this->createMock(TicketValidators::clazz());
        $this->newTicketValidator = new NewTicketValidator($this->categoryRetriever, $this->banRetriever, $this->ticketValidators);
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
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['NO_NAME'], $validationModel->errorKeys);
    }

    function testItAddsTheProperValidationErrorWhenNameIsBlank() {
        //-- Arrange
        $this->ticketRequest->name = '';

        //-- Act
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['NO_NAME'], $validationModel->errorKeys);
    }

    function testItAddsTheProperValidationErrorWhenEmailIsNull() {
        //-- Arrange
        $this->ticketRequest->email = null;

        //-- Act
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['INVALID_OR_MISSING_EMAIL'], $validationModel->errorKeys);
    }

    function testItAddsTheProperValidationErrorWhenEmailIsBlank() {
        //-- Arrange
        $this->ticketRequest->email = '';

        //-- Act
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['INVALID_OR_MISSING_EMAIL'], $validationModel->errorKeys);
    }

    function testItAddsTheProperValidationErrorWhenEmailIsInvalid() {
        //-- Arrange
        $this->ticketRequest->email = 'something@';

        //-- Act
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['INVALID_OR_MISSING_EMAIL'], $validationModel->errorKeys);
    }

    function testItSupportsMultipleEmails() {
        //-- Arrange
        $this->ticketRequest->email = 'something@email.com;another@valid.email';
        $this->ticketRequest->language = 'English';
        $this->heskSettings['multi_eml'] = true;

        //-- Act
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        self::assertThat(count($validationModel->errorKeys), self::equalTo(0));
    }

    function testItAddsTheProperValidationErrorWhenCategoryIsNotANumber() {
        //-- Arrange
        $this->ticketRequest->category = 'something';

        //-- Act
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['NO_CATEGORY'], $validationModel->errorKeys);
    }

    function testItAddsTheProperValidationErrorWhenCategoryIsNegative() {
        //-- Arrange
        $this->ticketRequest->category = -5;

        //-- Act
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['NO_CATEGORY'], $validationModel->errorKeys);
    }

    function testItAddsTheProperValidationErrorWhenTheCategoryDoesNotExist() {
        //-- Arrange
        $this->ticketRequest->category = 10;

        //-- Act
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['CATEGORY_DOES_NOT_EXIST'], $validationModel->errorKeys);
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWithPriorityCritical() {
        //-- Arrange
        $this->ticketRequest->priority = Priority::CRITICAL;
        $this->heskSettings['cust_urgency'] = true;

        //-- Act
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['CRITICAL_PRIORITY_FORBIDDEN'], $validationModel->errorKeys);
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWithNullSubjectAndItIsRequired() {
        //-- Arrange
        $this->ticketRequest->subject = null;
        $this->heskSettings['require_subject'] = 1;

        //-- Act
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['SUBJECT_REQUIRED'], $validationModel->errorKeys);
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWithBlankSubjectAndItIsRequired() {
        //-- Arrange
        $this->ticketRequest->subject = '';
        $this->heskSettings['require_subject'] = 1;

        //-- Act
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['SUBJECT_REQUIRED'], $validationModel->errorKeys);
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWithNullMessageAndItIsRequired() {
        //-- Arrange
        $this->ticketRequest->message = null;
        $this->heskSettings['require_message'] = 1;

        //-- Act
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['MESSAGE_REQUIRED'], $validationModel->errorKeys);
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWithBlankMessageAndItIsRequired() {
        //-- Arrange
        $this->ticketRequest->message = '';
        $this->heskSettings['require_message'] = 1;

        //-- Act
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['MESSAGE_REQUIRED'], $validationModel->errorKeys);
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
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['CUSTOM_FIELD_1_INVALID::NO_VALUE'], $validationModel->errorKeys);
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
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['CUSTOM_FIELD_1_INVALID::NO_VALUE'], $validationModel->errorKeys);
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
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['CUSTOM_FIELD_1_INVALID::INVALID_DATE'], $validationModel->errorKeys);
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
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['CUSTOM_FIELD_1_INVALID::DATE_BEFORE_MIN::MIN:2017-01-01::ENTERED:2016-12-31'], $validationModel->errorKeys);
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
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['CUSTOM_FIELD_1_INVALID::DATE_AFTER_MAX::MAX:2017-01-01::ENTERED:2017-01-02'], $validationModel->errorKeys);
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
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['CUSTOM_FIELD_1_INVALID::INVALID_EMAIL'], $validationModel->errorKeys);
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWithABannedEmail() {
        //-- Arrange
        $this->ticketRequest->email = 'some@banned.email';
        $this->banRetriever->method('isEmailBanned')
                        ->with($this->ticketRequest->email, $this->heskSettings)
                        ->willReturn(true);

        //-- Act
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['EMAIL_BANNED'], $validationModel->errorKeys);
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWhenTheyAreMaxedOut() {
        //-- Arrange
        $this->ticketRequest->email = 'some@maxedout.email';
        $this->ticketValidators->method('isCustomerAtMaxTickets')
            ->with($this->ticketRequest->email, $this->heskSettings)
            ->willReturn(true);

        //-- Act
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['EMAIL_AT_MAX_OPEN_TICKETS'], $validationModel->errorKeys);
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWithLanguageNull() {
        //-- Arrange
        $this->ticketRequest->language = null;
        $this->ticketValidators->method('isCustomerAtMaxTickets')
            ->with($this->ticketRequest->email, $this->heskSettings)
            ->willReturn(false);

        //-- Act
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['MISSING_LANGUAGE'], $validationModel->errorKeys);
    }

    function testItAddsTheProperValidationErrorWhenTheCustomerSubmitsTicketWithLanguageBlank() {
        //-- Arrange
        $this->ticketRequest->language = '';
        $this->ticketValidators->method('isCustomerAtMaxTickets')
            ->with($this->ticketRequest->email, $this->heskSettings)
            ->willReturn(false);

        //-- Act
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($this->ticketRequest,
            $this->heskSettings,
            $this->userContext);

        //-- Assert
        $this->assertArraySubset(['MISSING_LANGUAGE'], $validationModel->errorKeys);
    }
}
