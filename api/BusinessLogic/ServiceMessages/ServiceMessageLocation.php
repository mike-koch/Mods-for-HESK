<?php

namespace BusinessLogic\ServiceMessages;


class ServiceMessageLocation {
    const CUSTOMER_HOME = 'CUSTOMER_HOME';
    const CUSTOMER_KB_HOME = 'CUSTOMER_KB_HOME';
    const CUSTOMER_VIEW_KB_ARTICLE = 'CUSTOMER_VIEW_KB_ARTICLE';
    const CUSTOMER_SUBMIT_TICKET = 'CUSTOMER_SUBMIT_TICKET';
    const CUSTOMER_VIEW_TICKET = 'CUSTOMER_VIEW_TICKET';
    const STAFF_LOGIN = 'STAFF_LOGIN';
    const STAFF_HOME = 'STAFF_HOME';
    const STAFF_KB_HOME = 'STAFF_KB_HOME';
    const STAFF_VIEW_KB_ARTICLE = 'STAFF_VIEW_KB_ARTICLE';
    const STAFF_SUBMIT_TICKET = 'STAFF_SUBMIT_TICKET';
    const STAFF_VIEW_TICKET = 'STAFF_VIEW_TICKET';

    static function getAll() {
        return array(
            self::CUSTOMER_HOME,
            self::CUSTOMER_KB_HOME,
            self::CUSTOMER_VIEW_KB_ARTICLE,
            self::CUSTOMER_SUBMIT_TICKET,
            self::CUSTOMER_VIEW_TICKET,
            self::STAFF_LOGIN,
            self::STAFF_HOME,
            self::STAFF_KB_HOME,
            self::STAFF_VIEW_KB_ARTICLE,
            self::STAFF_SUBMIT_TICKET,
            self::STAFF_VIEW_TICKET,
        );
    }
}