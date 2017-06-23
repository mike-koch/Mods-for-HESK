<?php

namespace BusinessLogic\Tickets;

use BusinessLogic\Emails\Addressees;
use BusinessLogic\Emails\EmailSenderHelper;
use BusinessLogic\Emails\EmailTemplateRetriever;
use BusinessLogic\Exceptions\ValidationException;
use BusinessLogic\Statuses\DefaultStatusForAction;
use DataAccess\Security\UserGateway;
use DataAccess\Settings\ModsForHeskSettingsGateway;
use DataAccess\Statuses\StatusGateway;
use DataAccess\Tickets\TicketGateway;

class TicketCreator {
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

    function __construct($newTicketValidator, $trackingIdGenerator, $autoassigner, $statusGateway, $ticketGateway,
                         $verifiedEmailChecker, $emailSenderHelper, $userGateway, $modsForHeskSettingsGateway) {
        $this->newTicketValidator = $newTicketValidator;
        $this->trackingIdGenerator = $trackingIdGenerator;
        $this->autoassigner = $autoassigner;
        $this->statusGateway = $statusGateway;
        $this->ticketGateway = $ticketGateway;
        $this->verifiedEmailChecker = $verifiedEmailChecker;
        $this->emailSenderHelper = $emailSenderHelper;
        $this->userGateway = $userGateway;
        $this->modsForHeskSettingsGateway = $modsForHeskSettingsGateway;
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

        if ($heskSettings['autoassign']) {
            $ticket->ownerId = $this->autoassigner->getNextUserForTicket($ticketRequest->category, $heskSettings)->id;
        }

        // Transform one-to-one properties
        $ticket->name = $ticketRequest->name;
        $ticket->email = $ticketRequest->email;
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
            throw new \Exception("Could not find the default status for a new ticket!");
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

        $addressees = new Addressees();
        $addressees->to = $this->getAddressees($ticket->email);

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