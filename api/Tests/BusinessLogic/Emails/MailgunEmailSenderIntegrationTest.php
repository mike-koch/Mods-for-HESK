<?php

namespace BusinessLogic\Emails;

use BusinessLogic\IntegrationTestCaseBase;
use BusinessLogic\Tickets\Attachment;

class MailgunEmailSenderIntegrationTest extends IntegrationTestCaseBase {
    /**
     * @var $emailSender MailgunEmailSender;
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

    /**
     * @var $attachmentsToPurge string[]
     */
    private $attachmentsToPurge;

    protected function setUp(): void {
        global $hesk_settings, $modsForHesk_settings;

        $this->skip();

        if (!defined('IN_SCRIPT')) {
            define('IN_SCRIPT', 1);
        }
        require(__DIR__ . '/../../../../hesk_settings.inc.php');
        require(__DIR__ . '/../../integration_test_mfh_settings.php');

        $this->emailSender = new MailgunEmailSender();
        $this->heskSettings = $hesk_settings;
        $this->modsForHeskSettings = $modsForHesk_settings;
        $this->attachmentsToPurge = array();
    }

    protected function tearDown() {
        foreach ($this->attachmentsToPurge as $file) {
            unlink($file);
        }
    }

    function testItCanSendMail() {
        //-- Arrange
        $emailBuilder = new EmailBuilder();
        $emailBuilder->to = array('mfh1@mailinator.com');
        $emailBuilder->cc = array('mfh2@mailinator.com');
        $emailBuilder->bcc = array('mfh3@mailinator.com');
        $emailBuilder->message = "Test PLAIN TEXT message";
        $emailBuilder->htmlMessage = "Test <b>HTML</b> <i>message</i>";
        $emailBuilder->subject = "MailgunEmailSenderIntegrationTest";

        $attachment = new Attachment();
        $attachment->id = 1;
        $attachment->fileName = "file.txt";
        $attachment->savedName = "test1.txt";
        $filename1 = __DIR__ . '/../../../../' . $this->heskSettings['attach_dir'] . '/' . $attachment->savedName;
        file_put_contents($filename1, 'TEST DATA');

        $otherAttachment = new Attachment();
        $otherAttachment->id = 2;
        $otherAttachment->fileName = "file2.txt";
        $otherAttachment->savedName = "test2.txt";
        $filename2 = __DIR__ . '/../../../../' . $this->heskSettings['attach_dir'] . '/' . $otherAttachment->savedName;
        file_put_contents($filename2, 'TEST DATA 2');

        $emailBuilder->attachments = array($attachment, $otherAttachment);
        $this->attachmentsToPurge = array($filename1, $filename2);


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
        $emailBuilder->subject = "MailgunEmailSenderIntegrationTest";


        //-- Act
        $result = $this->emailSender->sendEmail($emailBuilder, $this->heskSettings, $this->modsForHeskSettings, false);

        //-- Assert
        if ($result !== true) {
            $this->fail($result);
        }
    }
}
