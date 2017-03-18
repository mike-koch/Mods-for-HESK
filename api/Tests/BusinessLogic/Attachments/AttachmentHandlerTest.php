<?php


namespace BusinessLogic\Attachments;


use BusinessLogic\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

class AttachmentHandlerTest extends TestCase {

    /* @var $attachmentHandler AttachmentHandler */
    private $attachmentHandler;

    /* @var $createAttachmentModel CreateAttachmentForTicketModel */
    private $createAttachmentForTicketModel;

    protected function setUp() {
        $this->attachmentHandler = new AttachmentHandler();
        $this->createAttachmentForTicketModel = new CreateAttachmentForTicketModel();
        $this->createAttachmentForTicketModel->attachmentContents = base64_encode('string');
        $this->createAttachmentForTicketModel->displayName = 'Display Name';
        $this->createAttachmentForTicketModel->ticketId = 1;
    }

    function testThatValidateThrowsAnExceptionWhenTheAttachmentBodyIsNull() {
        //-- Arrange
        $this->createAttachmentForTicketModel->attachmentContents = null;

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/CONTENTS_EMPTY/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel);
    }

    function testThatValidateThrowsAnExceptionWhenTheAttachmentBodyIsEmpty() {
        //-- Arrange
        $this->createAttachmentForTicketModel->attachmentContents = '';

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/CONTENTS_EMPTY/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel);
    }

    function testThatValidateThrowsAnExceptionWhenTheAttachmentBodyIsInvalidBase64() {
        //-- Arrange
        $this->createAttachmentForTicketModel->attachmentContents = 'invalid base 64';

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/CONTENTS_NOT_BASE_64/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel);
    }

    function testThatValidateThrowsAnExceptionWhenTheDisplayNameIsNull() {
        //-- Arrange
        $this->createAttachmentForTicketModel->displayName = null;

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/DISPLAY_NAME_EMPTY/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel);
    }

    function testThatValidateThrowsAnExceptionWhenTheDisplayNameIsEmpty() {
        //-- Arrange
        $this->createAttachmentForTicketModel->displayName = '';

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/DISPLAY_NAME_EMPTY/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel);
    }

    function testThatValidateThrowsAnExceptionWhenTheTicketIdIsNull() {
        //-- Arrange
        $this->createAttachmentForTicketModel->ticketId = null;

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/TICKET_ID_MISSING/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel);
    }

    function testThatValidateThrowsAnExceptionWhenTheTicketIdIsANonPositiveInteger() {
        //-- Arrange
        $this->createAttachmentForTicketModel->ticketId = 0;

        //-- Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/TICKET_ID_MISSING/');

        //-- Act
        $this->attachmentHandler->createAttachmentForTicket($this->createAttachmentForTicketModel);
    }
}
