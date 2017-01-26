<?php

function print_error($title, $message) {
    require_once(__DIR__ . '/output.php');

    $error = array();
    $error['type'] = 'ERROR';
    $error['title'] = $title;
    $error['message'] = $message;

    print output($error);
    return;
}