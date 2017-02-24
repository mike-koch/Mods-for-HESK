<?php
/**
 * Created by PhpStorm.
 * User: mkoch
 * Date: 2/22/2017
 * Time: 9:11 PM
 */

namespace BusinessLogic\Emails;


use BusinessLogic\Exceptions\EmailTemplateNotFoundException;
use BusinessLogic\Exceptions\InvalidEmailTemplateException;
use BusinessLogic\Tickets\Ticket;
use DataAccess\Statuses\StatusGateway;

class EmailTemplateParser {

    /**
     * @var $statusGateway StatusGateway
     */
    private $statusGateway;

    function __construct($statusGateway) {
        $this->statusGateway = $statusGateway;
    }

    /**
     * @param $templateName string
     * @param $language string
     * @param $ticket Ticket
     */
    function getFormattedEmailForLanguage($templateName, $language, $ticket) {
        global $hesklang;

        $template = self::getFromFileSystem($templateName, $language);
        $subject = ValidEmailTemplates::getValidEmailTemplates()[$templateName];

        $subject = self::parseSubject($subject, $ticket);
    }

    private function getFromFileSystem($template, $language)
    {
        if (!isset(ValidEmailTemplates::getValidEmailTemplates()[$template])) {
            throw new InvalidEmailTemplateException($template);
        }

        /* Get email template */
        $file = 'language/' . $language . '/emails/' . $template . '.txt';
        $absoluteFilePath = __DIR__ . '/../../../' . $file;

        if (file_exists($absoluteFilePath)) {
            return file_get_contents($absoluteFilePath);
        } else {
            throw new EmailTemplateNotFoundException($template, $language);
        }
    }

    private static function parseSubject($subjectTemplate, $ticket) {
        if ($ticket === null) {
            return $subjectTemplate;
        }

        //--
    }
}