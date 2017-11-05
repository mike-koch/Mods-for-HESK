<?php

namespace Controllers\Tickets;

use BusinessLogic\Helpers;
use BusinessLogic\Tickets\CreateTicketByCustomerModel;
use BusinessLogic\Tickets\TicketCreator;
use BusinessLogic\Tickets\TicketRetriever;
use BusinessLogic\ValidationModel;
use Controllers\JsonRetriever;


class CustomerTicketController extends \BaseClass {
    function get() {
        global $applicationContext, $hesk_settings;

        $trackingId = isset($_GET['trackingId']) ? $_GET['trackingId'] : null;
        $emailAddress = isset($_GET['email']) ? $_GET['email'] : null;

        /* @var $ticketRetriever TicketRetriever */
        $ticketRetriever = $applicationContext->get(TicketRetriever::clazz());

        output($ticketRetriever->getTicketByTrackingIdAndEmail($trackingId, $emailAddress, $hesk_settings));
    }

    function post() {
        global $applicationContext, $hesk_settings, $userContext;

        /* @var $ticketCreator TicketCreator */
        $ticketCreator = $applicationContext->get(TicketCreator::clazz());

        $jsonRequest = JsonRetriever::getJsonData();

        $ticket = $ticketCreator->createTicketByCustomer($this->buildTicketRequestFromJson($jsonRequest), $hesk_settings, $userContext);

        return output($ticket->ticket, $ticket->emailVerified ? 201 : 202);
    }

    /**
     * @param $json array
     * @return CreateTicketByCustomerModel
     */
    private function buildTicketRequestFromJson($json) {
        $ticketRequest = new CreateTicketByCustomerModel();
        $ticketRequest->name = Helpers::safeArrayGet($json, 'name');
        $ticketRequest->email = Helpers::safeArrayGet($json, 'email');
        $ticketRequest->category = Helpers::safeArrayGet($json, 'category');
        $ticketRequest->priority = Helpers::safeArrayGet($json, 'priority');
        $ticketRequest->subject = Helpers::safeArrayGet($json, 'subject');
        $ticketRequest->message = Helpers::safeArrayGet($json, 'message');
        $ticketRequest->html = Helpers::safeArrayGet($json, 'html');
        $ticketRequest->location = Helpers::safeArrayGet($json, 'location');
        $ticketRequest->suggestedKnowledgebaseArticleIds = Helpers::safeArrayGet($json, 'suggestedArticles');
        $ticketRequest->userAgent = Helpers::safeArrayGet($json, 'userAgent');
        $ticketRequest->screenResolution = Helpers::safeArrayGet($json, 'screenResolution');
        $ticketRequest->ipAddress = Helpers::safeArrayGet($json, 'ip');
        $ticketRequest->language = Helpers::safeArrayGet($json, 'language');
        $ticketRequest->sendEmailToCustomer = true;
        $ticketRequest->customFields = array();

        $jsonCustomFields = Helpers::safeArrayGet($json, 'customFields');

        if ($jsonCustomFields !== null && !empty($jsonCustomFields)) {
            foreach ($jsonCustomFields as $key => $value) {
                $ticketRequest->customFields[intval($key)] = $value;
            }
        }

        return $ticketRequest;
    }
}