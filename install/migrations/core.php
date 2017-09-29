<?php

use Pre140\StatusesMigration;

function getAllMigrations() {
    return array(
        1 => new StatusesMigration()
    );
}