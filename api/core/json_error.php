<?php

function print_error($title, $message) {
    $error = array();
    $error['type'] = 'ERROR';
    $error['title'] = $title;
    $error['message'] = $message;

    print json_encode($error);
    return;
}