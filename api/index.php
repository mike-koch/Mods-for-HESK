<?php
// Router: handles all REST requests to go to their proper place. Common dependency loading also happens here
define('IN_SCRIPT', 1);
define('HESK_PATH', '../');
require_once(__DIR__ . '/core/common.php');
require(__DIR__ . '/../Link/src/Link.php');
require(__DIR__ . '/../hesk_settings.inc.php');

// Controllers
require(__DIR__ . '/controllers/CategoryController.php');


class HomeController
{

    function get($i){
        echo 'You have got to home :) Val:' . intval($i);
    }

    function post(){
        echo 'You have posted to home';
    }

    function put(){
        echo 'You have put to home';
    }

    function delete(){
        echo 'You have deleted the home :(';
    }
}

function handle404() {
    http_response_code(404);
    print json_encode('404 found');
}

function assertApiIsEnabled() {
    //-- TODO
}

// Must use fully-qualified namespace to controllers
Link::before('assertApiIsEnabled');

Link::all(array(
    '/' => 'assertApiIsEnabled',
    '/test/{i}' => '\Controllers\Category\CategoryController',
    '404' => 'handle404'
));