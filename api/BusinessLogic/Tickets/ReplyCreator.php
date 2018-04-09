<?php

namespace BusinessLogic\Tickets;


use BusinessLogic\Emails\EmailSenderHelper;
use BusinessLogic\Emails\EmailTemplateRetriever;
use BusinessLogic\Exceptions\ApiFriendlyException;
use BusinessLogic\Exceptions\ValidationException;
use BusinessLogic\Helpers;
use BusinessLogic\Security\UserContext;
use BusinessLogic\Statuses\Closable;
use BusinessLogic\Statuses\DefaultStatusForAction;
use BusinessLogic\ValidationModel;
use DataAccess\AuditTrail\AuditTrailGateway;
use DataAccess\Security\LoginGateway;
use DataAccess\Security\UserGateway;
use DataAccess\Statuses\StatusGateway;
use DataAccess\Tickets\ReplyGateway;
use DataAccess\Tickets\TicketGateway;

class ReplyCreator {
    private $statusGateway;
    private $ticketGateway;
    private $emailSenderHelper;
    private $userGateway;
    private $auditTrailGateway;
    private $loginGateway;
    private $replyGateway;

    public function __construct(StatusGateway $statusGateway,
                                TicketGateway $ticketGateway,
                                EmailSenderHelper $emailSenderHelper,
                                UserGateway $userGateway,
                                AuditTrailGateway $auditTrailGateway,
                                LoginGateway $loginGateway,
                                ReplyGateway $replyGateway) {
        $this->statusGateway = $statusGateway;
        $this->ticketGateway = $ticketGateway;
        $this->emailSenderHelper = $emailSenderHelper;
        $this->userGateway = $userGateway;
        $this->auditTrailGateway = $auditTrailGateway;
        $this->loginGateway = $loginGateway;
        $this->replyGateway = $replyGateway;
    }

    /**
     * @param $replyRequest CreateReplyRequest
     * @param $heskSettings array
     * @param $modsForHeskSettings array
     * @param $userContext UserContext
     * @throws ApiFriendlyException
     * @throws \Exception
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

            throw new ValidationException($validationModel);
        }

        if ($modsForHeskSettings['rich_text_for_tickets_for_customers']) {
            $replyRequest->replyMessage = Helpers::heskMakeUrl($replyRequest->replyMessage);
            $replyRequest->replyMessage = nl2br($replyRequest->replyMessage);
        }

        if ($this->loginGateway->isIpLockedOut($replyRequest->ipAddress, $heskSettings)) {
            throw new ApiFriendlyException("The IP address entered has been locked out of the system for {$heskSettings['attempt_banmin']} minutes because of too many login failures",
                "Locked Out",
                403);
        }

        if ($this->ticketGateway->areRepliesBeingFlooded($replyRequest->ticketId, $replyRequest->ipAddress, $heskSettings)) {
            throw new ApiFriendlyException("You have been locked out of the system for {$heskSettings['attempt_banmin']} minutes because of too many replies to a ticket.",
                "Locked Out",
                403);
        }

        // If staff hasn't replied yet, don't change the status; otherwise set it to the status for customer replies
        $currentStatus = $this->statusGateway->getStatusById($ticket->statusId, $heskSettings);
        if ($currentStatus->closable === Closable::YES || $currentStatus->closable === Closable::CUSTOMERS_ONLY) {
            $customerReplyStatus = $this->statusGateway->getStatusForDefaultAction(DefaultStatusForAction::CUSTOMER_REPLY, $heskSettings);
            $defaultNewTicketStatus = $this->statusGateway->getStatusForDefaultAction(DefaultStatusForAction::NEW_TICKET, $heskSettings);

            $ticket->statusId = $ticket->statusId === $defaultNewTicketStatus->id ?
                $defaultNewTicketStatus->id :
                $customerReplyStatus->id;
        }

        $this->ticketGateway->updateMetadataForReply($ticket->id, $ticket->statusId, $heskSettings);
        $this->replyGateway->insertReply($ticket->id, $ticket->name, $replyRequest->replyMessage, $replyRequest->hasHtml, $heskSettings);

        //-- Changing the ticket message to the reply's
        $ticket->message = $replyRequest->replyMessage;

        // TODO Send the email.
    }
}