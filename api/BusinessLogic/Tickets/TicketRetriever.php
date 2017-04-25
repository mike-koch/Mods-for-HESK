<?php

namespace BusinessLogic\Tickets;


use BusinessLogic\Exceptions\ApiFriendlyException;
use BusinessLogic\Exceptions\ValidationException;
use BusinessLogic\ValidationModel;
use DataAccess\Tickets\TicketGateway;

class TicketRetriever {
    /**
     * @var $ticketGateway TicketGateway
     */
    private $ticketGateway;

    function __construct($ticketGateway) {
        $this->ticketGateway = $ticketGateway;
    }

    function getTicketById($id, $heskSettings, $userContext) {
        return $this->ticketGateway->getTicketById($id, $heskSettings);
    }

    function getTicketByTrackingIdAndEmail($trackingId, $emailAddress, $heskSettings) {
        $this->validate($trackingId, $emailAddress, $heskSettings);

        $ticket = $this->ticketGateway->getTicketByTrackingId($trackingId, $heskSettings);
        if ($ticket === null) {
            $ticket = $this->ticketGateway->getTicketByMergedTrackingId($trackingId, $heskSettings);

            if ($ticket === null) {
                return null;
            }
        }

        if ($heskSettings['email_view_ticket'] && !in_array($emailAddress, $ticket->email)) {
            throw new ApiFriendlyException("Email '{$emailAddress}' entered in for ticket '{$trackingId}' does not match!",
                "Email Does Not Match", 400);
        }

        return $ticket;
    }

    private function validate($trackingId, $emailAddress, $heskSettings) {
        $validationModel = new ValidationModel();

        if ($trackingId === null || trim($trackingId) === '') {
            $validationModel->errorKeys[] = 'MISSING_TRACKING_ID';
        }

        if ($heskSettings['email_view_ticket'] && ($emailAddress === null || trim($emailAddress) === '')) {
            $validationModel->errorKeys[] = 'EMAIL_REQUIRED_AND_MISSING';
        }

        if (count($validationModel->errorKeys) > 0) {
            throw new ValidationException($validationModel);
        }
    }
}