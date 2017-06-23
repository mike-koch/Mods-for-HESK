<?php

namespace BusinessLogic\Emails;


class EmailTemplate {
    /**
     * @var $languageKey string
     */
    public $languageKey;

    /**
     * @var $fileName string
     */
    public $fileName;

    /**
     * @var $forStaff bool
     */
    public $forStaff;

    function __construct($forStaff, $fileName, $languageKey = null) {
        $this->languageKey = $languageKey === null ? $fileName : $languageKey;
        $this->fileName = $fileName;
        $this->forStaff = $forStaff;
    }
}