<?php
class AccessException extends Exception {
    public function __construct($code)
    {
        $message = '';
        if ($code == 422) {
            $message = 'The X-Auth-Token is invalid';
        }
        parent::__construct($message, $code);
    }
}