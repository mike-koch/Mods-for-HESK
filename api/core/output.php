<?php

function output($data, $status_code = 200) {
    header('Content-Type: application/json');
    print json_encode($data);
    return http_response_code($status_code);
}