<?php

namespace v201;


class UpdateVersion extends \AbstractMigration {

    function up($hesk_settings) {
        $this->updateVersion('2.0.1', $hesk_settings);
    }

    function down($hesk_settings) {
        $this->updateVersion('2.0.0', $hesk_settings);
    }
}