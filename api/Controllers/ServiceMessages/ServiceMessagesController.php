<?php

namespace Controllers\ServiceMessages;

use BusinessLogic\Security\UserContext;
use BusinessLogic\ServiceMessages\ServiceMessage;
use BusinessLogic\ServiceMessages\ServiceMessageHandler;
use Controllers\JsonRetriever;
use Symfony\Component\EventDispatcher\Tests\Service;

class ServiceMessagesController extends \BaseClass {
    function get() {
        global $applicationContext, $hesk_settings;

        /* @var $handler ServiceMessageHandler */
        $handler = $applicationContext->get(ServiceMessageHandler::clazz());

        return output($handler->getServiceMessages($hesk_settings));
    }

    function post() {
        global $applicationContext, $userContext, $hesk_settings;

        /* @var $handler ServiceMessageHandler */
        $handler = $applicationContext->get(ServiceMessageHandler::clazz());

        $data = JsonRetriever::getJsonData();
        $element = $handler->createServiceMessage($this->buildElementModel($data, $userContext), $hesk_settings);

        return output($element, 201);
    }

    function put() {
        global $applicationContext, $hesk_settings;

        /* @var $handler ServiceMessageHandler */
        $handler = $applicationContext->get(ServiceMessageHandler::clazz());

        $data = JsonRetriever::getJsonData();
        $element = $handler->editServiceMessage($this->buildElementModel($data, null, false), $hesk_settings);

        return output($element);
    }

    function delete($id) {
        global $applicationContext, $hesk_settings;

        /* @var $handler ServiceMessageHandler */
        $handler = $applicationContext->get(ServiceMessageHandler::clazz());

        $handler->deleteServiceMessage($id, $hesk_settings);

        return http_response_code(204);
    }

    /**
     * @param $data array
     * @param $userContext UserContext
     * @return ServiceMessage
     */
    private function buildElementModel($data, $userContext, $creating = true) {
        $serviceMessage = new ServiceMessage();

        if (!$creating) {
            $serviceMessage->id = $data['id'];
            $serviceMessage->order = $data['order'];
        }

        if ($creating) {
            $serviceMessage->createdBy = $userContext->id;
        }

        $serviceMessage->title = $data['title'];
        $serviceMessage->icon = $data['icon'];
        $serviceMessage->message = $data['message'];
        $serviceMessage->published = $data['published'];
        $serviceMessage->style = $data['style'];

        return $serviceMessage;
    }
}