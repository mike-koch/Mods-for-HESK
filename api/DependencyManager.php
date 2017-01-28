<?php

namespace Core;

// Responsible for loading in all necessary classes. AKA a poor man's DI solution.
use BusinessLogic\Category\CategoryRetriever;
use DataAccess\CategoryGateway;

class DependencyManager {
    public $get;

    function __construct() {
        $this->get = array();

        $this->get['CategoryGateway'] = new CategoryGateway();
        $this->get['CategoryRetriever'] = new CategoryRetriever($this->get['CategoryGateway']);
    }
}