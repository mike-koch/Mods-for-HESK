<?php

namespace Controllers\ServiceMessages;

use BusinessLogic\Exceptions\ApiFriendlyException;
use BusinessLogic\Helpers;
use BusinessLogic\Security\UserContext;
use BusinessLogic\Security\UserPrivilege;
use BusinessLogic\ServiceMessages\GetServiceMessagesFilter;
use BusinessLogic\ServiceMessages\ServiceMessage;
use BusinessLogic\ServiceMessages\ServiceMessageHandler;
use Controllers\ControllerWithSecurity;
use Controllers\JsonRetriever;

class ServiceMessagesController extends \BaseClass {
    /**
     * @param $userContext UserContext
     * @throws ApiFriendlyException
     */
    function checkSecurity($userContext) {
        if (!$userContext->admin && !in_array(UserPrivilege::CAN_MANAGE_SERVICE_MESSAGES, $userContext->permissions)) {
            throw new ApiFriendlyException("User does not have permission to access the following URI: " . $_SERVER['REQUEST_URI'], "Access Forbidden", 403);
        }
    }

    static function staticCheckSecurity($userContext) {
        if (!$userContext->admin && !in_array(UserPrivilege::CAN_MANAGE_SERVICE_MESSAGES, $userContext->permissions)) {
            throw new ApiFriendlyException("User does not have permission to access the following URI: " . $_SERVER['REQUEST_URI'], "Access Forbidden", 403);
        }
    }
    
    function get() {
        /* @var $userContext UserContext */
        /* @var $hesk_settings array */
        global $applicationContext, $hesk_settings, $userContext;

        $searchFilter = new GetServiceMessagesFilter();
        if ($userContext->isAnonymousUser()) {
            $searchFilter->includeDrafts = false;
            $searchFilter->includeStaffServiceMessages = false;
        } elseif (!$userContext->admin && !in_array(UserPrivilege::CAN_MANAGE_SERVICE_MESSAGES, $userContext->permissions)) {
            $searchFilter->includeDrafts = false;
        }

        /* @var $handler ServiceMessageHandler */
        $handler = $applicationContext->get(ServiceMessageHandler::clazz());

        return output($handler->getServiceMessages($hesk_settings, $searchFilter));
    }

    function post() {
        global $applicationContext, $userContext, $hesk_settings;

        $this->checkSecurity($userContext);

        /* @var $handler ServiceMessageHandler */
        $handler = $applicationContext->get(ServiceMessageHandler::clazz());

        $data = JsonRetriever::getJsonData();
        $element = $handler->createServiceMessage($this->buildElementModel($data, $userContext), $hesk_settings);

        return output($element, 201);
    }

    function put($id) {
        global $applicationContext, $hesk_settings, $userContext;

        $this->checkSecurity($userContext);

        /* @var $handler ServiceMessageHandler */
        $handler = $applicationContext->get(ServiceMessageHandler::clazz());

        $data = JsonRetriever::getJsonData();
        $serviceMessage = $this->buildElementModel($data, null, false);
        $serviceMessage->id = $id;
        $element = $handler->editServiceMessage($serviceMessage, $hesk_settings);

        return output($element);
    }

    function delete($id) {
        global $applicationContext, $hesk_settings, $userContext;

        $this->checkSecurity($userContext);

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
            $serviceMessage->order = Helpers::safeArrayGet($data, 'order');
        }

        if ($creating) {
            $serviceMessage->createdBy = $userContext->id;
        }

        $serviceMessage->title = Helpers::safeArrayGet($data, 'title');
        $serviceMessage->icon = Helpers::safeArrayGet($data, 'icon');
        $serviceMessage->message = Helpers::safeArrayGet($data, 'message');
        $serviceMessage->published = Helpers::safeArrayGet($data, 'published');
        $serviceMessage->style = Helpers::safeArrayGet($data, 'style');
        $serviceMessage->language = Helpers::safeArrayGet($data, 'language');

        $jsonLocations = Helpers::safeArrayGet($data, 'locations');

        if ($jsonLocations !== null && !empty($jsonLocations)) {
            foreach ($jsonLocations as $key => $value) {
                $serviceMessage->locations[] = $value;
            }
        }

        return $serviceMessage;
    }

    static function sort() {
        /* @var $userContext UserContext */
        global $applicationContext, $hesk_settings, $userContext;

        self::staticCheckSecurity($userContext);

        $data = JsonRetriever::getJsonData();

        $order = Helpers::safeArrayGet($data, 'order');

        /* @var $handler ServiceMessageHandler */
        $handler = $applicationContext->get(ServiceMessageHandler::clazz());

        $handler->sortServiceMessages($order, $hesk_settings);
    }
}