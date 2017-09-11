<?php

namespace BusinessLogic\Tickets;


use BusinessLogic\Attachments\AttachmentHandler;
use BusinessLogic\Exceptions\AccessViolationException;
use BusinessLogic\Exceptions\ApiFriendlyException;
use BusinessLogic\Security\UserPrivilege;
use BusinessLogic\Security\UserToTicketChecker;
use DataAccess\Tickets\TicketGateway;

class TicketDeleter {
    /* @var $ticketGateway TicketGateway */
    private $ticketGateway;

    /* @var $userToTicketChecker UserToTicketChecker */
    private $userToTicketChecker;

    /* @var $attachmentHandler AttachmentHandler */
    private $attachmentHandler;

    function __construct(TicketGateway $ticketGateway,
                         UserToTicketChecker $userToTicketChecker,
                         AttachmentHandler $attachmentHandler) {
        $this->ticketGateway = $ticketGateway;
        $this->userToTicketChecker = $userToTicketChecker;
        $this->attachmentHandler = $attachmentHandler;
    }

    function deleteTicket($ticketId, $userContext, $heskSettings) {
        $ticket = $this->ticketGateway->getTicketById($ticketId, $heskSettings);

        if ($ticket === null) {
            throw new ApiFriendlyException("Ticket {$ticketId} not found!", "Ticket Not Found", 404);
        }

        if (!$this->userToTicketChecker->isTicketAccessibleToUser($userContext, $ticket, $heskSettings,
            array(UserPrivilege::CAN_DELETE_TICKETS))) {
            throw new AccessViolationException("User does not have access to ticket {$ticketId}");
        }

        foreach ($ticket->attachments as $attachment) {
            $this->attachmentHandler->deleteAttachmentFromTicket($ticketId, $attachment->id, $userContext, $heskSettings);
        }

        foreach ($ticket->replies as $reply) {
            foreach ($reply->attachments as $attachment) {
                $this->attachmentHandler->deleteAttachmentFromTicket($ticketId, $attachment->id, $userContext, $heskSettings);
            }
        }

        $this->ticketGateway->deleteReplyDraftsForTicket($ticketId, $heskSettings);

        $this->ticketGateway->deleteRepliesForTicket($ticketId, $heskSettings);

        $this->ticketGateway->deleteNotesForTicket($ticketId, $heskSettings);

        $this->ticketGateway->deleteTicket($ticketId, $heskSettings);
    }
}