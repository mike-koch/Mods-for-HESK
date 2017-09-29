<?php

abstract class AbstractMigration {
    abstract function up();

    abstract function down();
}