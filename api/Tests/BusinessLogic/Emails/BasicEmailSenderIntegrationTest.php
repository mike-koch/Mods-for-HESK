<?php

namespace BusinessLogic\Emails;

use PHPUnit\Framework\TestCase;

class BasicEmailSenderIntegrationTest extends TestCase {
    /**
     * @var $emailSender BasicEmailSender;
     */
    private $emailSender;

    /**
     * @var $heskSettings array
     */
    private $heskSettings;

    /**
     * @var $modsForHeskSettings array
     */
    private $modsForHeskSettings;

    protected function setUp() {
        global $hesk_settings, $modsForHesk_settings;

        if (!defined('IN_SCRIPT')) {
            define('IN_SCRIPT', 1);
        }
        require(__DIR__ . '/../../../../hesk_settings.inc.php');
        require(__DIR__ . '/../../integration_test_mfh_settings.php');

        $this->emailSender = new BasicEmailSender();
        $this->heskSettings = $hesk_settings;
        $this->modsForHeskSettings = $modsForHesk_settings;
    }

    function testItCanSendHtmlMail() {
        //-- Arrange
        //$hesk_settings['smtp'] = 0 //Uncomment this to use PHPMail
        $emailBuilder = new EmailBuilder();
        $emailBuilder->to = array('mfh1@mailinator.com');
        $emailBuilder->cc = array('mfh2@mailinator.com');
        $emailBuilder->bcc = array('mfh3@mailinator.com');
        $emailBuilder->message = "Test PLAIN TEXT message";
        $emailBuilder->htmlMessage = "Test <b>HTML</b> <i>message</i>";
        $emailBuilder->subject = "BasicEmailSenderIntegrationTest";


        //-- Act
        $result = $this->emailSender->sendEmail($emailBuilder, $this->heskSettings, $this->modsForHeskSettings, true);

        //-- Assert
        if ($result !== true) {
            $this->fail($result);
        }
    }

    function testItCanSendPlaintextMail() {
        //-- Arrange
        //$hesk_settings['smtp'] = 0 //Uncomment this to use PHPMail
        $emailBuilder = new EmailBuilder();
        $emailBuilder->to = array('mfh1@mailinator.com');
        $emailBuilder->cc = array('mfh2@mailinator.com');
        $emailBuilder->bcc = array('mfh3@mailinator.com');
        $emailBuilder->message = "Test PLAIN TEXT message";
        $emailBuilder->subject = "BasicEmailSenderIntegrationTest";


        //-- Act
        $result = $this->emailSender->sendEmail($emailBuilder, $this->heskSettings, $this->modsForHeskSettings, false);

        //-- Assert
        if ($result !== true) {
            $this->fail($result);
        }
    }
}
