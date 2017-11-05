<?php

class RequestMethod {
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
    const PATCH = 'PATCH';

    static function all() {
        return array(self::GET, self::POST, self::PUT, self::DELETE, self::PATCH);
    }
}