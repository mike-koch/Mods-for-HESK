<?php

abstract class AbstractUpdateMigration extends AbstractMigration {
    abstract function getUpVersion();

    abstract function getDownVersion();

    function up($hesk_settings) {
        $this->updateVersion($this->getUpVersion(), $hesk_settings);
    }

    function down($hesk_settings) {
        $this->updateVersion($this->getDownVersion(), $hesk_settings);
    }
}