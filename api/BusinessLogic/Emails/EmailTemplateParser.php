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
use DataAccess\Security\UserGateway;
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

    /**
     * @var $userGateway UserGateway
     */
    private $userGateway;

    function __construct($statusGateway, $categoryGateway, $userGateway) {
        $this->statusGateway = $statusGateway;
        $this->categoryGateway = $categoryGateway;
        $this->userGateway = $userGateway;
    }

    /**
     * @param $templateName string
     * @param $language string
     * @param $ticket Ticket
     */
    function getFormattedEmailForLanguage($templateName, $language, $ticket, $forStaff, $heskSettings) {
        global $hesklang;

        $template = self::getFromFileSystem($templateName, $language, false);
        $htmlTemplate = self::getFromFileSystem($templateName, $language, true);
        $subject = ValidEmailTemplates::getValidEmailTemplates()[$templateName];

        $subject = $this->parseSubject($subject, $ticket, $language, $heskSettings);
        $message = $this->parseMessage($template, $ticket, $language, $forStaff, $heskSettings);
        $htmlMessage = $this->parseMessage($htmlTemplate, $ticket, $language, $forStaff, $heskSettings);
    }

    /**
     * @param $template string
     * @param $language string
     * @param $html bool
     * @return string The template
     * @throws EmailTemplateNotFoundException If the template was not found in the filesystem for the provided language
     * @throws InvalidEmailTemplateException If the $template is not a valid template name
     */
    private function getFromFileSystem($template, $language, $html)
    {
        if (!isset(ValidEmailTemplates::getValidEmailTemplates()[$template])) {
            throw new InvalidEmailTemplateException($template);
        }
        $htmlFolder = $html ? 'html/' : '';

        /* Get email template */
        $file = "language/{$language}/emails/{$htmlFolder}{$template}.txt";
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
     * @throws \Exception if common.inc.php isn't loaded
     */
    private function parseSubject($subjectTemplate, $ticket, $language, $heskSettings) {
        global $hesklang;

        if (!function_exists('hesk_msgToPlain')) {
            throw new \Exception("common.inc.php not loaded!");
        }

        if ($ticket === null) {
            return $subjectTemplate;
        }

        // Status name and category name
        $defaultStatus = $this->statusGateway->getStatusForDefaultAction(DefaultStatusForAction::NEW_TICKET, $heskSettings);
        $statusName = $defaultStatus->localizedNames[$language]->text;
        $category = $this->categoryGateway->getAllCategories($heskSettings)[$ticket->categoryId];

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
        $subject = str_replace('%%SUBJECT%%', $ticket->subject, $subjectTemplate);
        $subject = str_replace('%%TRACK_ID%%', $ticket->trackingId, $subject);
        $subject = str_replace('%%CATEGORY%%', $category->id, $subject);
        $subject = str_replace('%%PRIORITY%%', $priority, $subject);
        $subject = str_replace('%%STATUS%%', $statusName, $subject);

        return $subject;
    }

    /**
     * @param $messageTemplate string
     * @param $ticket Ticket
     * @param $language string
     * @param $heskSettings array
     * @return string
     * @throws \Exception if common.inc.php isn't loaded
     */
    private function parseMessage($messageTemplate, $ticket, $language, $admin, $heskSettings) {
        global $hesklang;

        if (!function_exists('hesk_msgToPlain')) {
            throw new \Exception("common.inc.php not loaded!");
        }

        if ($ticket === null) {
            return $messageTemplate;
        }

        $heskSettings['site_title'] = hesk_msgToPlain($heskSettings['site_title'], 1);

        // Is email required to view ticket (for customers only)?
        $heskSettings['e_param'] = $heskSettings['email_view_ticket'] ? '&e=' . rawurlencode($ticket->email) : '';

        /* Generate the ticket URLs */
        $trackingURL = $heskSettings['hesk_url'];
        $trackingURL .= $admin ? '/' . $heskSettings['admin_dir'] . '/admin_ticket.php' : '/ticket.php';
        $trackingURL .= '?track=' . $ticket->trackingId . ($admin ? '' : $heskSettings['e_param']) . '&Refresh=' . rand(10000, 99999);

        // Status name and category name
        $defaultStatus = $this->statusGateway->getStatusForDefaultAction(DefaultStatusForAction::NEW_TICKET, $heskSettings);
        $statusName = hesk_msgToPlain($defaultStatus->localizedNames[$language]->text);
        $category = hesk_msgToPlain($this->categoryGateway->getAllCategories($heskSettings)[$ticket->categoryId]);
        $owner = hesk_msgToPlain($this->userGateway->getNameForId($ticket->ownerId, $heskSettings));

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
        $msg = str_replace('%%NAME%%', $ticket->name, $messageTemplate);
        $msg = str_replace('%%SUBJECT%%', $ticket->subject, $msg);
        $msg = str_replace('%%TRACK_ID%%', $ticket->trackingId, $msg);
        $msg = str_replace('%%TRACK_URL%%', $trackingURL, $msg);
        $msg = str_replace('%%SITE_TITLE%%', $heskSettings['site_title'], $msg);
        $msg = str_replace('%%SITE_URL%%', $heskSettings['site_url'], $msg);
        $msg = str_replace('%%CATEGORY%%', $category, $msg);
        $msg = str_replace('%%PRIORITY%%', $priority, $msg);
        $msg = str_replace('%%OWNER%%', $owner, $msg);
        $msg = str_replace('%%STATUS%%', $statusName, $msg);
        $msg = str_replace('%%EMAIL%%', $ticket->email, $msg);
        $msg = str_replace('%%CREATED%%', $ticket->dateCreated, $msg);
        $msg = str_replace('%%UPDATED%%', $ticket->lastChanged, $msg);
        $msg = str_replace('%%ID%%', $ticket->id, $msg);

        return $msg;
    }
}