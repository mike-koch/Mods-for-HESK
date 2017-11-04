<?php

class UpdateMigration extends AbstractUpdatableMigration {
    private $upVersion;
    private $downVersion;

    public function __construct($upVersion, $downVersion, $migrationNumber) {
        parent::__construct($migrationNumber);

        $this->upVersion = $upVersion;
        $this->downVersion = $downVersion;
    }

    function innerUp($hesk_settings) {
        $this->updateVersion($this->upVersion, $hesk_settings);
    }

    function innerDown($hesk_settings) {
        $this->updateVersion($this->downVersion, $hesk_settings);
    }
}