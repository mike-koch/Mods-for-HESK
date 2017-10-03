<?php

namespace v231;


class UpdateVersion extends \AbstractUpdateMigration {

    function getUpVersion() {
        return '2.3.1';
    }

    function getDownVersion() {
        return '2.3.0';
    }
}