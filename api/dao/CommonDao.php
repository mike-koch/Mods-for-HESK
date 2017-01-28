<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 1/28/2017
 * Time: 1:33 AM
 */

namespace DataAccess;


use Exception;

class CommonDao {
    /**
     * @throws Exception if the database isn't properly configured
     */
    function init() {
        if (!function_exists('hesk_dbConnect')) {
            throw new Exception('Database not loaded!');
        }
        hesk_dbConnect();
    }

    function close() {
        hesk_dbClose();
    }
}