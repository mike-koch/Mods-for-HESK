<?php

namespace BusinessLogic\Emails;


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

    protected function setUp() {
        $this->emailTemplateParser = $this->createMock(EmailTemplateParser::class);
        $this->basicEmailSender = $this->createMock(BasicEmailSender::class);
        $this->mailgunEmailSender = $this->createMock(MailgunEmailSender::class);

        $this->emailSenderHelper = new EmailSenderHelper($this->emailTemplateParser, $this->basicEmailSender,
            $this->mailgunEmailSender);
    }

    function testItParsesTheTemplateForTheTicket() {

    }
}
