<?php

namespace BusinessLogic\Navigation;

// TODO Test!
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

    function deleteCustomNavElement() {

    }

    function saveCustomNavElement() {

    }

    function createCustomNavElement() {

    }
}