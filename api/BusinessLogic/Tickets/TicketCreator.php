<?php

namespace BusinessLogic\Tickets;

use BusinessLogic\Emails\Addressees;
use BusinessLogic\Emails\EmailSenderHelper;
use BusinessLogic\Emails\EmailTemplateRetriever;
use BusinessLogic\Exceptions\ValidationException;
use BusinessLogic\Statuses\DefaultStatusForAction;
use DataAccess\Security\UserGateway;
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

    function __construct($newTicketValidator, $trackingIdGenerator, $autoassigner,
                         $statusGateway, $ticketGateway, $verifiedEmailChecker, $emailSenderHelper, $userGateway) {
        $this->newTicketValidator = $newTicketValidator;
        $this->trackingIdGenerator = $trackingIdGenerator;
        $this->autoassigner = $autoassigner;
        $this->statusGateway = $statusGateway;
        $this->ticketGateway = $ticketGateway;
        $this->verifiedEmailChecker = $verifiedEmailChecker;
        $this->emailSenderHelper = $emailSenderHelper;
        $this->userGateway = $userGateway;
    }

    /**
     * Ticket attachments are <b>NOT</b> handled here!
     *
     * @param $ticketRequest CreateTicketByCustomerModel
     * @param $heskSettings array HESK settings
     * @param $modsForHeskSettings array Mods for HESK settings
     * @param $userContext
     * @return Ticket The newly created ticket
     * @throws ValidationException When a required field in $ticket_request is missing
     * @throws \Exception When the default status for new tickets is not found
     */
    function createTicketByCustomer($ticketRequest, $heskSettings, $modsForHeskSettings, $userContext) {
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($ticketRequest, $heskSettings, $userContext);

        if (count($validationModel->errorKeys) > 0) {
            // Validation failed
            $validationModel->valid = false;
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
            $ticket->ownerId = $this->autoassigner->getNextUserForTicket($ticketRequest->category, $heskSettings);
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

        if ($ticketRequest->sendEmailToCustomer) {
            $this->emailSenderHelper->sendEmailForTicket(EmailTemplateRetriever::NEW_TICKET, $ticketRequest->language, $addressees, $ticket, $heskSettings, $modsForHeskSettings);
        }

        if ($ticket->ownerId !== null) {
            $ownerEmail = $this->userGateway->getEmailForId($ticket->ownerId, $heskSettings);

            $addressees = new Addressees();
            $addressees->to = array($ownerEmail);
            $this->emailSenderHelper->sendEmailForTicket(EmailTemplateRetriever::TICKET_ASSIGNED_TO_YOU, $ticketRequest->language, $addressees, $ticket, $heskSettings, $modsForHeskSettings);
        } else {
            // TODO email all users who should be notified
        }

        return $ticket;
    }

    private function getAddressees($emailAddress) {
        if ($emailAddress === null) {
            return null;
        }

        $emails = str_replace(';', ',', $emailAddress);

        return explode(',', $emails);
    }
}