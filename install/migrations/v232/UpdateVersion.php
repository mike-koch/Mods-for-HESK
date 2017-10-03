<?php

namespace v232;


class UpdateVersion extends \AbstractUpdateMigration {

    function getUpVersion() {
        return '2.3.2';
    }

    function getDownVersion() {
        return '2.3.1';
    }
}