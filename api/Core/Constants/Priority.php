<?php

namespace Core\Constants;


class Priority extends \BaseClass {
    const CRITICAL = 0;
    const HIGH = 1;
    const MEDIUM = 2;
    const LOW = 3;

    static function getByValue($value) {
        switch ($value) {
            case self::CRITICAL:
                return 'CRITICAL';
            case self::HIGH:
                return 'HIGH';
            case self::MEDIUM:
                return 'MEDIUM';
            case self::LOW:
                return 'LOW';
            default:
                return 'UNKNOWN';
        }
    }
}