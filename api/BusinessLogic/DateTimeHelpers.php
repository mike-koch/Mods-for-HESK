<?php

namespace BusinessLogic;


class DateTimeHelpers {
    static function heskDate($heskSettings, $dt = '', $isStr = true, $return_str = true) {

        if (!$dt) {
            $dt = time();
        } elseif ($isStr) {
            $dt = strtotime($dt);
        }

        // Return formatted date
        return $return_str ? date($heskSettings['timeformat'], $dt) : $dt;

    }
}