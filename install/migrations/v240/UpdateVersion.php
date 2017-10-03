<?php

namespace v240;


class UpdateVersion extends \AbstractUpdateMigration {

    function getUpVersion() {
        return '2.4.0';
    }

    function getDownVersion() {
        return '2.3.2';
    }
}