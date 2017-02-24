<?php
/**
 * Created by PhpStorm.
 * User: mkoch
 * Date: 2/23/2017
 * Time: 8:13 PM
 */

namespace BusinessLogic\Exceptions;


class InvalidEmailTemplateException extends ApiFriendlyException {
    function __construct($template) {
        parent::__construct(sprintf("The email template '%s' is invalid", $template), 'Invalid Email Template', 400);
    }
}