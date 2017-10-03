<?php

namespace v250;


class UpdateVersion extends \AbstractUpdateMigration {

    function getUpVersion() {
        return '2.5.0';
    }

    function getDownVersion() {
        return '2.4.2';
    }
}