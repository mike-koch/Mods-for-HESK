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
        $this->ticketCreator = new TicketCreator($this->categoryRetriever, $this->banRetriever);
        $this->userContext = new UserContext();

        $this->ticketRequest = new CreateTicketByCustomerModel();
        $this->ticketRequest->name = 'Name';
        $this->ticketRequest->email = 'some@e.mail';
        $this->ticketRequest->category = 1;
        $this->heskSettings = array(
            'multi_eml' => false
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
}
