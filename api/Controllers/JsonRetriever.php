<?php
/**
 * Created by PhpStorm.
 * User: mkoch
 * Date: 2/13/2017
 * Time: 9:21 PM
 */

namespace Controllers;


class JsonRetriever {
    /**
     * Support POST, PUT, and PATCH request (and possibly more)
     *
     * @return mixed
     */
    static function getJsonData() {
        $json = file_get_contents('php://input');
        return json_decode($json, true);
    }
}