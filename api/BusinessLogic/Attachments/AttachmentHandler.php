<?php

namespace BusinessLogic\Attachments;


use BusinessLogic\Exceptions\ValidationException;
use BusinessLogic\ValidationModel;

class AttachmentHandler {

    /**
     * @param $createAttachmentModel CreateAttachmentForTicketModel
     */
    function createAttachmentForTicket($createAttachmentModel) {
        $this->validate($createAttachmentModel);
    }

    /**
     * @param $createAttachmentModel CreateAttachmentForTicketModel
     * @throws ValidationException
     */
    private function validate($createAttachmentModel) {
        $errorKeys = array();
        if ($createAttachmentModel->attachmentContents === null ||
            trim($createAttachmentModel->attachmentContents) === '') {
            $errorKeys[] = 'CONTENTS_EMPTY';
        }

        if (base64_decode($createAttachmentModel->attachmentContents, true) === false) {
            $errorKeys[] = 'CONTENTS_NOT_BASE_64';
        }

        if ($createAttachmentModel->displayName === null ||
            trim($createAttachmentModel->displayName === '')) {
            $errorKeys[] = 'DISPLAY_NAME_EMPTY';
        }

        if ($createAttachmentModel->ticketId === null ||
            $createAttachmentModel->ticketId < 1) {
            $errorKeys[] = 'TICKET_ID_MISSING';
        }

        if (count($errorKeys) > 0) {
            $validationModel = new ValidationModel();
            $validationModel->errorKeys = $errorKeys;
            $validationModel->valid = false;
            throw new ValidationException($validationModel);
        }
    }
}