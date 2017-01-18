<?php
//define('IN_SCRIPT', 1);
//define('HESK_PATH', '../');
// Router: handles all REST requests to go to their proper place. Common dependency loading also happens here
//require_once(__DIR__ . '/core/common.php');
require(__DIR__ . '/../Link/src/Link.php');

function routeMe(){
    echo 'I am routed';
}

Link::all( array(
    '/test' => 'routeMe',
));