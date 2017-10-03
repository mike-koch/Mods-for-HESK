<?php

namespace v242;


class UpdateVersion extends \AbstractUpdateMigration {

    function getUpVersion() {
        return '2.4.2';
    }

    function getDownVersion() {
        return '2.4.1';
    }
}