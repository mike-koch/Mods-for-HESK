<?php

function print_error($title, $message, $response_code = 500) {
    require_once(__DIR__ . '/output.php');

    $error = array();
    $error['type'] = 'ERROR';
    $error['title'] = $title;
    $error['message'] = $message;

    print output($error, $response_code);
    return;
}