<?php

abstract class AbstractMigration {
    abstract function up($hesk_settings);

    abstract function down($hesk_settings);

    function executeQuery($sql)
    {
        global $hesk_last_query;
        global $hesk_db_link;
        if (function_exists('mysqli_connect')) {

            if (!$hesk_db_link && !hesk_dbConnect()) {
                return false;
            }

            $hesk_last_query = $sql;

            if ($res = @mysqli_query($hesk_db_link, $sql)) {
                return $res;
            } else {
                http_response_code(500);
                print "Could not execute query: $sql. MySQL said: " . mysqli_error($hesk_db_link);
                die();
            }
        } else {
            if (!$hesk_db_link && !hesk_dbConnect()) {
                return false;
            }

            $hesk_last_query = $sql;

            if ($res = @mysql_query($sql, $hesk_db_link)) {
                return $res;
            } else {
                http_response_code(500);
                print "Could not execute query: $sql. MySQL said: " . mysql_error();
                die();
            }
        }
    }

    function updateVersion($version, $hesk_settings) {
        $this->executeQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET `Value` = '{$version}' WHERE `Key` = 'modsForHeskVersion'");
    }
}