<?php

namespace BusinessLogic\Exceptions;


use Exception;

class ApiFriendlyException extends Exception {
    public $title;
    public $httpResponseCode;

    /**
     * ApiFriendlyException constructor.
     * @param string $message
     * @param string $title
     * @param int $httpResponseCode
     */
    function __construct($message, $title, $httpResponseCode) {
        $this->title = $title;
        $this->httpResponseCode = $httpResponseCode;

        parent::__construct($message);
    }

}