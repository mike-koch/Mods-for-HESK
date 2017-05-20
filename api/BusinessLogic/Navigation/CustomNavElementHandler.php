<?php

namespace BusinessLogic\Navigation;

// TODO Test!
use BusinessLogic\Exceptions\ApiFriendlyException;
use DataAccess\Navigation\CustomNavElementGateway;

class CustomNavElementHandler {
    /* @var $customNavElementGateway CustomNavElementGateway */
    private $customNavElementGateway;

    function __construct($customNavElementGateway) {
        $this->customNavElementGateway = $customNavElementGateway;
    }


    function getAllCustomNavElements($heskSettings) {
        return $this->customNavElementGateway->getAllCustomNavElements($heskSettings);
    }

    function getCustomNavElement($id, $heskSettings) {
        $elements = $this->getAllCustomNavElements($heskSettings);

        if (isset($elements[$id])) {
            return $elements[$id];
        }

        throw new ApiFriendlyException("Custom nav element {$id} not found!", "Element Not Found", 404);
    }

    function deleteCustomNavElement($id, $heskSettings) {
        $this->customNavElementGateway->deleteCustomNavElement($id, $heskSettings);
        $this->customNavElementGateway->resortAllElements($heskSettings);
    }

    function saveCustomNavElement($element, $heskSettings) {
        $this->customNavElementGateway->saveCustomNavElement($element, $heskSettings);
    }

    function createCustomNavElement($element, $heskSettings) {
        $element = $this->customNavElementGateway->createCustomNavElement($element, $heskSettings);
        $this->customNavElementGateway->resortAllElements($heskSettings);

        return $element;
    }

    function sortCustomNavElement($elementId, $direction, $heskSettings) {
        /* @var $element CustomNavElement */
        $element = $this->customNavElementGateway->getAllCustomNavElements($heskSettings)[$elementId];

        if ($direction === 'up') {
            $element->sort -= 15;
        } else {
            $element->sort += 15;
        }

        $this->customNavElementGateway->saveCustomNavElement($element, $heskSettings);
        $this->customNavElementGateway->resortAllElements($heskSettings);
    }
}