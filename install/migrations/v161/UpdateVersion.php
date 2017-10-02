<?php

namespace v161;


class UpdateVersion extends \AbstractMigration {

    function up($hesk_settings) {
        $this->updateVersion('1.6.1', $hesk_settings);
    }

    function down($hesk_settings) {
        $this->updateVersion('1.6.0', $hesk_settings);
    }
}