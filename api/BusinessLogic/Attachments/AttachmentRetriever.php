<?php

namespace BusinessLogic\Attachments;


use DataAccess\Attachments\AttachmentGateway;
use DataAccess\Files\FileReader;

class AttachmentRetriever {
    /* @var $attachmentGateway AttachmentGateway */
    private $attachmentGateway;

    /* @var $fileReader FileReader */
    private $fileReader;

    function __construct($attachmentGateway, $fileReader) {
        $this->attachmentGateway = $attachmentGateway;
        $this->fileReader = $fileReader;
    }

    function getAttachmentContentsForTicket($id, $heskSettings) {
        $attachment = $this->attachmentGateway->getAttachmentById($id, $heskSettings);
        $contents = base64_encode($this->fileReader->readFromFile(
            $attachment->savedName, $heskSettings['attach_dir']));

        return $contents;
    }
}