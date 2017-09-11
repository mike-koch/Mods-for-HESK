<?php

namespace Controllers\Attachments;


use BusinessLogic\Attachments\Attachment;
use BusinessLogic\Attachments\AttachmentRetriever;
use BusinessLogic\Exceptions\ApiFriendlyException;

class PublicAttachmentController {
    static function getRaw($trackingId, $attachmentId) {
        global $hesk_settings, $applicationContext, $userContext;

        self::verifyAttachmentsAreEnabled($hesk_settings);

        /* @var $attachmentRetriever AttachmentRetriever */
        $attachmentRetriever = $applicationContext->get(AttachmentRetriever::class);

        $attachment = $attachmentRetriever->getAttachmentContentsForTrackingId($trackingId, $attachmentId, $userContext, $hesk_settings);

        /* @var $metadata Attachment */
        $metadata = $attachment['meta'];

        // Send the file as an attachment to prevent malicious code from executing
        header("Pragma: "); # To fix a bug in IE when running https
        header("Cache-Control: "); # To fix a bug in IE when running https
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . $metadata->fileSize);
        header('Content-Disposition: attachment; filename=' . $metadata->displayName);
        print $attachment['contents'];
    }

    private static function verifyAttachmentsAreEnabled($heskSettings) {
        if (!$heskSettings['attachments']['use']) {
            throw new ApiFriendlyException('Attachments are disabled on this server', 'Attachments Disabled', 404);
        }
    }
}
