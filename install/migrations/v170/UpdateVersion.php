<?php

namespace v170;


class UpdateVersion extends \AbstractMigration {

    function up($hesk_settings) {
        $this->updateVersion('1.7.0', $hesk_settings);
    }

    function down($hesk_settings) {
        $this->updateVersion('1.6.1', $hesk_settings);
    }
}