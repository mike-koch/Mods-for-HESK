<?php

namespace Controllers\Tickets;


use BusinessLogic\Helpers;
use BusinessLogic\Tickets\CreateReplyByCustomerModel;
use Controllers\JsonRetriever;

class CustomerReplyController extends \BaseClass {
    function post($ticketId) {
        global $applicationContext, $hesk_settings;

        $trackingId = isset($_GET['trackingId']) ? $_GET['trackingId'] : null;
        $emailAddress = isset($_GET['email']) ? $_GET['email'] : null;

        $jsonRequest = JsonRetriever::getJsonData();

        $createReplyByCustomerModel = new CreateReplyByCustomerModel();
        $createReplyByCustomerModel->id = $ticketId;
        $createReplyByCustomerModel->emailAddress = $emailAddress;
        $createReplyByCustomerModel->trackingId = $trackingId;
        $createReplyByCustomerModel->message = Helpers::safeArrayGet($jsonRequest, 'message');
        $createReplyByCustomerModel->html = Helpers::safeArrayGet($jsonRequest, 'html');
    }
}