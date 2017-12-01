<?php

function print_error($title, $message, $logId = null, $response_code = 500) {
    require_once(__DIR__ . '/output.php');

    $error = array();
    $error['type'] = 'ERROR';
    $error['title'] = $title;
    $error['message'] = $message;

    if ($logId !== null) {
        $error['logId'] = $logId;
    }


    print output($error, $response_code);
    return;
}