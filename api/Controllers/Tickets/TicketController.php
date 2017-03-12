<?php

namespace Controllers\Tickets;

use BusinessLogic\Helpers;
use BusinessLogic\Tickets\CreateTicketByCustomerModel;
use BusinessLogic\Tickets\TicketCreator;
use BusinessLogic\Tickets\TicketRetriever;
use Controllers\JsonRetriever;


class TicketController {
    function get($id) {
        global $applicationContext, $hesk_settings, $userContext;

        /* @var $ticketRetriever TicketRetriever */
        $ticketRetriever = $applicationContext->get[TicketRetriever::class];

        output($ticketRetriever->getTicketById($id, $hesk_settings, $userContext));
    }

    function post() {
        global $applicationContext, $hesk_settings, $userContext;

        /* @var $ticketCreator TicketCreator */
        $ticketCreator = $applicationContext->get[TicketCreator::class];

        $jsonRequest = JsonRetriever::getJsonData();

        $ticket = $ticketCreator->createTicketByCustomer($this->buildTicketRequestFromJson($jsonRequest), $hesk_settings, $userContext);

        //if ticket is a stageTicket, email user
        //else if assigned to owner, email new owner
        //else email all staff

        return output($ticket);
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
        $ticketRequest->sendEmailToCustomer = Helpers::safeArrayGet($json, 'sendEmailToCustomer');
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