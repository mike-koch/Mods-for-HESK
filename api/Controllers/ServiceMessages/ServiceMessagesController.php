<?php

namespace Controllers\ServiceMessages;

use BusinessLogic\Security\UserContext;
use BusinessLogic\ServiceMessages\ServiceMessage;
use BusinessLogic\ServiceMessages\ServiceMessageHandler;
use Controllers\JsonRetriever;

class ServiceMessagesController extends \BaseClass {
    function post() {
        global $applicationContext, $userContext, $hesk_settings;

        /* @var $handler ServiceMessageHandler */
        $handler = $applicationContext->get(ServiceMessageHandler::clazz());

        $data = JsonRetriever::getJsonData();
        $element = $handler->createServiceMessage($this->buildElementModel($data, $userContext), $hesk_settings);

        return output($element, 201);
    }

    /**
     * @param $data array
     * @param $userContext UserContext
     * @return ServiceMessage
     */
    private function buildElementModel($data, $userContext) {
        $serviceMessage = new ServiceMessage();
        $serviceMessage->createdBy = $userContext->id;
        $serviceMessage->title = $data['title'];
        $serviceMessage->icon = $data['icon'];
        $serviceMessage->message = $data['message'];
        $serviceMessage->published = $data['published'];
        $serviceMessage->style = $data['style'];

        return $serviceMessage;
    }
}