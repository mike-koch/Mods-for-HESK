<?php

namespace BusinessLogic\ServiceMessages;


class ServiceMessageStyle {
    const NONE = 'NONE'; // 0
    const SUCCESS = 'SUCCESS'; // 1
    const INFO = 'INFO'; // 2
    const NOTICE = 'NOTICE'; // 3
    const ERROR = 'ERROR'; // 4

    static function getStyleById($id) {
        $styles = array(
            0 => self::NONE,
            1 => self::SUCCESS,
            2 => self::INFO,
            3 => self::NOTICE,
            4 => self::ERROR
        );

        if (!isset($styles[$id])) {
            throw new \Exception("Style {$id} is not a valid service message style.");
        }

        return $styles[$id];
    }

    static function getIdForStyle($style) {
        $styles = array(
            self::NONE => 0,
            self::SUCCESS => 1,
            self::INFO => 2,
            self::NOTICE => 3,
            self::ERROR => 4
        );

        if (!isset($styles[$style])) {
            throw new \Exception("Style {$style} is not a valid service message style.");
        }

        return $styles[$style];
    }
}