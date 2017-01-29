<?php
/**
 * Created by PhpStorm.
 * User: mkoch
 * Date: 1/28/2017
 * Time: 8:54 PM
 */

namespace BusinessLogic\Helpers;


class Helpers {
    static function getHeader($key) {
        $headers = getallheaders();

        $uppercaseHeaders = array();
        foreach ($headers as $header => $value) {
            $uppercaseHeaders[strtoupper($header)] = $value;
        }

        return isset($uppercaseHeaders[$key])
            ? $uppercaseHeaders[$key]
            : NULL;
    }

    static function hashToken($token) {
        return hash('sha512', $token);
    }
}