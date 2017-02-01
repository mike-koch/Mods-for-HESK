<?php

namespace Core;

// Responsible for loading in all necessary classes. AKA a poor man's DI solution.
use BusinessLogic\Category\CategoryRetriever;
use BusinessLogic\Security\BanRetriever;
use BusinessLogic\Security\UserContextBuilder;
use BusinessLogic\Tickets\TicketRetriever;
use DataAccess\CategoryGateway;
use DataAccess\Security\BanGateway;
use DataAccess\Security\UserGateway;
use DataAccess\Tickets\TicketGateway;

class ApplicationContext {
    public $get;

    function __construct() {
        $this->get = array();

        // Categories
        $this->get['CategoryGateway'] = new CategoryGateway();
        $this->get['CategoryRetriever'] = new CategoryRetriever($this->get['CategoryGateway']);

        // Tickets
        $this->get['TicketGateway'] = new TicketGateway();
        $this->get['TicketRetriever'] = new TicketRetriever($this->get['TicketGateway']);

        // Bans
        $this->get['BanGateway'] = new BanGateway();
        $this->get['BanRetriever'] = new BanRetriever($this->get['BanGateway']);

        // User Context
        $this->get['UserGateway'] = new UserGateway();
        $this->get['UserContextBuilder'] = new UserContextBuilder($this->get['UserGateway']);
    }
}