<?php
//define('IN_SCRIPT', 1);
//define('HESK_PATH', '../');
// Router: handles all REST requests to go to their proper place. Common dependency loading also happens here
//require_once(__DIR__ . '/core/common.php');
require(__DIR__ . '/../Link/src/Link.php');

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

Link::all(array(
    '/test/{i}' => 'HomeController',
));