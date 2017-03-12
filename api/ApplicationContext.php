<?php

// Responsible for loading in all necessary classes. AKA a poor man's DI solution.
use BusinessLogic\Categories\CategoryRetriever;
use BusinessLogic\Emails\BasicEmailSender;
use BusinessLogic\Emails\EmailSenderHelper;
use BusinessLogic\Emails\EmailTemplateParser;
use BusinessLogic\Emails\EmailTemplateRetriever;
use BusinessLogic\Emails\MailgunEmailSender;
use BusinessLogic\Security\BanRetriever;
use BusinessLogic\Security\UserContextBuilder;
use BusinessLogic\Settings\ApiChecker;
use BusinessLogic\Tickets\Autoassigner;
use BusinessLogic\Tickets\TicketRetriever;
use BusinessLogic\Tickets\TicketCreator;
use BusinessLogic\Tickets\NewTicketValidator;
use BusinessLogic\Tickets\TicketValidators;
use BusinessLogic\Tickets\TrackingIdGenerator;
use BusinessLogic\Tickets\VerifiedEmailChecker;
use DataAccess\Categories\CategoryGateway;
use DataAccess\Security\BanGateway;
use DataAccess\Security\UserGateway;
use DataAccess\Settings\ModsForHeskSettingsGateway;
use DataAccess\Statuses\StatusGateway;
use DataAccess\Tickets\TicketGateway;
use DataAccess\Tickets\VerifiedEmailGateway;


class ApplicationContext {
    public $get;

    function __construct() {
        $this->get = array();

        // Settings
        $this->get[ModsForHeskSettingsGateway::class] = new ModsForHeskSettingsGateway();

        // API Checker
        $this->get[ApiChecker::class] = new ApiChecker($this->get[ModsForHeskSettingsGateway::class]);

        // Verified Email Checker
        $this->get[VerifiedEmailGateway::class] = new VerifiedEmailGateway();
        $this->get[VerifiedEmailChecker::class] = new VerifiedEmailChecker($this->get[VerifiedEmailGateway::class]);

        // Users
        $this->get[UserGateway::class] = new UserGateway();
        $this->get[UserContextBuilder::class] = new UserContextBuilder($this->get[UserGateway::class]);

        // Categories
        $this->get[CategoryGateway::class] = new CategoryGateway();
        $this->get[CategoryRetriever::class] = new CategoryRetriever($this->get[CategoryGateway::class]);

        // Bans
        $this->get[BanGateway::class] = new BanGateway();
        $this->get[BanRetriever::class] = new BanRetriever($this->get[BanGateway::class]);

        // Statuses
        $this->get[StatusGateway::class] = new StatusGateway();

        // Email Sender
        $this->get[EmailTemplateRetriever::class] = new EmailTemplateRetriever();
        $this->get[EmailTemplateParser::class] = new EmailTemplateParser($this->get[StatusGateway::class],
            $this->get[CategoryGateway::class],
            $this->get[UserGateway::class],
            $this->get[EmailTemplateRetriever::class]);
        $this->get[BasicEmailSender::class] = new BasicEmailSender();
        $this->get[MailgunEmailSender::class] = new MailgunEmailSender();
        $this->get[EmailSenderHelper::class] = new EmailSenderHelper($this->get[EmailTemplateParser::class],
            $this->get[BasicEmailSender::class],
            $this->get[MailgunEmailSender::class]);

        // Tickets
        $this->get[TicketGateway::class] = new TicketGateway();
        $this->get[TicketRetriever::class] = new TicketRetriever($this->get[TicketGateway::class]);
        $this->get[TicketValidators::class] = new TicketValidators($this->get[TicketGateway::class]);
        $this->get[TrackingIdGenerator::class] = new TrackingIdGenerator($this->get[TicketGateway::class]);
        $this->get[Autoassigner::class] = new Autoassigner($this->get[CategoryGateway::class], $this->get[UserGateway::class]);
        $this->get[NewTicketValidator::class] = new NewTicketValidator($this->get[CategoryRetriever::class],
            $this->get[BanRetriever::class],
            $this->get[TicketValidators::class]);
        $this->get[TicketCreator::class] = new TicketCreator($this->get[NewTicketValidator::class],
            $this->get[TrackingIdGenerator::class],
            $this->get[Autoassigner::class],
            $this->get[StatusGateway::class],
            $this->get[TicketGateway::class],
            $this->get[VerifiedEmailChecker::class],
            $this->get[EmailSenderHelper::class],
            $this->get[UserGateway::class],
            $this->get[ModsForHeskSettingsGateway::class]);
    }
}