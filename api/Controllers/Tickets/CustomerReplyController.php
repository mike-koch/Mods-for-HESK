<?php

namespace Controllers\Tickets;


use BusinessLogic\Helpers;
use BusinessLogic\Tickets\CreateReplyRequest;
use Controllers\JsonRetriever;

class CustomerReplyController extends \BaseClass {
    function post($ticketId) {
        global $applicationContext, $hesk_settings;

        $jsonRequest = JsonRetriever::getJsonData();

        $createReplyByCustomerModel = new CreateReplyRequest();
        $createReplyByCustomerModel->id = $ticketId;
        $createReplyByCustomerModel->emailAddress = Helpers::safeArrayGet($jsonRequest, 'email');
        $createReplyByCustomerModel->trackingId = Helpers::safeArrayGet($jsonRequest, 'trackingId');
        $createReplyByCustomerModel->replyMessage = Helpers::safeArrayGet($jsonRequest, 'message');
        $createReplyByCustomerModel->hasHtml = Helpers::safeArrayGet($jsonRequest, 'html');
        $createReplyByCustomerModel->ipAddress = Helpers::safeArrayGet($jsonRequest, 'ip');
    }
}
