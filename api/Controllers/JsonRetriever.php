<?php

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