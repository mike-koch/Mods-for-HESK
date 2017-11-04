<?php

class LegacyUpdateMigration extends AbstractMigration {
    private $upVersion;
    private $downVersion;

    public function __construct($upVersion, $downVersion) {
        $this->upVersion = $upVersion;
        $this->downVersion = $downVersion;
    }

    function up($hesk_settings) {
        $this->updateVersion($this->upVersion, $hesk_settings);
    }

    function down($hesk_settings) {
        $this->updateVersion($this->downVersion, $hesk_settings);
    }
}