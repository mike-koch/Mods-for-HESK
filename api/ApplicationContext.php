<?php

// Responsible for loading in all necessary classes. AKA a poor man's DI solution.
use BusinessLogic\Attachments\AttachmentHandler;
use BusinessLogic\Attachments\AttachmentRetriever;
use BusinessLogic\Categories\CategoryHandler;
use BusinessLogic\Categories\CategoryRetriever;
use BusinessLogic\Emails\BasicEmailSender;
use BusinessLogic\Emails\EmailSenderHelper;
use BusinessLogic\Emails\EmailTemplateParser;
use BusinessLogic\Emails\EmailTemplateRetriever;
use BusinessLogic\Emails\MailgunEmailSender;
use BusinessLogic\Navigation\CustomNavElementHandler;
use BusinessLogic\Security\BanRetriever;
use BusinessLogic\Security\PermissionChecker;
use BusinessLogic\Security\UserContextBuilder;
use BusinessLogic\Security\UserToTicketChecker;
use BusinessLogic\Settings\ApiChecker;
use BusinessLogic\Settings\SettingsRetriever;
use BusinessLogic\Statuses\StatusRetriever;
use BusinessLogic\Tickets\Autoassigner;
use BusinessLogic\Tickets\TicketDeleter;
use BusinessLogic\Tickets\TicketEditor;
use BusinessLogic\Tickets\TicketRetriever;
use BusinessLogic\Tickets\TicketCreator;
use BusinessLogic\Tickets\NewTicketValidator;
use BusinessLogic\Tickets\TicketValidators;
use BusinessLogic\Tickets\TrackingIdGenerator;
use BusinessLogic\Tickets\VerifiedEmailChecker;
use DataAccess\Attachments\AttachmentGateway;
use DataAccess\Categories\CategoryGateway;
use DataAccess\Files\FileDeleter;
use DataAccess\Files\FileReader;
use DataAccess\Files\FileWriter;
use DataAccess\Logging\LoggingGateway;
use DataAccess\Navigation\CustomNavElementGateway;
use DataAccess\Security\BanGateway;
use DataAccess\Security\UserGateway;
use DataAccess\Settings\ModsForHeskSettingsGateway;
use DataAccess\Statuses\StatusGateway;
use DataAccess\Tickets\TicketGateway;
use DataAccess\Tickets\VerifiedEmailGateway;


class ApplicationContext {
    public $get;

    /**
     * ApplicationContext constructor.
     */
    function __construct() {
        $this->get = array();

        $this->get[PermissionChecker::class] = new PermissionChecker();
        $this->get[ModsForHeskSettingsGateway::class] = new ModsForHeskSettingsGateway();
        $this->get[CustomNavElementGateway::class] = new CustomNavElementGateway();
        $this->get[LoggingGateway::class] = new LoggingGateway();
        $this->get[VerifiedEmailGateway::class] = new VerifiedEmailGateway();
        $this->get[UserGateway::class] = new UserGateway();
        $this->get[CategoryGateway::class] = new CategoryGateway();
        $this->get[BanGateway::class] = new BanGateway();
        $this->get[StatusGateway::class] = new StatusGateway();
        $this->get[BasicEmailSender::class] = new BasicEmailSender();
        $this->get[MailgunEmailSender::class] = new MailgunEmailSender();
        $this->get[EmailTemplateRetriever::class] = new EmailTemplateRetriever();
        $this->get[TicketGateway::class] = new TicketGateway();
        $this->get[FileWriter::class] = new FileWriter();
        $this->get[FileReader::class] = new FileReader();
        $this->get[FileDeleter::class] = new FileDeleter();
        $this->get[AttachmentGateway::class] = new AttachmentGateway();

        $this->get[ApiChecker::class] = new ApiChecker($this->get[ModsForHeskSettingsGateway::class]);
        $this->get[CustomNavElementHandler::class] = new CustomNavElementHandler($this->get[CustomNavElementGateway::class]);
        $this->get[VerifiedEmailChecker::class] = new VerifiedEmailChecker($this->get[VerifiedEmailGateway::class]);
        $this->get[UserContextBuilder::class] = new UserContextBuilder($this->get[UserGateway::class]);
        $this->get[BanRetriever::class] = new BanRetriever($this->get[BanGateway::class]);
        $this->get[UserToTicketChecker::class] = new UserToTicketChecker($this->get[UserGateway::class]);
        $this->get[StatusRetriever::class] = new StatusRetriever($this->get[StatusGateway::class]);
        $this->get[SettingsRetriever::class] = new SettingsRetriever($this->get[ModsForHeskSettingsGateway::class]);
        $this->get[TicketValidators::class] = new TicketValidators($this->get[TicketGateway::class]);
        $this->get[TrackingIdGenerator::class] = new TrackingIdGenerator($this->get[TicketGateway::class]);
        $this->get[Autoassigner::class] = new Autoassigner($this->get[CategoryGateway::class], $this->get[UserGateway::class]);
        $this->get[CategoryRetriever::class] = new CategoryRetriever($this->get[CategoryGateway::class],
            $this->get[ModsForHeskSettingsGateway::class]);
        $this->get[CategoryHandler::class] = new CategoryHandler(
            $this->get[CategoryGateway::class],
            $this->get[TicketGateway::class],
            $this->get[PermissionChecker::class],
            $this->get[ModsForHeskSettingsGateway::class]);
        $this->get[EmailTemplateParser::class] = new EmailTemplateParser($this->get[StatusGateway::class],
            $this->get[CategoryGateway::class],
            $this->get[UserGateway::class],
            $this->get[EmailTemplateRetriever::class]);
        $this->get[EmailSenderHelper::class] = new EmailSenderHelper($this->get[EmailTemplateParser::class],
            $this->get[BasicEmailSender::class],
            $this->get[MailgunEmailSender::class]);
        $this->get[TicketRetriever::class] = new TicketRetriever($this->get[TicketGateway::class],
            $this->get[UserToTicketChecker::class]);
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
        $this->get[AttachmentHandler::class] = new AttachmentHandler($this->get[TicketGateway::class],
            $this->get[AttachmentGateway::class],
            $this->get[FileWriter::class],
            $this->get[UserToTicketChecker::class],
            $this->get[FileDeleter::class]);
        $this->get[AttachmentRetriever::class] = new AttachmentRetriever($this->get[AttachmentGateway::class],
            $this->get[FileReader::class],
            $this->get[TicketGateway::class],
            $this->get[UserToTicketChecker::class]);
        $this->get[TicketDeleter::class] =
            new TicketDeleter($this->get[TicketGateway::class],
                $this->get[UserToTicketChecker::class],
                $this->get[AttachmentHandler::class]);
        $this->get[TicketEditor::class] =
            new TicketEditor($this->get[TicketGateway::class], $this->get[UserToTicketChecker::class]);
    }
}