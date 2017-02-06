<?php
/**
 * Created by PhpStorm.
 * User: mkoch
 * Date: 2/4/2017
 * Time: 9:32 PM
 */

namespace BusinessLogic\Tickets;


use BusinessLogic\Exceptions\ValidationException;
use BusinessLogic\Security\BanRetriever;
use PHPUnit\Framework\TestCase;

class TicketCreatorTest extends TestCase {
    /**
     * @var $ticketCreator TicketCreator
     */
    private $ticketCreator;

    /**
     * @var $banRetriever BanRetriever
     */
    private $banRetriever;

    /**
     * @var $ticketRequest CreateTicketByCustomerModel
     */
    private $ticketRequest;

    private $heskSettings = array();
    private $modsForHeskSettings = array();

    function setUp() {
        $this->banRetriever = $this->createMock(BanRetriever::class);
        $this->ticketCreator = new TicketCreator($this->banRetriever);

        $this->ticketRequest = new CreateTicketByCustomerModel();
        $this->ticketRequest->name = 'Name';
    }

    function testItAddsTheProperValidationErrorWhenNameIsNull() {
        //-- Arrange
        $this->ticketRequest->name = null;

        //-- Act
        $exceptionThrown = false;
        try {
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->modsForHeskSettings);
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
            $this->ticketCreator->createTicketByCustomer($this->ticketRequest, $this->heskSettings, $this->modsForHeskSettings);
        } catch (ValidationException $e) {
            //-- Assert (1/2)
            $exceptionThrown = true;
            $this->assertArraySubset(['NO_NAME'], $e->validationModel->errorKeys);
        }

        //-- Assert (2/2)
        $this->assertThat($exceptionThrown, $this->equalTo(true));
    }
}
