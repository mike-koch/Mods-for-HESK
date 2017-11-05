<?php

namespace v302;


class AddMissingCustomFields extends \AbstractMigration {

    function up($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets`
        ADD COLUMN `custom21` MEDIUMTEXT,
        ADD COLUMN `custom22` MEDIUMTEXT,
        ADD COLUMN `custom23` MEDIUMTEXT,
        ADD COLUMN `custom24` MEDIUMTEXT,
        ADD COLUMN `custom25` MEDIUMTEXT,
        ADD COLUMN `custom26` MEDIUMTEXT,
        ADD COLUMN `custom27` MEDIUMTEXT,
        ADD COLUMN `custom28` MEDIUMTEXT,
        ADD COLUMN `custom29` MEDIUMTEXT,
        ADD COLUMN `custom30` MEDIUMTEXT,
        ADD COLUMN `custom31` MEDIUMTEXT,
        ADD COLUMN `custom32` MEDIUMTEXT,
        ADD COLUMN `custom33` MEDIUMTEXT,
        ADD COLUMN `custom34` MEDIUMTEXT,
        ADD COLUMN `custom35` MEDIUMTEXT,
        ADD COLUMN `custom36` MEDIUMTEXT,
        ADD COLUMN `custom37` MEDIUMTEXT,
        ADD COLUMN `custom38` MEDIUMTEXT,
        ADD COLUMN `custom39` MEDIUMTEXT,
        ADD COLUMN `custom40` MEDIUMTEXT,
        ADD COLUMN `custom41` MEDIUMTEXT,
        ADD COLUMN `custom42` MEDIUMTEXT,
        ADD COLUMN `custom43` MEDIUMTEXT,
        ADD COLUMN `custom44` MEDIUMTEXT,
        ADD COLUMN `custom45` MEDIUMTEXT,
        ADD COLUMN `custom46` MEDIUMTEXT,
        ADD COLUMN `custom47` MEDIUMTEXT,
        ADD COLUMN `custom48` MEDIUMTEXT,
        ADD COLUMN `custom49` MEDIUMTEXT,
        ADD COLUMN `custom50` MEDIUMTEXT");
    }

    function down($hesk_settings) {
        $this->executeQuery("ALTER TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "stage_tickets`
        DROP COLUMN `custom21`,
        DROP COLUMN `custom22`,
        DROP COLUMN `custom23`,
        DROP COLUMN `custom24`,
        DROP COLUMN `custom25`,
        DROP COLUMN `custom26`,
        DROP COLUMN `custom27`,
        DROP COLUMN `custom28`,
        DROP COLUMN `custom29`,
        DROP COLUMN `custom30`,
        DROP COLUMN `custom31`,
        DROP COLUMN `custom32`,
        DROP COLUMN `custom33`,
        DROP COLUMN `custom34`,
        DROP COLUMN `custom35`,
        DROP COLUMN `custom36`,
        DROP COLUMN `custom37`,
        DROP COLUMN `custom38`,
        DROP COLUMN `custom39`,
        DROP COLUMN `custom40`,
        DROP COLUMN `custom41`,
        DROP COLUMN `custom42`,
        DROP COLUMN `custom43`,
        DROP COLUMN `custom44`,
        DROP COLUMN `custom45`,
        DROP COLUMN `custom46`,
        DROP COLUMN `custom47`,
        DROP COLUMN `custom48`,
        DROP COLUMN `custom49`,
        DROP COLUMN `custom50`");
    }
}