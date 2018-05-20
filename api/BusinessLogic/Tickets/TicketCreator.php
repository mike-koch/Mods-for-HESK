<?php

namespace BusinessLogic\Tickets;

use BusinessLogic\DateTimeHelpers;
use BusinessLogic\Emails\Addressees;
use BusinessLogic\Emails\EmailSenderHelper;
use BusinessLogic\Emails\EmailTemplateRetriever;
use BusinessLogic\Exceptions\ValidationException;
use BusinessLogic\Statuses\DefaultStatusForAction;
use DataAccess\AuditTrail\AuditTrailGateway;
use DataAccess\Categories\CategoryGateway;
use DataAccess\CustomFields\CustomFieldsGateway;
use DataAccess\Security\UserGateway;
use DataAccess\Settings\ModsForHeskSettingsGateway;
use DataAccess\Statuses\StatusGateway;
use DataAccess\Tickets\TicketGateway;

class TicketCreator extends \BaseClass {
    /**
     * @var $newTicketValidator NewTicketValidator
     */
    private $newTicketValidator;

    /**
     * @var $trackingIdGenerator TrackingIdGenerator
     */
    private $trackingIdGenerator;

    /**
     * @var $autoassigner Autoassigner
     */
    private $autoassigner;

    /**
     * @var $statusGateway StatusGateway
     */
    private $statusGateway;

    /**
     * @var $ticketGateway TicketGateway
     */
    private $ticketGateway;

    /**
     * @var $verifiedEmailChecker VerifiedEmailChecker
     */
    private $verifiedEmailChecker;

    /**
     * @var $emailSenderHelper EmailSenderHelper
     */
    private $emailSenderHelper;

    /**
     * @var $userGateway UserGateway
     */
    private $userGateway;

    /* @var $modsForHeskSettingsGateway ModsForHeskSettingsGateway */
    private $modsForHeskSettingsGateway;

    /* @var $auditTrailGateway AuditTrailGateway */
    private $auditTrailGateway;

    /* @var $customFieldsGateway CustomFieldsGateway */
    private $customFieldsGateway;

    private $categoryGateway;

    function __construct(NewTicketValidator $newTicketValidator,
                         TrackingIdGenerator $trackingIdGenerator,
                         Autoassigner $autoassigner,
                         StatusGateway $statusGateway,
                         TicketGateway $ticketGateway,
                         VerifiedEmailChecker $verifiedEmailChecker,
                         EmailSenderHelper $emailSenderHelper,
                         UserGateway $userGateway,
                         ModsForHeskSettingsGateway $modsForHeskSettingsGateway,
                         AuditTrailGateway $auditTrailGateway,
                         CustomFieldsGateway $customFieldsGateway,
                         CategoryGateway $categoryGateway) {
        $this->newTicketValidator = $newTicketValidator;
        $this->trackingIdGenerator = $trackingIdGenerator;
        $this->autoassigner = $autoassigner;
        $this->statusGateway = $statusGateway;
        $this->ticketGateway = $ticketGateway;
        $this->verifiedEmailChecker = $verifiedEmailChecker;
        $this->emailSenderHelper = $emailSenderHelper;
        $this->userGateway = $userGateway;
        $this->modsForHeskSettingsGateway = $modsForHeskSettingsGateway;
        $this->auditTrailGateway = $auditTrailGateway;
        $this->customFieldsGateway = $customFieldsGateway;
        $this->categoryGateway = $categoryGateway;
    }

    /**
     * Ticket attachments are <b>NOT</b> handled here!
     *
     * @param $ticketRequest CreateTicketByCustomerModel
     * @param $heskSettings array HESK settings
     * @param $userContext
     * @return CreatedTicketModel The newly created ticket along with if the email is verified or not
     * @throws ValidationException When a required field in $ticket_request is missing
     * @throws \Exception When the default status for new tickets is not found
     */
    function createTicketByCustomer($ticketRequest, $heskSettings, $userContext) {
        $modsForHeskSettings = $this->modsForHeskSettingsGateway->getAllSettings($heskSettings);

        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($ticketRequest, $heskSettings, $userContext);

        if (count($validationModel->errorKeys) > 0) {
            // Validation failed
            throw new ValidationException($validationModel);
        }

        $emailVerified = true;
        if ($modsForHeskSettings['customer_email_verification_required']) {
            $emailVerified = $this->verifiedEmailChecker->isEmailVerified($ticketRequest->email, $heskSettings);
        }

        // Create the ticket
        $ticket = $emailVerified
            ? new Ticket()
            : new StageTicket();
        $ticket->trackingId = $this->trackingIdGenerator->generateTrackingId($heskSettings);

        $category = null;
        $categories = $this->categoryGateway->getAllCategories($heskSettings, $modsForHeskSettings);
        foreach ($categories as $innerCategory) {
            if ($innerCategory->id === $ticketRequest->category) {
                $category = $innerCategory;
                break;
            }
        }
        if ($heskSettings['autoassign'] && $category->autoAssign) {
            $autoassignOwner = $this->autoassigner->getNextUserForTicket($ticketRequest->category, $heskSettings);
            $ticket->ownerId = $autoassignOwner === null ? null : $autoassignOwner->id;
            $ticket->assignedBy = -1;
        }

        // Transform one-to-one properties
        $ticket->name = $ticketRequest->name;
        $ticket->email = $this->getAddressees($ticketRequest->email);
        $ticket->priorityId = $ticketRequest->priority;
        $ticket->categoryId = $ticketRequest->category;
        $ticket->subject = $ticketRequest->subject;
        $ticket->message = $ticketRequest->message;
        $ticket->usesHtml = $ticketRequest->html;
        $ticket->customFields = $ticketRequest->customFields;
        $ticket->location = $ticketRequest->location;
        $ticket->suggestedArticles = $ticketRequest->suggestedKnowledgebaseArticleIds;
        $ticket->userAgent = $ticketRequest->userAgent;
        $ticket->screenResolution = $ticketRequest->screenResolution;
        $ticket->ipAddress = $ticketRequest->ipAddress;
        $ticket->language = $ticketRequest->language;

        $status = $this->statusGateway->getStatusForDefaultAction(DefaultStatusForAction::NEW_TICKET, $heskSettings);

        if ($status === null) {
            throw new \BaseException("Could not find the default status for a new ticket!");
        }
        $ticket->statusId = $status->id;

        $ticketGatewayGeneratedFields = $this->ticketGateway->createTicket($ticket, $emailVerified, $heskSettings);

        $ticket->dateCreated = $ticketGatewayGeneratedFields->dateCreated;
        $ticket->lastChanged = $ticketGatewayGeneratedFields->dateModified;
        $ticket->archived = false;
        $ticket->locked = false;
        $ticket->id = $ticketGatewayGeneratedFields->id;
        $ticket->openedBy = 0;
        $ticket->numberOfReplies = 0;
        $ticket->numberOfStaffReplies = 0;
        $ticket->timeWorked = '00:00:00';
        $ticket->lastReplier = 0;

        $this->auditTrailGateway->insertAuditTrailRecord($ticket->id, AuditTrailEntityType::TICKET,
            'audit_created', DateTimeHelpers::heskDate($heskSettings), array(
                0 => $ticket->name
            ), $heskSettings);

        $addressees = new Addressees();
        $addressees->to = $ticket->email;

        foreach ($ticket->customFields as $key => $value) {
            $customField = $this->customFieldsGateway->getCustomField($key, $heskSettings);
            if ($customField !== null &&
                $customField->type === 'email' &&
                $customField->properties['email_type'] !== 'none') {
                if ($customField->properties['email_type'] === 'cc') {
                    $addressees->cc[] = $value;
                } elseif ($customField->properties['email_type'] === 'bcc') {
                    $addressees->bcc[] = $value;
                }
            }
        }

        if ($ticketRequest->sendEmailToCustomer && $emailVerified) {
            $this->emailSenderHelper->sendEmailForTicket(EmailTemplateRetriever::NEW_TICKET, $ticketRequest->language, $addressees, $ticket, $heskSettings, $modsForHeskSettings);
        } else if ($modsForHeskSettings['customer_email_verification_required'] && !$emailVerified) {
            $this->emailSenderHelper->sendEmailForTicket(EmailTemplateRetriever::VERIFY_EMAIL, $ticketRequest->language, $addressees, $ticket, $heskSettings, $modsForHeskSettings);
        }

        if ($ticket->ownerId !== null) {
            $owner = $this->userGateway->getUserById($ticket->ownerId, $heskSettings);

            if ($owner->notificationSettings->newAssignedToMe) {
                $addressees = new Addressees();
                $addressees->to = array($owner->email);
                $this->emailSenderHelper->sendEmailForTicket(EmailTemplateRetriever::TICKET_ASSIGNED_TO_YOU, $ticketRequest->language, $addressees, $ticket, $heskSettings, $modsForHeskSettings);
            }
        } else {
            // TODO Test
            $usersToBeNotified = $this->userGateway->getUsersForNewTicketNotification($heskSettings);

            foreach ($usersToBeNotified as $user) {
                if ($user->admin || in_array($ticket->categoryId, $user->categories)) {
                    $this->sendEmailToStaff($user, $ticket, $heskSettings, $modsForHeskSettings);
                }
            }
        }

        return new CreatedTicketModel($ticket, $emailVerified);
    }

    private function getAddressees($emailAddress) {
        if ($emailAddress === null) {
            return null;
        }

        $emails = str_replace(';', ',', $emailAddress);

        return explode(',', $emails);
    }

    private function sendEmailToStaff($user, $ticket, $heskSettings, $modsForHeskSettings) {
        $addressees = new Addressees();
        $addressees->to = array($user->email);
        $language = $user->language !== null && trim($user->language) !== ''
            ? $user->language
            : $heskSettings['language'];

        $this->emailSenderHelper->sendEmailForTicket(EmailTemplateRetriever::NEW_TICKET_STAFF, $language,
            $addressees, $ticket, $heskSettings, $modsForHeskSettings);
    }
}