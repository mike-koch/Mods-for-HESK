<?php

namespace Controllers\Attachments;


use BusinessLogic\Attachments\AttachmentHandler;
use BusinessLogic\Attachments\AttachmentRetriever;
use BusinessLogic\Attachments\CreateAttachmentForTicketModel;
use BusinessLogic\Exceptions\ApiFriendlyException;
use BusinessLogic\Helpers;
use BusinessLogic\Security\UserToTicketChecker;
use Controllers\JsonRetriever;

class StaffTicketAttachmentsController {
    function get($ticketId, $attachmentId) {
        global $hesk_settings, $applicationContext, $userContext;

        $this->verifyAttachmentsAreEnabled($hesk_settings);

        /* @var $attachmentRetriever AttachmentRetriever */
        $attachmentRetriever = $applicationContext->get[AttachmentRetriever::class];

        $contents = $attachmentRetriever->getAttachmentContentsForTicket($ticketId, $attachmentId, $userContext, $hesk_settings);

        output(array('contents' => base64_encode($contents)));
    }

    private function verifyAttachmentsAreEnabled($heskSettings) {
        if (!$heskSettings['attachments']['use']) {
            throw new ApiFriendlyException('Attachments are disabled on this server', 'Attachments Disabled', 404);
        }
    }

    function post($ticketId) {
        global $hesk_settings, $applicationContext, $userContext;

        $this->verifyAttachmentsAreEnabled($hesk_settings);

        /* @var $attachmentHandler AttachmentHandler */
        $attachmentHandler = $applicationContext->get[AttachmentHandler::class];

        $createAttachmentForTicketModel = $this->createModel(JsonRetriever::getJsonData(), $ticketId);

        $createdAttachment = $attachmentHandler->createAttachmentForTicket(
            $createAttachmentForTicketModel, $userContext, $hesk_settings);

        return output($createdAttachment, 201);
    }

    private function createModel($json, $ticketId) {
        $model = new CreateAttachmentForTicketModel();
        $model->attachmentContents = Helpers::safeArrayGet($json, 'data');
        $model->displayName = Helpers::safeArrayGet($json, 'displayName');
        $model->ticketId = $ticketId;

        return $model;
    }
}