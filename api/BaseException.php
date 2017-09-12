<?php

class BaseException extends Exception {
    static function clazz() {
        return get_called_class();
    }
}