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
use BusinessLogic\Statuses\DefaultStatusForAction;
use BusinessLogic\Tickets\Ticket;
use Core\Constants\Priority;
use DataAccess\Categories\CategoryGateway;
use DataAccess\Statuses\StatusGateway;

class EmailTemplateParser {

    /**
     * @var $statusGateway StatusGateway
     */
    private $statusGateway;

    /**
     * @var $categoryGateway CategoryGateway
     */
    private $categoryGateway;

    function __construct($statusGateway, $categoryGateway) {
        $this->statusGateway = $statusGateway;
        $this->categoryGateway = $categoryGateway;
    }

    /**
     * @param $templateName string
     * @param $language string
     * @param $ticket Ticket
     */
    function getFormattedEmailForLanguage($templateName, $language, $ticket, $heskSettings) {
        global $hesklang;

        $template = self::getFromFileSystem($templateName, $language);
        $subject = ValidEmailTemplates::getValidEmailTemplates()[$templateName];

        $subject = $this->parseSubject($subject, $ticket, $language, $heskSettings);
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

    /**
     * @param $subjectTemplate string
     * @param $ticket Ticket
     * @param $language string
     * @param $heskSettings array
     * @return string
     */
    private function parseSubject($subjectTemplate, $ticket, $language, $heskSettings) {
        global $hesklang;

        if ($ticket === null) {
            return $subjectTemplate;
        }

        // Status name and category name
        $defaultStatus = $this->statusGateway->getStatusForDefaultAction(DefaultStatusForAction::NEW_TICKET, $heskSettings);
        $statusName = $defaultStatus->localizedNames[$language]->text;
        $category = $this->categoryGateway->getAllCategories($heskSettings)[$ticket->categoryId];

        $priority = '';
        switch ($ticket->priorityId) {
            case Priority::CRITICAL:
                $priority = $hesklang['critical'];
                break;
            case Priority::HIGH:
                $priority = $hesklang['high'];
                break;
            case Priority::MEDIUM:
                $priority = $hesklang['medium'];
                break;
            case Priority::LOW:
                $priority = $hesklang['low'];
                break;
            default:
                $priority = 'PRIORITY NOT FOUND';
                break;
        }

        // Special tags
        $msg = str_replace('%%SUBJECT%%', $ticket->subject, $subjectTemplate);
        $msg = str_replace('%%TRACK_ID%%', $ticket->trackingId, $msg);
        $msg = str_replace('%%CATEGORY%%', $category->id, $msg);
        $msg = str_replace('%%PRIORITY%%', $priority, $msg);
        $msg = str_replace('%%STATUS%%', $statusName, $msg);
    }
}