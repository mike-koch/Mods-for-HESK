<?php

function output($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    print json_encode($data);
}