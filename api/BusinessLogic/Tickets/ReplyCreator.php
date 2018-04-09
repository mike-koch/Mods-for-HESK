<?php

namespace BusinessLogic\Tickets;


use BusinessLogic\Emails\EmailSenderHelper;
use BusinessLogic\Exceptions\ApiFriendlyException;
use BusinessLogic\Helpers;
use BusinessLogic\Security\UserContext;
use BusinessLogic\ValidationModel;
use DataAccess\AuditTrail\AuditTrailGateway;
use DataAccess\Security\UserGateway;
use DataAccess\Statuses\StatusGateway;
use DataAccess\Tickets\TicketGateway;

class ReplyCreator {
    private $statusGateway;
    private $ticketGateway;
    private $emailSenderHelper;
    private $userGateway;
    private $auditTrailGateway;

    public function __construct(StatusGateway $statusGateway,
                                TicketGateway $ticketGateway,
                                EmailSenderHelper $emailSenderHelper,
                                UserGateway $userGateway,
                                AuditTrailGateway $auditTrailGateway) {
        $this->statusGateway = $statusGateway;
        $this->ticketGateway = $ticketGateway;
        $this->emailSenderHelper = $emailSenderHelper;
        $this->userGateway = $userGateway;
        $this->auditTrailGateway = $auditTrailGateway;
    }

    /**
     * @param $replyRequest CreateReplyRequest
     * @param $heskSettings array
     * @param $modsForHeskSettings array
     * @param $userContext UserContext
     * @throws ApiFriendlyException
     */
    function createReplyByCustomer($replyRequest, $heskSettings, $modsForHeskSettings, $userContext) {
        $ticket = $this->ticketGateway->getTicketByTrackingId($replyRequest->trackingId, $heskSettings);

        if ($ticket === null) {
            throw new ApiFriendlyException("Ticket with tracking ID {$replyRequest->trackingId} not found.",
                "Ticket not found", 404);
        }

        $validationModel = new ValidationModel();
        if (!strlen($replyRequest->replyMessage)) {
            $validationModel->errorKeys[] = 'MESSAGE_REQUIRED';
        }

        if ($modsForHeskSettings['rich_text_for_tickets_for_customers']) {
            $replyRequest->replyMessage = Helpers::heskMakeUrl($replyRequest->replyMessage);
            $replyRequest->replyMessage = nl2br($replyRequest->replyMessage);
        }
    }
}