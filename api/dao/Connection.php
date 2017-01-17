<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 1/16/17
 * Time: 10:18 PM
 */

namespace DataAccess;


use mysqli;

class Connection {
    static function connect($hesk_settings) {
        //-- Return mysqli_connection
    }

    /**
     * @param $mysqliConnection mysqli The MySQLi connection obtained from Connection::connect
     * @return bool true if connection closed, false otherwise
     */
    static function close($mysqliConnection) {
        return $mysqliConnection->close();
    }
}