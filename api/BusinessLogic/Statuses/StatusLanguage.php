<?php
/**
 * Created by PhpStorm.
 * User: mkoch
 * Date: 2/16/2017
 * Time: 8:53 PM
 */

namespace BusinessLogic\Statuses;


class StatusLanguage {
    public $language;
    public $text;

    function __construct($language, $text) {
        $this->language = $language;
        $this->text = $text;
    }
}