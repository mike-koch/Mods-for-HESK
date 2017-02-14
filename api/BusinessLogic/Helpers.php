<?php

namespace BusinessLogic;


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

    static function safeArrayGet($array, $key) {
        return $array !== null && array_key_exists($key, $array)
            ? $array[$key]
            : null;
    }
}