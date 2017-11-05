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
        $this->emailTemplateParser = $this->createMock(EmailTemplateParser::clazz());
        $this->basicEmailSender = $this->createMock(BasicEmailSender::clazz());
        $this->mailgunEmailSender = $this->createMock(MailgunEmailSender::clazz());
        $this->heskSettings = array(
            'languages' => array(
                'English' => array('folder' => 'en')
            ),
            'custom_fields' => array()
        );
        $this->modsForHeskSettings = array(
            'attachments' => 0,
            'use_mailgun' => 0,
            'html_emails' => 0
        );

        $this->emailSenderHelper = new EmailSenderHelper($this->emailTemplateParser, $this->basicEmailSender,
            $this->mailgunEmailSender);
    }

    function testItParsesTheTemplateForTheTicket() {
        //-- Arrange
        $templateId = EmailTemplateRetriever::NEW_NOTE;
        $languageCode = 'en';
        $ticket = new Ticket();
        $this->emailTemplateParser->method('getFormattedEmailForLanguage')->willReturn(new ParsedEmailProperties('Subject', 'Message', 'HTML Message'));

        //-- Assert
        $this->emailTemplateParser->expects($this->once())
            ->method('getFormattedEmailForLanguage')
            ->with($templateId, $languageCode, $ticket, $this->heskSettings, $this->modsForHeskSettings);

        //-- Act
        $this->emailSenderHelper->sendEmailForTicket($templateId, 'English', new Addressees(), $ticket, $this->heskSettings, $this->modsForHeskSettings);
    }

    function testItSendsTheEmailThroughTheMailgunEmailSender() {
        //-- Arrange
        $addressees = new Addressees();
        $addressees->to = ['to@email'];
        $addressees->cc = ['cc1', 'cc2'];
        $addressees->bcc = ['bcc1', 'bcc2'];
        $this->modsForHeskSettings['use_mailgun'] = 1;
        $this->modsForHeskSettings['html_emails'] = true;

        $expectedEmailBuilder = new EmailBuilder();
        $expectedEmailBuilder->to = $addressees->to;
        $expectedEmailBuilder->cc = $addressees->cc;
        $expectedEmailBuilder->bcc = $addressees->bcc;
        $expectedEmailBuilder->subject = 'Subject';
        $expectedEmailBuilder->message = 'Message';
        $expectedEmailBuilder->htmlMessage = 'HTML Message';

        $this->emailTemplateParser->method('getFormattedEmailForLanguage')->willReturn(new ParsedEmailProperties('Subject', 'Message', 'HTML Message'));

        //-- Assert
        $this->mailgunEmailSender->expects($this->once())
            ->method('sendEmail')
            ->with($expectedEmailBuilder, $this->heskSettings, $this->modsForHeskSettings, true);

        //-- Act
        $this->emailSenderHelper->sendEmailForTicket(EmailTemplateRetriever::NEW_NOTE, 'English', $addressees, new Ticket(), $this->heskSettings, $this->modsForHeskSettings);
    }

    function testItSendsTheEmailThroughTheBasicEmailSender() {
        //-- Arrange
        $addressees = new Addressees();
        $addressees->to = ['to@email'];
        $addressees->cc = ['cc1', 'cc2'];
        $addressees->bcc = ['bcc1', 'bcc2'];
        $this->modsForHeskSettings['use_mailgun'] = 0;
        $this->modsForHeskSettings['html_emails'] = true;

        $expectedEmailBuilder = new EmailBuilder();
        $expectedEmailBuilder->to = $addressees->to;
        $expectedEmailBuilder->cc = $addressees->cc;
        $expectedEmailBuilder->bcc = $addressees->bcc;
        $expectedEmailBuilder->subject = 'Subject';
        $expectedEmailBuilder->message = 'Message';
        $expectedEmailBuilder->htmlMessage = 'HTML Message';

        $this->emailTemplateParser->method('getFormattedEmailForLanguage')->willReturn(new ParsedEmailProperties('Subject', 'Message', 'HTML Message'));

        //-- Assert
        $this->basicEmailSender->expects($this->once())
            ->method('sendEmail')
            ->with($expectedEmailBuilder, $this->heskSettings, $this->modsForHeskSettings, true);

        //-- Act
        $this->emailSenderHelper->sendEmailForTicket(EmailTemplateRetriever::NEW_NOTE, 'English', $addressees, new Ticket(), $this->heskSettings, $this->modsForHeskSettings);
    }
}
