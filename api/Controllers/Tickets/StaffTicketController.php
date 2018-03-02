<?php

namespace Controllers\Tickets;


use BusinessLogic\Helpers;
use BusinessLogic\Security\UserContext;
use BusinessLogic\Tickets\EditTicketModel;
use BusinessLogic\Tickets\TicketDeleter;
use BusinessLogic\Tickets\TicketEditor;
use BusinessLogic\Tickets\TicketRetriever;
use Controllers\JsonRetriever;

class StaffTicketController extends \BaseClass {
    function get($id) {
        global $applicationContext, $userContext, $hesk_settings;

        /* @var $ticketRetriever TicketRetriever */
        $ticketRetriever = $applicationContext->get(TicketRetriever::clazz());

        output($ticketRetriever->getTicketById($id, $hesk_settings, $userContext));
    }

    function delete($id) {
        global $applicationContext, $userContext, $hesk_settings;

        /* @var $ticketDeleter TicketDeleter */
        $ticketDeleter = $applicationContext->get(TicketDeleter::clazz());

        $ticketDeleter->deleteTicket($id, $userContext, $hesk_settings);

        http_response_code(204);
    }

    function put($id) {
        global $applicationContext, $userContext, $hesk_settings;

        /* @var $ticketEditor TicketEditor */
        $ticketEditor = $applicationContext->get(TicketEditor::clazz());

        $jsonRequest = JsonRetriever::getJsonData();

        $ticketEditor->editTicket($this->getEditTicketModel($id, $jsonRequest), $userContext, $hesk_settings);

        http_response_code(204);
        return;
    }

    static function updateDueDate($id) {
        /* @var $userContext UserContext */
        global $applicationContext, $userContext, $hesk_settings;

        /* @var $ticketEditor TicketEditor */
        $ticketEditor = $applicationContext->get(TicketEditor::clazz());

        $json = JsonRetriever::getJsonData();

        $dueDate = date('Y-m-d H:i:s', strtotime(Helpers::safeArrayGet($json, 'dueDate')));

        $ticketEditor->updateDueDate($id, $dueDate, $userContext, $hesk_settings);
    }

    private function getEditTicketModel($id, $jsonRequest) {
        $editTicketModel = new EditTicketModel();
        $editTicketModel->id = $id;
        $editTicketModel->language = Helpers::safeArrayGet($jsonRequest, 'language');
        $editTicketModel->name = Helpers::safeArrayGet($jsonRequest, 'name');
        $editTicketModel->subject = Helpers::safeArrayGet($jsonRequest, 'subject');
        $editTicketModel->message = Helpers::safeArrayGet($jsonRequest, 'message');
        $editTicketModel->html = Helpers::safeArrayGet($jsonRequest, 'html');
        $editTicketModel->email = Helpers::safeArrayGet($jsonRequest, 'email');

        $jsonCustomFields = Helpers::safeArrayGet($jsonRequest, 'customFields');

        if ($jsonCustomFields !== null && !empty($jsonCustomFields)) {
            foreach ($jsonCustomFields as $key => $value) {
                $editTicketModel->customFields[intval($key)] = $value;
            }
        }

        return $editTicketModel;
    }
}