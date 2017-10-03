<?php

namespace v221;


class UpdateVersion extends \AbstractMigration {

    function up($hesk_settings) {
        $this->updateVersion('2.2.1', $hesk_settings);
    }

    function down($hesk_settings) {
        $this->updateVersion('2.2.0', $hesk_settings);
    }
}