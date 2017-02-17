<?php

namespace BusinessLogic\Statuses;


class StatusLanguage {
    public $language;
    public $text;

    function __construct($language, $text) {
        $this->language = $language;
        $this->text = $text;
    }
}