<?php

namespace BusinessLogic\Tickets;

use BusinessLogic\Exceptions\ValidationException;
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
     * @var $ticketGateway TicketGateway
     */
    private $ticketGateway;

    function __construct($newTicketValidator, $trackingIdGenerator, $autoassigner, $ticketGateway) {
        $this->newTicketValidator = $newTicketValidator;
        $this->trackingIdGenerator = $trackingIdGenerator;
        $this->autoassigner = $autoassigner;
        $this->ticketGateway = $ticketGateway;
    }

    /**
     * Ticket attachments are <b>NOT</b> handled here!
     *
     * @param $ticketRequest CreateTicketByCustomerModel
     * @param $heskSettings array HESK settings
     * @param $modsForHeskSettings array Mods for HESK settings
     * @return Ticket The newly created ticket
     * @throws ValidationException When a required field in $ticket_request is missing
     *
     */
    function createTicketByCustomer($ticketRequest, $heskSettings, $modsForHeskSettings, $userContext) {
        $validationModel = $this->newTicketValidator->validateNewTicketForCustomer($ticketRequest, $heskSettings, $userContext);

        if (count($validationModel->errorKeys) > 0) {
            // Validation failed
            $validationModel->valid = false;
            throw new ValidationException($validationModel);
        }

        // Create the ticket
        $ticket = new Ticket();
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

        $ticketGatewayGeneratedFields = $this->ticketGateway->createTicket($ticket, $heskSettings);

        $ticket->dateCreated = $ticketGatewayGeneratedFields->dateCreated;
        $ticket->lastChanged = $ticketGatewayGeneratedFields->dateModified;

        return $ticket;
    }
}