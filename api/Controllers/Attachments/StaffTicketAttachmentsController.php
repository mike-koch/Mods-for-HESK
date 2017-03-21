<?php

namespace Controllers\Attachments;


use BusinessLogic\Attachments\AttachmentHandler;
use BusinessLogic\Attachments\CreateAttachmentForTicketModel;
use BusinessLogic\Exceptions\ApiFriendlyException;
use BusinessLogic\Helpers;
use Controllers\JsonRetriever;

class StaffTicketAttachmentsController {
    function post() {
        global $hesk_settings, $applicationContext;

        if (!$hesk_settings['attachments']['use']) {
            throw new ApiFriendlyException('Attachments are disabled on this server', 'Attachments Disabled', 404);
        }

        /* @var $attachmentHandler AttachmentHandler */
        $attachmentHandler = $applicationContext->get[AttachmentHandler::class];

        $createAttachmentForTicketModel = $this->createModel(JsonRetriever::getJsonData());

        $createdAttachment = $attachmentHandler->createAttachmentForTicket($createAttachmentForTicketModel, $hesk_settings);

        return output($createdAttachment, 201);
    }

    private function createModel($json) {
        $model = new CreateAttachmentForTicketModel();
        $model->attachmentContents = Helpers::safeArrayGet($json, 'data');
        $model->displayName = Helpers::safeArrayGet($json, 'displayName');
        $model->ticketId = Helpers::safeArrayGet($json, 'ticketId');
        $model->type = Helpers::safeArrayGet($json, 'type');

        return $model;
    }
}