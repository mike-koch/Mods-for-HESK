<?php

function get_header($key) {
    $headers = getallheaders();

    return isset($headers[$key])
        ? $headers[$key]
        : NULL;
}