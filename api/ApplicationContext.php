<?php

// Responsible for loading in all necessary classes. AKA a poor man's DI solution.
use BusinessLogic\Categories\CategoryRetriever;
use BusinessLogic\Security\BanRetriever;
use BusinessLogic\Security\UserContextBuilder;
use BusinessLogic\Tickets\Autoassigner;
use BusinessLogic\Tickets\TicketRetriever;
use BusinessLogic\Tickets\TicketCreator;
use BusinessLogic\Tickets\NewTicketValidator;
use BusinessLogic\Tickets\TicketValidators;
use BusinessLogic\Tickets\TrackingIdGenerator;
use DataAccess\Categories\CategoryGateway;
use DataAccess\Security\BanGateway;
use DataAccess\Security\UserGateway;
use DataAccess\Statuses\StatusGateway;
use DataAccess\Tickets\TicketGateway;


class ApplicationContext {
    public $get;

    function __construct() {
        $this->get = array();

        // User Context
        $this->get[UserGateway::class] = new UserGateway();
        $this->get[UserContextBuilder::class] = new UserContextBuilder($this->get[UserGateway::class]);

        // Categories
        $this->get[CategoryGateway::class] = new CategoryGateway();
        $this->get[CategoryRetriever::class] = new CategoryRetriever($this->get[CategoryGateway::class]);

        // Bans
        $this->get[BanGateway::class] = new BanGateway();
        $this->get[BanRetriever::class] = new BanRetriever($this->get[BanGateway::class]);

        // Tickets
        $this->get[StatusGateway::class] = new StatusGateway();
        $this->get[TicketGateway::class] = new TicketGateway();
        $this->get[TicketRetriever::class] = new TicketRetriever($this->get[TicketGateway::class]);
        $this->get[TicketValidators::class] = new TicketValidators($this->get[TicketGateway::class]);
        $this->get[TrackingIdGenerator::class] = new TrackingIdGenerator($this->get[TicketGateway::class]);
        $this->get[Autoassigner::class] = new Autoassigner();
        $this->get[NewTicketValidator::class] = new NewTicketValidator($this->get[CategoryRetriever::class],
            $this->get[BanRetriever::class],
            $this->get[TicketValidators::class]);
        $this->get[TicketCreator::class] = new TicketCreator($this->get[NewTicketValidator::class],
            $this->get[TrackingIdGenerator::class],
            $this->get[Autoassigner::class],
            $this->get[StatusGateway::class],
            $this->get[TicketGateway::class]);
    }
}