<?php
/**
 * Created by PhpStorm.
 * User: mkoch
 * Date: 2/22/2017
 * Time: 10:00 PM
 */

namespace BusinessLogic\Exceptions;


class EmailTemplateNotFoundException extends ApiFriendlyException {
    function __construct($emailTemplate, $language) {
        parent::__construct(sprintf("The email template '%s' was not found for the language '%s'", $emailTemplate, $language),
            'Email Template Not Found!', 400);
    }
}