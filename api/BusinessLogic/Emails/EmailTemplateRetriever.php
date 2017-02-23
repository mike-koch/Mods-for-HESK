<?php
/**
 * Created by PhpStorm.
 * User: mkoch
 * Date: 2/22/2017
 * Time: 9:11 PM
 */

namespace BusinessLogic\Emails;


use BusinessLogic\Exceptions\EmailTemplateNotFoundException;

class EmailTemplateRetriever {

    function getTemplateForLanguage($templateName, $language) {

    }

    private function getFromFileSystem($template, $language)
    {
        global $hesk_settings, $hesklang;

        // Demo mode
        if (defined('HESK_DEMO')) {
            return '';
        }

        /* Get list of valid emails */
        $valid_emails = hesk_validEmails();

        /* Verify this is a valid email include */

        if (!isset(ValidEmailTemplates::getValidEmailTemplates()[$template])) {
            hesk_error($hesklang['inve']);
        }

        /* Get email template */
        $file = 'language/' . $language . '/emails/' . $template . '.txt';
        $absoluteFilePath = __DIR__ . '/../../../' . $file;

        if (file_exists($absoluteFilePath)) {
            return file_get_contents($absoluteFilePath);
        } else {
            throw new EmailTemplateNotFoundException($template, $language);
        }
    }
}