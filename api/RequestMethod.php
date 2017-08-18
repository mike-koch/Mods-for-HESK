<?php

class RequestMethod {
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
    const PATCH = 'PATCH';
    const ALL = [self::GET, self::POST, self::PUT, self::DELETE, self::PATCH];
}