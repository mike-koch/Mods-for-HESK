<?php

namespace DataAccess;


use Exception;

class CommonDao extends \BaseClass {
    /**
     * @throws Exception if the database isn't properly configured
     */
    function init() {
        if (!function_exists('hesk_dbConnect')) {
            throw new \BaseException('Database not loaded!');
        }
        hesk_dbConnect();
    }

    function close() {
        hesk_dbClose();
    }
}