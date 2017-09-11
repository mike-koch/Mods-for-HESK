<?php

namespace BusinessLogic\Navigation;

// TODO Test!
use BusinessLogic\Exceptions\ApiFriendlyException;
use DataAccess\Navigation\CustomNavElementGateway;

class CustomNavElementHandler {
    /* @var $customNavElementGateway CustomNavElementGateway */
    private $customNavElementGateway;

    function __construct(CustomNavElementGateway $customNavElementGateway) {
        $this->customNavElementGateway = $customNavElementGateway;
    }


    function getAllCustomNavElements($heskSettings) {
        return $this->customNavElementGateway->getAllCustomNavElements($heskSettings);
    }

    function getCustomNavElement($id, $heskSettings) {
        $elements = $this->getAllCustomNavElements($heskSettings);

        foreach ($elements as $element) {
            if ($element->id === intval($id)) {
                return output($element);
            }
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
        $elements = $this->customNavElementGateway->getAllCustomNavElements($heskSettings);
        $elementToChange = null;
        foreach ($elements as $element) {
            if ($element->id === intval($elementId)) {
                $elementToChange = $element;
            }
        }


        if ($direction === Direction::UP) {
            $elementToChange->sort -= 15;
        } else {
            $elementToChange->sort += 15;
        }

        $this->customNavElementGateway->saveCustomNavElement($elementToChange, $heskSettings);
        $this->customNavElementGateway->resortAllElements($heskSettings);
    }
}