<?php

namespace BusinessLogic\Emails;


use BusinessLogic\Tickets\Attachment;
use BusinessLogic\Tickets\Ticket;
use Mailgun\Mailgun;

class MailgunEmailSender implements EmailSender {
    function sendEmail($emailBuilder, $heskSettings, $modsForHeskSettings, $sendAsHtml) {
        $mailgunArray = array();

        $mailgunArray['from'] = $heskSettings['noreply_mail']; // Email Address
        if ($heskSettings['noreply_name'] !== null && $heskSettings['noreply_name'] !== '') {
            $mailgunArray['from'] = "{$heskSettings['noreply_name']} <{$heskSettings['noreply_mail']}>"; // Name and address
        }

        $mailgunArray['to'] = implode(',', $emailBuilder->to);

        if ($emailBuilder->cc !== null) {
            $mailgunArray['cc'] = implode(',', $emailBuilder->cc);
        }

        if ($emailBuilder->bcc !== null) {
            $mailgunArray['bcc'] = implode(',', $emailBuilder->bcc);
        }

        $mailgunArray['subject'] = $emailBuilder->subject;
        $mailgunArray['text'] = $emailBuilder->message;

        if ($sendAsHtml) {
            $mailgunArray['html'] = $emailBuilder->htmlMessage;
        }

        $mailgunAttachments = array();
        if ($emailBuilder->attachments !== null) {
            foreach ($emailBuilder->attachments as $attachment) {
                $mailgunAttachments[] = array(
                    'remoteName' => $attachment->fileName,
                    'filePath' => __DIR__ . '/../../../' . $heskSettings['attach_dir'] . '/' . $attachment->savedName
                );
            }
        }

        var_dump($mailgunArray);

        $result = $this->sendMessage($mailgunArray, $mailgunAttachments, $modsForHeskSettings);


        if (isset($result->http_response_code)
            && $result->http_response_code === 200) {
            return true;
        }

        return $result;
    }

    private function sendMessage($mailgunArray, $attachments, $modsForHeskSettings) {
        $messageClient = new Mailgun($modsForHeskSettings['mailgun_api_key']);

        $mailgunAttachments = array();
        if (count($attachments) > 0) {
            $mailgunAttachments = array(
                'attachment' => $attachments
            );
        }

        $result = $messageClient->sendMessage($modsForHeskSettings['mailgun_domain'], $mailgunArray, $mailgunAttachments);

        return $result;
    }
}