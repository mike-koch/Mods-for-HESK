<?php

namespace Controllers\Tickets;


use BusinessLogic\Helpers;
use BusinessLogic\Tickets\CreateReplyRequest;
use BusinessLogic\Tickets\ReplyCreator;
use Controllers\JsonRetriever;
use DataAccess\Settings\ModsForHeskSettingsGateway;

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

        /* @var $modsForHeskSettingsGateway ModsForHeskSettingsGateway */
        $modsForHeskSettingsGateway = $applicationContext->get(ModsForHeskSettingsGateway::clazz());
        $modsForHesk_settings = $modsForHeskSettingsGateway->getAllSettings($hesk_settings);

        /* @var $replyCreator ReplyCreator */
        $replyCreator = $applicationContext->get(ReplyCreator::clazz());
        $createdReply = $replyCreator->createReplyByCustomer($createReplyByCustomerModel, $hesk_settings, $modsForHesk_settings);

        return output($createdReply, 201);
    }
}
