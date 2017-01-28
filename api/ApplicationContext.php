<?php

namespace Core;

// Responsible for loading in all necessary classes. AKA a poor man's DI solution.
use BusinessLogic\Category\CategoryRetriever;
use BusinessLogic\Security\BanRetriever;
use DataAccess\CategoryGateway;
use DataAccess\Security\BanGateway;

class ApplicationContext {
    public $get;

    function __construct() {
        $this->get = array();

        $this->get['CategoryGateway'] = new CategoryGateway();
        $this->get['CategoryRetriever'] = new CategoryRetriever($this->get['CategoryGateway']);

        $this->get['BanGateway'] = new BanGateway();
        $this->get['BanRetriever'] = new BanRetriever($this->get['BanGateway']);
    }
}