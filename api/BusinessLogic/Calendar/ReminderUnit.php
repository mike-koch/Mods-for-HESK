<?php

namespace BusinessLogic\Calendar;


class ReminderUnit {
    const MINUTE = 0;
    const HOUR = 1;
    const DAY = 2;
    const WEEK = 3;

    static function getByValue($value) {
        switch ($value) {
            case 0:
                return 'MINUTE';
            case 1:
                return 'HOUR';
            case 2:
                return 'DAY';
            case 3:
                return 'WEEK';
            default:
                return 'UNKNOWN';
        }
    }
}