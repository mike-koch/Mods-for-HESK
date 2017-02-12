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
     * @var $ticketGateway TicketGateway
     */
    private $ticketGateway;

    function __construct($newTicketValidator, $trackingIdGenerator, $ticketGateway) {
        $this->newTicketValidator = $newTicketValidator;
        $this->trackingIdGenerator = $trackingIdGenerator;
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

        //-- TODO suggested kb articles

        //-- TODO owner/autoassign logic

        //-- TODO latitude/longitude

        //-- TODO HTML flag

        $this->ticketGateway->createTicket($ticket, $heskSettings);

        return $ticket;
    }
}