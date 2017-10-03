<?php

namespace v211;


class UpdateVersion extends \AbstractMigration {

    function up($hesk_settings) {
        $this->updateVersion('2.1.1', $hesk_settings);
    }

    function down($hesk_settings) {
        $this->updateVersion('2.1.0', $hesk_settings);
    }
}