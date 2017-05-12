<?php

namespace Controllers\Navigation;


use BusinessLogic\Helpers;
use BusinessLogic\Navigation\CustomNavElement;
use BusinessLogic\Navigation\CustomNavElementHandler;
use Controllers\InternalApiController;
use Controllers\JsonRetriever;

class CustomNavElementController extends InternalApiController {
    static function getAll() {
        global $applicationContext, $hesk_settings;

        self::checkForInternalUseOnly();

        /* @var $handler CustomNavElementHandler */
        $handler = $applicationContext->get[CustomNavElementHandler::class];

        output($handler->getAllCustomNavElements($hesk_settings));
    }

    function get($id) {
        global $applicationContext, $hesk_settings;

        $this->checkForInternalUseOnly();

        /* @var $handler CustomNavElementHandler */
        $handler = $applicationContext->get[CustomNavElementHandler::class];

        output($handler->getCustomNavElement($id, $hesk_settings));
    }

    function post() {
        global $applicationContext, $hesk_settings;

        $this->checkForInternalUseOnly();

        /* @var $handler CustomNavElementHandler */
        $handler = $applicationContext->get[CustomNavElementHandler::class];

        $data = JsonRetriever::getJsonData();
        $element = $handler->createCustomNavElement($this->buildElementModel($data), $hesk_settings);

        return output($element, 201);
    }

    function put($id) {
        global $applicationContext, $hesk_settings;

        $this->checkForInternalUseOnly();

        /* @var $handler CustomNavElementHandler */
        $handler = $applicationContext->get[CustomNavElementHandler::class];

        $data = JsonRetriever::getJsonData();
        $handler->saveCustomNavElement($this->buildElementModel($data, $id), $hesk_settings);

        return http_response_code(204);
    }

    function delete($id) {
        global $applicationContext, $hesk_settings;

        $this->checkForInternalUseOnly();

        /* @var $handler CustomNavElementHandler */
        $handler = $applicationContext->get[CustomNavElementHandler::class];

        $handler->deleteCustomNavElement($id, $hesk_settings);

        return http_response_code(204);
    }

    private function buildElementModel($data, $id = null) {
        $element = new CustomNavElement();
        $element->id = $id;
        $element->place = intval(Helpers::safeArrayGet($data, 'place'));
        $element->fontIcon = Helpers::safeArrayGet($data, 'fontIcon');
        $element->imageUrl = Helpers::safeArrayGet($data, 'imageUrl');
        $element->text = Helpers::safeArrayGet($data, 'text');
        $element->subtext = Helpers::safeArrayGet($data, 'subtext');

        return $element;
    }
}