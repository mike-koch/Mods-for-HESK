<?php

namespace BusinessLogic\Emails;


use BusinessLogic\Tickets\Ticket;
use PHPUnit\Framework\TestCase;

class EmailSenderHelperTest extends TestCase {
    /**
     * @var $emailTemplateParser \PHPUnit_Framework_MockObject_MockObject
     */
    private $emailTemplateParser;

    /**
     * @var $basicEmailSender \PHPUnit_Framework_MockObject_MockObject
     */
    private $basicEmailSender;

    /**
     * @var $mailgunEmailSender \PHPUnit_Framework_MockObject_MockObject
     */
    private $mailgunEmailSender;

    /**
     * @var $emailSenderHelper EmailSenderHelper
     */
    private $emailSenderHelper;

    /**
     * @var $heskSettings array
     */
    private $heskSettings;

    /**
     * @var $modsForHeskSettings array
     */
    private $modsForHeskSettings;

    protected function setUp() {
        $this->emailTemplateParser = $this->createMock(EmailTemplateParser::class);
        $this->basicEmailSender = $this->createMock(BasicEmailSender::class);
        $this->mailgunEmailSender = $this->createMock(MailgunEmailSender::class);
        $this->heskSettings = array();

        $this->emailSenderHelper = new EmailSenderHelper($this->emailTemplateParser, $this->basicEmailSender,
            $this->mailgunEmailSender);
    }

    function testItParsesTheTemplateForTheTicket() {
        //-- Arrange
        $templateId = EmailTemplateRetriever::NEW_NOTE;
        $languageCode = 'en';
        $ticket = new Ticket();

        //-- Assert
        $this->emailTemplateParser->expects($this->once())
            ->method('getFormattedEmailForLanguage')
            ->with($templateId, $languageCode, $ticket, $this->heskSettings, $this->modsForHeskSettings);

        //-- Act
        $this->emailSenderHelper->sendEmailForTicket($templateId, $languageCode, $ticket, $this->heskSettings, $this->modsForHeskSettings);
    }
}
