<?php

namespace Controllers\Tickets;


use BusinessLogic\Emails\Addressees;
use BusinessLogic\Emails\EmailSenderHelper;
use BusinessLogic\Emails\EmailTemplate;
use BusinessLogic\Emails\EmailTemplateRetriever;
use BusinessLogic\Exceptions\ApiFriendlyException;
use BusinessLogic\Tickets\TicketRetriever;
use Controllers\InternalApiController;
use DataAccess\Settings\ModsForHeskSettingsGateway;

class ResendTicketEmailToCustomerController extends InternalApiController {
    function get($ticketId) {
        global $applicationContext, $userContext, $hesk_settings;

        $this->checkForInternalUseOnly();

        /* @var $ticketRetriever TicketRetriever */
        $ticketRetriever = $applicationContext->get(TicketRetriever::class);
        $ticket = $ticketRetriever->getTicketById($ticketId, $hesk_settings, $userContext);

        /* @var $modsForHeskSettingsGateway ModsForHeskSettingsGateway */
        $modsForHeskSettingsGateway = $applicationContext->get(ModsForHeskSettingsGateway::class);
        $modsForHeskSettings = $modsForHeskSettingsGateway->getAllSettings($hesk_settings);

        /* @var $emailSender EmailSenderHelper */
        $emailSender = $applicationContext->get(EmailSenderHelper::class);

        $language = $ticket->language;

        if ($language === null) {
            $language = $hesk_settings['language'];
        }

        if ($ticket === null) {
            throw new ApiFriendlyException("Ticket {$ticketId} not found!", "Ticket Not Found", 404);
        }

        $reply = null;
        $emailTemplate = EmailTemplateRetriever::NEW_TICKET;
        if (isset($_GET['replyId'])) {
            $replyId = $_GET['replyId'];
            $emailTemplate = EmailTemplateRetriever::NEW_REPLY_BY_STAFF;

            foreach ($ticket->replies as $ticketReply) {
                if ($ticketReply->id === $replyId) {
                    $reply = $ticketReply;
                    break;
                }
            }

            if ($reply === null) {
                throw new ApiFriendlyException("Reply {$replyId} not found on ticket {$ticketId}!", "Reply Not Found", 404);
            }

            // Copy over reply properties onto the Ticket
            $ticket->lastReplier = $reply->replierName;
            $ticket->message = $reply->message;
            $ticket->attachments = $reply->attachments;
        }

        $addressees = new Addressees();
        $addressees->to = $ticket->email;

        $emailSender->sendEmailForTicket($emailTemplate, $language, $addressees, $ticket, $hesk_settings, $modsForHeskSettings);

        http_response_code(204);
    }
}