<?php

namespace DataAccess\Entities;


use Spot\Entity;

class BaseEntity extends Entity {
    public static function table($tableName) {
        global $hesk_settings;

        return parent::table($hesk_settings['db_pfix'] . $tableName);
    }
}