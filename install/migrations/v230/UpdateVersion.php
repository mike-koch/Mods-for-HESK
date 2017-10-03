<?php

namespace v230;


class UpdateVersion extends \AbstractUpdateMigration {

    function getUpVersion() {
        return '2.3.0';
    }

    function getDownVersion() {
        return '2.2.1';
    }
}